<?php

namespace App\Http\Controllers\President;

use App\Http\Controllers\Controller;
use App\Models\ClearanceRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FinalApprovalController extends Controller
{
    /**
     * Display clearance requests that are ready for President final approval.
     */
    public function index()
    {
        $clearanceRequests = ClearanceRequest::with([
            'user.course',
            'approvals.office',
            'approvals.approver',
        ])
            ->latest()
            ->get()
            ->filter(function ($clearanceRequest) {
                $regularApprovals = $clearanceRequest->approvals->filter(function ($approval) {
                    return ! $approval->office?->is_final_approver;
                });

                $presidentApproval = $clearanceRequest->approvals->first(function ($approval) {
                    return $approval->office?->is_final_approver;
                });

                return $regularApprovals->isNotEmpty()
                    && $regularApprovals->every(fn($approval) => $approval->status === 'approved')
                    && $presidentApproval
                    && $presidentApproval->status === 'pending'
                    && $clearanceRequest->status !== 'cleared';
            })
            ->values();

        return Inertia::render('President/FinalApprovals', [
            'clearanceRequests' => $clearanceRequests,
            'readyCount' => $clearanceRequests->count(),
        ]);
    }

    /**
     * Approve the final clearance request.
     */
    public function approve(Request $request, ClearanceRequest $clearanceRequest)
    {
        $clearanceRequest->load([
            'user',
            'approvals.office',
        ]);

        $regularApprovals = $clearanceRequest->approvals->filter(function ($approval) {
            return ! $approval->office?->is_final_approver;
        });

        $presidentApproval = $clearanceRequest->approvals->first(function ($approval) {
            return $approval->office?->is_final_approver;
        });

        if ($regularApprovals->isEmpty()) {
            return back()->with('error', 'This clearance request has no regular office approvals.');
        }

        if (! $regularApprovals->every(fn($approval) => $approval->status === 'approved')) {
            return back()->with('error', 'This clearance request is not yet ready for final approval.');
        }

        if (! $presidentApproval) {
            return back()->with('error', 'President approval record was not found.');
        }

        if ($presidentApproval->status !== 'pending') {
            return back()->with('error', 'This clearance request has already been processed.');
        }

        $presidentApproval->update([
            'status' => 'approved',
            'remarks' => null,
            'approved_by' => $request->user()->id,
            'acted_at' => now(),
        ]);

        $receiptNumber = $clearanceRequest->receipt_number
            ?: sprintf('TPC-CLR-%s-%06d', now()->format('Y'), $clearanceRequest->id);

        do {
            $verificationCode = Str::upper(Str::random(32));
        } while (ClearanceRequest::where('verification_code', $verificationCode)->exists());

        $clearanceRequest->update([
            'status' => 'cleared',
            'cleared_at' => now(),
            'receipt_number' => $receiptNumber,
            'verification_code' => $clearanceRequest->verification_code ?: $verificationCode,
        ]);

        NotificationService::send(
            user: $clearanceRequest->user,
            title: 'Clearance Fully Approved',
            message: 'Your clearance request has been fully approved by the College President.',
            link: '/dashboard'
        );

        return back()->with('success', 'Clearance request has been finally approved.');
    }
}
