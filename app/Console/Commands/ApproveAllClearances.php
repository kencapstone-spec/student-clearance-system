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
            $officeStaffId = User::where('role', 'staff')
                ->where('office_id', $approval->office_id)
                ->first()?->id;

            $approval->update([
                'status' => 'approved',
                'approved_by' => $officeStaffId,
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
    }

}
