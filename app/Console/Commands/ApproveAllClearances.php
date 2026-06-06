<?php

namespace App\Console\Commands;

use App\Models\ClearanceApproval;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class ApproveAllClearances extends Command
{
    protected $signature = 'clearance:approve-all {student_id?}';

    protected $description = 'Automatically approve all pending clearance requests for all offices.';

    public function handle()
    {
        $studentId = $this->argument('student_id');

        $query = ClearanceApproval::query()
            ->where('status', 'pending')
            ->whereHas('office', function ($q) {
                $q->where('is_final_approver', false);
            });

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

        foreach ($pendingApprovals as $approval) {
            // Failsafe in case there are no staff users
            $dummyStaffId = User::where('role', 'staff')->first()?->id ?? 1;

            $approval->update([
                'status' => 'approved',
                'approved_by' => $dummyStaffId,
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

            if ($approval->office && ! $approval->office->is_final_approver) {
                $this->notifyPresidentIfReadyForFinalApproval($approval);
            }
        }

        $this->info("Successfully auto-approved {$pendingApprovals->count()} clearance requests.");
    }

    private function notifyPresidentIfReadyForFinalApproval(ClearanceApproval $approval): void
    {
        $clearanceRequest = $approval->clearanceRequest;
        $clearanceRequest->load(['user', 'approvals.office']);

        $regularApprovals = $clearanceRequest->approvals->filter(function ($app) {
            return ! $app->office?->is_final_approver;
        });

        $allRegularOfficesApproved = $regularApprovals->isNotEmpty()
            && $regularApprovals->every(function ($app) {
                return $app->status === 'approved';
            });

        if (! $allRegularOfficesApproved) {
            return;
        }

        $presidentApproval = $clearanceRequest->approvals->first(function ($app) {
            return $app->office?->is_final_approver;
        });

        if (! $presidentApproval || $presidentApproval->status !== 'not_requested') {
            return;
        }

        $presidentApproval->update([
            'status' => 'pending',
        ]);

        User::where('role', 'president')
            ->where('is_active', true)
            ->get()
            ->each(function (User $president) use ($clearanceRequest) {
                NotificationService::send(
                    $president,
                    'Clearance ready for final approval',
                    "{$clearanceRequest->user->name}'s clearance request is ready for final approval.",
                    '/president/final-approvals'
                );
            });
    }
}
