<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

class ApproveAllClearances extends Command
{
    protected $signature = 'clearance:approve-all {student_id?}';
    protected $description = 'Automatically approve all pending clearance requests for all offices.';

    public function handle()
    {
        $studentId = $this->argument('student_id');

        $query = \App\Models\ClearanceApproval::query()->where('status', 'pending');

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
            $dummyStaffId = \App\Models\User::where('role', 'staff')->first()?->id ?? 1;

            // If it's the president office, use president ID
            if ($approval->office?->is_final_approver) {
                $dummyStaffId = \App\Models\User::where('role', 'president')->first()?->id ?? 1;
            }

            $approval->update([
                'status' => 'approved',
                'approved_by' => $dummyStaffId,
                'remarks' => null,
                'acted_at' => now(),
            ]);

            if ($approval->office && !$approval->office->is_final_approver) {
                $this->notifyPresidentIfReadyForFinalApproval($approval);
            }
        }

        // Check if final approval was just opened, and if we should auto-approve that too.
        $this->approvePresidentPendings($studentId);

        $this->info("Successfully auto-approved {$pendingApprovals->count()} clearance requests.");
    }

    private function approvePresidentPendings($studentId)
    {
        $query = \App\Models\ClearanceApproval::query()
            ->where('status', 'pending')
            ->whereHas('office', function ($q) {
                $q->where('is_final_approver', true);
            });

        if ($studentId) {
            $query->whereHas('clearanceRequest.user', function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            });
        }

        $presidentApprovals = $query->get();

        foreach ($presidentApprovals as $approval) {
            $dummyPresidentId = \App\Models\User::where('role', 'president')->first()?->id ?? 1;
            
            $approval->update([
                'status' => 'approved',
                'approved_by' => $dummyPresidentId,
                'remarks' => null,
                'acted_at' => now(),
            ]);

            $receiptNumber = $approval->clearanceRequest->receipt_number
                ?: sprintf('TPC-CLR-%s-%06d', now()->format('Y'), $approval->clearanceRequest->id);

            $verificationCode = $approval->clearanceRequest->verification_code;

            if (! $verificationCode) {
                do {
                    $verificationCode = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(32));
                } while (\App\Models\ClearanceRequest::where('verification_code', $verificationCode)->exists());
            }

            $approval->clearanceRequest->update([
                'status' => 'cleared',
                'cleared_at' => now(),
                'receipt_number' => $receiptNumber,
                'verification_code' => $verificationCode,
            ]);
            
            $this->info("Automatically granted Final Approval from the College President.");
        }
    }

    private function notifyPresidentIfReadyForFinalApproval(\App\Models\ClearanceApproval $approval): void
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
    }
}
