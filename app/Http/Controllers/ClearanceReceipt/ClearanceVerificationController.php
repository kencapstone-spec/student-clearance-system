<?php

namespace App\Http\Controllers\ClearanceReceipt;

use App\Http\Controllers\Controller;
use App\Models\ClearanceRequest;
use Inertia\Inertia;

class ClearanceVerificationController extends Controller
{
    /**
     * Display the public clearance verification result.
     */
    public function show(string $verificationCode)
    {
        $clearanceRequest = ClearanceRequest::query()
            ->where('verification_code', $verificationCode)
            ->with([
                'user.course',
                'approvals.office',
                'approvals.approver',
            ])
            ->first();

        if (! $clearanceRequest) {
            return Inertia::render('ClearanceVerification/Show', [
                'isValid' => false,
                'message' => 'No clearance record was found for this verification code.',
                'clearanceRequest' => null,
                'student' => null,
                'presidentApproval' => null,
            ]);
        }

        $isValid = $clearanceRequest->status === 'cleared'
            && filled($clearanceRequest->receipt_number)
            && filled($clearanceRequest->verification_code)
            && filled($clearanceRequest->cleared_at);

        $presidentApproval = $clearanceRequest->approvals
            ->first(fn ($approval) => $approval->office?->is_final_approver);

        return Inertia::render('ClearanceVerification/Show', [
            'isValid' => $isValid,
            'message' => $isValid
                ? 'This clearance receipt is valid and fully cleared.'
                : 'This clearance record exists but is not fully cleared.',
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
            'presidentApproval' => $presidentApproval ? [
                'office' => $presidentApproval->office?->name,
                'status' => $presidentApproval->status,
                'approver' => $presidentApproval->approver?->name,
                'acted_at' => $presidentApproval->acted_at?->format('F d, Y h:i A'),
            ] : null,
        ]);
    }
}