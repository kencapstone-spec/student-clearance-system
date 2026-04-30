<?php

namespace App\Http\Controllers\ClearanceReceipt;

use App\Http\Controllers\Controller;
use App\Models\ClearanceRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClearanceReceiptController extends Controller
{
    /**
     * Display the printable clearance receipt.
     */
    public function show(Request $request, ClearanceRequest $clearanceRequest)
    {
        $user = $request->user();

        $clearanceRequest->load([
            'user.course',
            'approvals.office',
            'approvals.approver',
        ]);

        $canViewReceipt =
            $user->id === $clearanceRequest->user_id ||
            in_array($user->role, ['admin', 'president']);

        if (! $canViewReceipt) {
            abort(403);
        }

        if ($clearanceRequest->status !== 'cleared') {
            return back()->with('error', 'Receipt is only available after final clearance approval.');
        }

        if (! $clearanceRequest->receipt_number || ! $clearanceRequest->verification_code) {
            return back()->with('error', 'Receipt details are not yet available.');
        }

        $regularApprovals = $clearanceRequest->approvals
            ->filter(fn ($approval) => ! $approval->office?->is_final_approver)
            ->values();

        $presidentApproval = $clearanceRequest->approvals
            ->first(fn ($approval) => $approval->office?->is_final_approver);

        return Inertia::render('ClearanceReceipt/Show', [
            'clearanceRequest' => [
                'id' => $clearanceRequest->id,
                'semester' => $clearanceRequest->semester,
                'school_year' => $clearanceRequest->school_year,
                'status' => $clearanceRequest->status,
                'submitted_at' => $clearanceRequest->submitted_at?->format('F d, Y h:i A'),
                'cleared_at' => $clearanceRequest->cleared_at?->format('F d, Y h:i A'),
                'receipt_number' => $clearanceRequest->receipt_number,
                'verification_code' => $clearanceRequest->verification_code,
            ],
            'student' => [
                'name' => $clearanceRequest->user?->name,
                'student_id' => $clearanceRequest->user?->student_id,
                'course' => $clearanceRequest->user?->course?->code,
                'course_name' => $clearanceRequest->user?->course?->name,
            ],
            'regularApprovals' => $regularApprovals->map(fn ($approval) => [
                'office' => $approval->office?->name,
                'status' => $approval->status,
                'approver' => $approval->approver?->name,
                'acted_at' => $approval->acted_at?->format('F d, Y h:i A'),
            ]),
            'presidentApproval' => $presidentApproval ? [
                'office' => $presidentApproval->office?->name,
                'status' => $presidentApproval->status,
                'approver' => $presidentApproval->approver?->name,
                'acted_at' => $presidentApproval->acted_at?->format('F d, Y h:i A'),
            ] : null,
        ]);
    }
}