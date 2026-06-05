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
        $clearanceRequests = $this->readyClearanceRequests();

        return Inertia::render('President/FinalApprovals', [
            'clearanceRequests' => $clearanceRequests,
            'readyCount' => $clearanceRequests->count(),
        ]);
    }

    /**
     * Approve one final clearance request.
     */
    public function approve(Request $request, ClearanceRequest $clearanceRequest)
    {
        $clearanceRequest->load([
            'user',
            'approvals.office',
        ]);

        if (! $this->isReadyForFinalApproval($clearanceRequest)) {
            return back()->with('error', 'This clearance request is not yet ready for final approval.');
        }

        $this->finalizeClearanceRequest(
            clearanceRequest: $clearanceRequest,
            presidentId: $request->user()->id
        );

        return back()->with('success', 'Clearance request has been finally approved.');
    }

    /**
     * Auto approve all clearance requests that are ready for final approval.
     */
    public function approveAll(Request $request)
    {
        $clearanceRequests = $this->readyClearanceRequests();

        if ($clearanceRequests->isEmpty()) {
            return back()->with('error', 'There are no clearance requests ready for final approval.');
        }

        foreach ($clearanceRequests as $clearanceRequest) {
            $this->finalizeClearanceRequest(
                clearanceRequest: $clearanceRequest,
                presidentId: $request->user()->id
            );
        }

        return back()->with(
            'success',
            $clearanceRequests->count() . ' clearance request(s) have been automatically approved.'
        );
    }

    /**
     * Get all clearance requests ready for President final approval.
     *
     * Filtering is done entirely at the database level to avoid loading every
     * clearance request into memory (which would cause fatal memory exhaustion
     * as the table grows over multiple semesters).
     *
     * A request is "ready" when:
     *   1. All of its non-final-approver approvals are 'approved'.
     *   2. Its president (final approver) approval is still 'pending'.
     *   3. The overall request is not yet 'cleared'.
     */
    private function readyClearanceRequests()
    {
        return ClearanceRequest::with([
            'user.course',
            'approvals.office',
            'approvals.approver',
        ])
            // Must NOT already be cleared
            ->where('status', '!=', 'cleared')
            // Must have at least one regular (non-final-approver) approval
            ->whereHas('approvals', function ($query) {
                $query->whereHas('office', function ($officeQuery) {
                    $officeQuery->where('is_final_approver', false);
                });
            })
            // All regular approvals must be 'approved'
            ->whereDoesntHave('approvals', function ($query) {
                $query->whereHas('office', function ($officeQuery) {
                    $officeQuery->where('is_final_approver', false);
                })->where('status', '!=', 'approved');
            })
            // The president approval must exist and still be 'pending'
            ->whereHas('approvals', function ($query) {
                $query->whereHas('office', function ($officeQuery) {
                    $officeQuery->where('is_final_approver', true);
                })->where('status', 'pending');
            })
            ->latest()
            ->get();
    }

    /**
     * Check if a clearance request is ready for final approval.
     */
    private function isReadyForFinalApproval(ClearanceRequest $clearanceRequest): bool
    {
        $regularApprovals = $clearanceRequest->approvals->filter(function ($approval) {
            return ! $approval->office?->is_final_approver;
        });

        $presidentApproval = $clearanceRequest->approvals->first(function ($approval) {
            return $approval->office?->is_final_approver;
        });

        return $regularApprovals->isNotEmpty()
            && $regularApprovals->every(fn ($approval) => $approval->status === 'approved')
            && $presidentApproval
            && $presidentApproval->status === 'pending'
            && $clearanceRequest->status !== 'cleared';
    }

    /**
     * Finalize a clearance request, generate receipt details, and notify the student.
     */
    private function finalizeClearanceRequest(ClearanceRequest $clearanceRequest, int $presidentId): void
    {
        $clearanceRequest->loadMissing([
            'user',
            'approvals.office',
        ]);

        $presidentApproval = $clearanceRequest->approvals->first(function ($approval) {
            return $approval->office?->is_final_approver;
        });

        if (! $presidentApproval) {
            return;
        }

        $presidentApproval->update([
            'status' => 'approved',
            'remarks' => null,
            'approved_by' => $presidentId,
            'acted_at' => now(),
        ]);

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

        NotificationService::send(
            user: $clearanceRequest->user,
            title: 'Clearance Fully Approved',
            message: 'Your clearance request has been fully approved by the College President.',
            link: '/dashboard'
        );
    }
}