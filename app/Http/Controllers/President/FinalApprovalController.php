<?php

namespace App\Http\Controllers\President;

use App\Http\Controllers\Controller;
use App\Models\ClearanceRequest;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinalApprovalController extends Controller
{
    public function index(): Response
    {
        $requests = ClearanceRequest::with([
            'user.course',
            'approvals.office',
        ])
            ->latest()
            ->get()
            ->filter(function ($clearanceRequest) {
                $regularApprovals = $clearanceRequest->approvals->filter(function ($approval) {
                    return ! $approval->office->is_final_approver;
                });

                $presidentApproval = $clearanceRequest->approvals->first(function ($approval) {
                    return $approval->office->is_final_approver;
                });

                return $regularApprovals->isNotEmpty()
                    && $regularApprovals->every(function ($approval) {
                        return $approval->status === 'approved';
                    })
                    && $presidentApproval
                    && $presidentApproval->status === 'pending';
            })
            ->values();

        return Inertia::render('President/FinalApprovals', [
            'requests' => $requests,
        ]);
    }

    public function approve(Request $request, ClearanceRequest $clearanceRequest): RedirectResponse
    {
        $clearanceRequest->load(['user', 'approvals.office']);

        $regularApprovals = $clearanceRequest->approvals->filter(function ($approval) {
            return ! $approval->office->is_final_approver;
        });

        $allRegularOfficesApproved = $regularApprovals->isNotEmpty()
            && $regularApprovals->every(function ($approval) {
                return $approval->status === 'approved';
            });

        if (! $allRegularOfficesApproved) {
            return back()->withErrors([
                'approval' => 'This request is not ready for final approval.',
            ]);
        }

        $presidentApproval = $clearanceRequest->approvals->first(function ($approval) {
            return $approval->office->is_final_approver;
        });

        if (! $presidentApproval) {
            return back()->withErrors([
                'approval' => 'President approval record was not found.',
            ]);
        }

        if ($presidentApproval->status !== 'pending') {
            return back()->withErrors([
                'approval' => 'This request has already been processed.',
            ]);
        }

        $presidentApproval->update([
            'status' => 'approved',
            'remarks' => null,
            'approved_by' => $request->user()->id,
            'acted_at' => now(),
        ]);

        $clearanceRequest->update([
            'status' => 'cleared',
            'cleared_at' => now(),
        ]);

        NotificationService::send(
            $clearanceRequest->user,
            'Clearance fully cleared',
            'Congratulations! Your clearance request has been fully cleared.',
            '/dashboard'
        );

        return redirect()
            ->route('president.final-approvals.index')
            ->with('success', 'Final clearance approval completed successfully.');
    }
}