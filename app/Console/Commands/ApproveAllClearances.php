<?php

namespace App\Console\Commands;

use App\Models\ClearanceApproval;
use App\Models\ClearanceRequest;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ApproveAllClearances extends Command
{
    protected $signature = 'clearance:approve-all {student_id?} {--final : Also grant College President final approval to fully clear the student}';

    protected $description = 'Automatically approve all pending clearance requests for all offices.';

    public function handle()
    {
        $studentId = $this->argument('student_id');
        $includeFinal = $this->option('final');

        $query = ClearanceApproval::query()
            ->where('status', 'pending');

        if (! $includeFinal) {
            $query->whereHas('office', function ($q) {
                $q->where('is_final_approver', false);
            });
        }

        if ($studentId) {
            $query->whereHas('clearanceRequest.user', function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            });
        }

        $pendingApprovals = $query->with(['clearanceRequest.user', 'clearanceRequest.approvals.office', 'office'])->get();

        if ($pendingApprovals->isEmpty()) {
            $this->info('No pending approvals found.');

            return;
        }

        $presidentId = User::where('role', 'president')->first()?->id ?? User::where('role', 'admin')->first()?->id ?? 1;

        foreach ($pendingApprovals as $approval) {
            if ($approval->office?->is_final_approver) {
                $approverId = $presidentId;
            } else {
                $approverId = User::where('role', 'staff')
                    ->where('office_id', $approval->office_id)
                    ->first()?->id ?? $presidentId;
            }

            $approval->update([
                'status' => 'approved',
                'approved_by' => $approverId,
                'remarks' => null,
                'acted_at' => now(),
            ]);

            if ($approval->clearanceRequest && $approval->clearanceRequest->user && $approval->office) {
                NotificationService::send(
                    $approval->clearanceRequest->user,
                    'Clearance request auto-approved',
                    "Your {$approval->office->name} clearance request has been automatically approved.",
                    '/dashboard'
                );
            }
        }

        $this->info("Successfully auto-approved {$pendingApprovals->count()} clearance requests.");

        if ($includeFinal) {
            $requestsQuery = ClearanceRequest::query()
                ->where('status', '!=', 'cleared');

            if ($studentId) {
                $requestsQuery->whereHas('user', function ($q) use ($studentId) {
                    $q->where('student_id', $studentId);
                });
            }

            $requestsToFinalize = $requestsQuery->with(['user', 'approvals.office'])->get();

            foreach ($requestsToFinalize as $clearanceRequest) {
                $allApproved = $clearanceRequest->approvals->every(fn ($a) => $a->status === 'approved');

                if ($allApproved) {
                    $receiptNumber = $clearanceRequest->receipt_number
                        ?: sprintf('TPC-CLR-%s-%06d', now()->format('Y'), $clearanceRequest->id);

                    $verificationCode = $clearanceRequest->verification_code;
                    if (! $verificationCode) {
                        do {
                            $verificationCode = Str::upper(Str::random(32));
                        } while (ClearanceRequest::where('verification_code', $verificationCode)->exists());
                    }

                    $clearanceRequest->update([
                        'status' => 'cleared',
                        'cleared_at' => now(),
                        'receipt_number' => $receiptNumber,
                        'verification_code' => $verificationCode,
                    ]);

                    if ($clearanceRequest->user) {
                        NotificationService::send(
                            user: $clearanceRequest->user,
                            title: 'Clearance Fully Approved',
                            message: 'Your clearance request has been fully approved by the College President.',
                            link: '/dashboard'
                        );
                    }

                    $this->info("Student {$clearanceRequest->user?->name} ({$clearanceRequest->user?->student_id}) is now FULLY CLEARED! Receipt: {$receiptNumber}");
                }
            }
        }
    }
}
