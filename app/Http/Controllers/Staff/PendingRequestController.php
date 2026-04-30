<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ClearanceApproval;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PendingRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user()->load('office');

        $approvals = ClearanceApproval::query()
            ->where('office_id', $user->office_id)
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->with([
                'clearanceRequest.user.course',
                'office',
                'approver',
            ])
            ->latest()
            ->get();

        return Inertia::render('staff/PendingRequests', [
            'staff' => $user,
            'approvals' => $approvals,
        ]);
    }

    public function approve(Request $request, ClearanceApproval $approval): RedirectResponse
    {
        $user = $request->user();

        if ($approval->office_id !== $user->office_id) {
            abort(403);
        }

        if ($approval->status !== 'pending') {
            return back()->with('error', 'Only pending clearance requests can be approved.');
        }

        $approval->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'remarks' => null,
            'acted_at' => now(),
        ]);

        $approval->load(['clearanceRequest.user', 'office']);

        NotificationService::send(
            $approval->clearanceRequest->user,
            'Clearance request approved',
            "Your {$approval->office->name} clearance request has been approved.",
            '/dashboard'
        );

        $this->notifyPresidentIfReadyForFinalApproval($approval);

        return back()->with('success', 'Clearance request approved successfully.');
    }

    public function reject(Request $request, ClearanceApproval $approval): RedirectResponse
    {
        $user = $request->user();

        if ($approval->office_id !== $user->office_id) {
            abort(403);
        }

        if ($approval->status !== 'pending') {
            return back()->with('error', 'Only pending clearance requests can be rejected.');
        }

        $validated = $request->validate([
            'remarks' => ['required', 'string', 'max:1000'],
        ]);

        $approval->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'remarks' => $validated['remarks'],
            'acted_at' => now(),
        ]);

        $approval->load(['clearanceRequest.user', 'office']);

        NotificationService::send(
            $approval->clearanceRequest->user,
            'Clearance request rejected',
            "Your {$approval->office->name} clearance request was rejected. Please check the remarks.",
            '/dashboard'
        );

        return back()->with('success', 'Clearance request rejected successfully.');
    }

    private function notifyPresidentIfReadyForFinalApproval(ClearanceApproval $approval): void
    {
        $clearanceRequest = $approval->clearanceRequest;

        $clearanceRequest->load(['user', 'approvals.office']);

        $regularApprovals = $clearanceRequest->approvals->filter(function ($approval) {
            return ! $approval->office->is_final_approver;
        });

        $allRegularOfficesApproved = $regularApprovals->isNotEmpty()
            && $regularApprovals->every(function ($approval) {
                return $approval->status === 'approved';
            });

        if (! $allRegularOfficesApproved) {
            return;
        }

        $presidentApproval = $clearanceRequest->approvals->first(function ($approval) {
            return $approval->office->is_final_approver;
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