<?php

namespace App\Console\Commands;

use App\Models\ClearanceApproval;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class RejectAllClearances extends Command
{
    protected $signature = 'clearance:reject-all {student_id?}';

    protected $description = 'Automatically reject all pending clearance requests for all offices.';

    public function handle()
    {
        $studentId = $this->argument('student_id');

        $query = ClearanceApproval::query()->where('status', 'pending');

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

            // If it's the president office, use president ID
            if ($approval->office?->is_final_approver) {
                $dummyStaffId = User::where('role', 'president')->first()?->id ?? 1;
            }

            $approval->update([
                'status' => 'rejected',
                'approved_by' => $dummyStaffId,
                'remarks' => 'Incomplete requirements.',
                'acted_at' => now(),
            ]);

            if ($approval->clearanceRequest && $approval->clearanceRequest->user && $approval->office) {
                NotificationService::send(
                    $approval->clearanceRequest->user,
                    'Clearance request auto-rejected',
                    "Your {$approval->office->name} clearance request has been automatically rejected. Remarks: Incomplete requirements.",
                    '/dashboard'
                );
            }
        }

        $this->info("Successfully auto-rejected {$pendingApprovals->count()} clearance requests.");
    }
}
