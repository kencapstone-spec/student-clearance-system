<?php

namespace App\Http\Controllers\ClearanceReceipt;

use App\Http\Controllers\Controller;
use App\Models\ClearanceRequest;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
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

    /**
     * Download the clearance receipt as a real PDF file.
     */
    public function download(Request $request, ClearanceRequest $clearanceRequest)
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

        $verificationUrl = route(
            'clearance-verification.show',
            $clearanceRequest->verification_code,
        );

        $safeReceiptNumber = preg_replace(
            '/[^A-Za-z0-9_-]/',
            '-',
            $clearanceRequest->receipt_number,
        );

        $html = $this->buildReceiptPdfHtml(
            clearanceRequest: $clearanceRequest,
            regularApprovals: $regularApprovals,
            presidentApproval: $presidentApproval,
            verificationUrl: $verificationUrl,
        );

        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->download("clearance-receipt-{$safeReceiptNumber}.pdf");
    }

    private function buildReceiptPdfHtml(
        ClearanceRequest $clearanceRequest,
        $regularApprovals,
        $presidentApproval,
        string $verificationUrl,
    ): string {
        $student = $clearanceRequest->user;
        $course = $student?->course;

        $headerImageDataUri = $this->imageToDataUri(public_path('images/header_receipt.jpg'));
        $qrCodeDataUri = $this->qrCodeToDataUri($verificationUrl);

        $submittedAt = $clearanceRequest->submitted_at?->format('F d, Y h:i A') ?? 'N/A';
        $clearedAt = $clearanceRequest->cleared_at?->format('F d, Y h:i A') ?? 'N/A';

        $approvalRows = $regularApprovals->map(function ($approval) {
            return '
                <tr>
                    <td>'.e($approval->office?->name ?? 'N/A').'</td>
                    <td class="approved">'.e($approval->status).'</td>
                    <td>'.e($approval->approver?->name ?? 'N/A').'</td>
                    <td>'.e($approval->acted_at?->format('F d, Y h:i A') ?? 'N/A').'</td>
                </tr>
            ';
        })->implode('');

        if ($approvalRows === '') {
            $approvalRows = '
                <tr>
                    <td colspan="4">No regular approval records available.</td>
                </tr>
            ';
        }

        $presidentApprovalHtml = '';

        if ($presidentApproval) {
            $presidentApprovalHtml = '
                <div class="section">
                    <h3>Final Approval</h3>

                    <table>
                        <thead>
                            <tr>
                                <th>Office</th>
                                <th>Status</th>
                                <th>Approved By</th>
                                <th>Date Approved</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>'.e($presidentApproval->office?->name ?? 'N/A').'</td>
                                <td class="approved">'.e($presidentApproval->status).'</td>
                                <td>'.e($presidentApproval->approver?->name ?? 'N/A').'</td>
                                <td>'.e($presidentApproval->acted_at?->format('F d, Y h:i A') ?? 'N/A').'</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            ';
        }

        $headerImageHtml = $headerImageDataUri
            ? '<img class="receipt-header" src="'.$headerImageDataUri.'" alt="Talibon Polytechnic College">'
            : '';

        $qrImageHtml = $qrCodeDataUri
            ? '<img class="qr-code" src="'.$qrCodeDataUri.'" alt="Clearance verification QR code">'
            : '<div class="qr-fallback">QR unavailable</div>';

        return '
            <!doctype html>
            <html lang="en">
                <head>
                    <meta charset="utf-8">
                    <title>Clearance Receipt</title>
                    <style>
                        @page {
                            size: A4 portrait;
                            margin: 0.25in;
                        }

                        body {
                            margin: 0;
                            padding: 0;
                            color: #111827;
                            font-family: DejaVu Sans, Arial, sans-serif;
                            font-size: 9px;
                            line-height: 1.12;
                        }

                        .receipt {
                            width: 100%;
                            margin: 0;
                            padding: 0;
                        }

                        .receipt-header {
                            display: block;
                            width: 100%;
                            height: 72px;
                            margin: 0 0 8px;
                        }

                        .title-block {
                            margin-bottom: 7px;
                            padding-bottom: 7px;
                            border-bottom: 1px solid #d1d5db;
                            text-align: center;
                        }

                        .title-block h1 {
                            margin: 0;
                            font-size: 15px;
                            font-weight: bold;
                            text-transform: uppercase;
                        }

                        .title-block p {
                            margin: 3px 0 0;
                            color: #4b5563;
                            font-size: 9px;
                        }

                        .info-table {
                            width: 100%;
                            margin-bottom: 7px;
                            padding-bottom: 6px;
                            border-bottom: 1px solid #d1d5db;
                            border-collapse: collapse;
                        }

                        .info-table td {
                            width: 50%;
                            padding: 2px 6px 4px 0;
                            vertical-align: top;
                            border: 0;
                        }

                        .label {
                            margin: 0 0 1px;
                            color: #4b5563;
                            font-size: 8px;
                            font-weight: bold;
                            text-transform: uppercase;
                        }

                        .value {
                            margin: 0;
                            font-weight: bold;
                        }

                        .status {
                            margin: 0;
                            color: #15803d;
                            font-weight: bold;
                            text-transform: uppercase;
                        }

                        .section {
                            margin-bottom: 7px;
                            padding-bottom: 6px;
                            border-bottom: 1px solid #d1d5db;
                            page-break-inside: avoid;
                        }

                        .section h3 {
                            margin: 0 0 5px;
                            font-size: 10px;
                            font-weight: bold;
                            text-transform: uppercase;
                        }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }

                        th,
                        td {
                            padding: 3px 4px;
                            border-bottom: 1px solid #e5e7eb;
                            text-align: left;
                            vertical-align: top;
                        }

                        th {
                            background: #f3f4f6;
                            font-weight: bold;
                        }

                        .approved {
                            color: #15803d;
                            font-weight: bold;
                            text-transform: uppercase;
                        }

                        .verification-table {
                            width: 100%;
                            margin-top: 5px;
                            border: 1px dashed #9ca3af;
                            border-collapse: collapse;
                        }

                        .verification-table td {
                            border: 0;
                            padding: 7px;
                            vertical-align: top;
                        }

                        .qr-cell {
                            width: 115px;
                            text-align: center;
                        }

                        .qr-code {
                            width: 105px;
                            height: 105px;
                        }

                        .qr-fallback {
                            width: 105px;
                            height: 105px;
                            border: 1px solid #d1d5db;
                            text-align: center;
                            line-height: 105px;
                            color: #6b7280;
                        }

                        .scan-text {
                            margin-top: 2px;
                            font-size: 8px;
                            font-weight: bold;
                            text-align: center;
                        }

                        .mono {
                            margin: 2px 0 6px;
                            font-family: DejaVu Sans Mono, monospace;
                            font-size: 8px;
                            word-break: break-all;
                        }

                        .verification-note {
                            margin: 6px 0 0;
                            color: #4b5563;
                            font-size: 8px;
                        }

                        .signatures {
                            width: 100%;
                            margin-top: 18px;
                            text-align: center;
                            border-collapse: collapse;
                        }

                        .signatures td {
                            width: 50%;
                            padding: 0 24px;
                            border: 0;
                        }

                        .signature-line {
                            height: 18px;
                            margin-bottom: 4px;
                            border-bottom: 1px solid #111827;
                        }

                        .signature-label {
                            font-size: 9px;
                            font-weight: bold;
                        }

                        .footer {
                            margin-top: 10px;
                            color: #6b7280;
                            font-size: 8px;
                            text-align: center;
                        }
                    </style>
                </head>

                <body>
                    <div class="receipt">
                        '.$headerImageHtml.'

                        <div class="title-block">
                            <h1>Clearance Receipt</h1>
                            <p>Official proof of completed student clearance</p>
                        </div>

                        <table class="info-table">
                            <tr>
                                <td>
                                    <p class="label">Receipt Number</p>
                                    <p class="value">'.e($clearanceRequest->receipt_number).'</p>
                                </td>
                                <td>
                                    <p class="label">Clearance Status</p>
                                    <p class="status">'.e($clearanceRequest->status).'</p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p class="label">Semester</p>
                                    <p class="value">'.e($clearanceRequest->semester).'</p>
                                </td>
                                <td>
                                    <p class="label">School Year</p>
                                    <p class="value">'.e($clearanceRequest->school_year).'</p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p class="label">Submitted At</p>
                                    <p class="value">'.e($submittedAt).'</p>
                                </td>
                                <td>
                                    <p class="label">Cleared At</p>
                                    <p class="value">'.e($clearedAt).'</p>
                                </td>
                            </tr>
                        </table>

                        <div class="section">
                            <h3>Student Information</h3>

                            <table class="info-table">
                                <tr>
                                    <td>
                                        <p class="label">Student Name</p>
                                        <p class="value">'.e($student?->name ?? 'N/A').'</p>
                                    </td>
                                    <td>
                                        <p class="label">Student ID</p>
                                        <p class="value">'.e($student?->student_id ?? 'N/A').'</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p class="label">Course Code</p>
                                        <p class="value">'.e($course?->code ?? 'N/A').'</p>
                                    </td>
                                    <td>
                                        <p class="label">Course Name</p>
                                        <p class="value">'.e($course?->name ?? 'N/A').'</p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="section">
                            <h3>Office Clearance Approval Summary</h3>

                            <table>
                                <thead>
                                    <tr>
                                        <th>Office</th>
                                        <th>Status</th>
                                        <th>Approved By</th>
                                        <th>Date Approved</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    '.$approvalRows.'
                                </tbody>
                            </table>
                        </div>

                        '.$presidentApprovalHtml.'

                        <div class="section">
                            <h3>Verification Information</h3>

                            <table class="verification-table">
                                <tr>
                                    <td class="qr-cell">
                                        '.$qrImageHtml.'
                                        <div class="scan-text">Scan to verify</div>
                                    </td>

                                    <td>
                                        <p class="label">Verification Code</p>
                                        <p class="mono">'.e($clearanceRequest->verification_code).'</p>

                                        <p class="label">Verification Link</p>
                                        <p class="mono">'.e($verificationUrl).'</p>

                                        <p class="verification-note">
                                            This receipt is valid only if the QR verification page confirms that the clearance request is cleared.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <table class="signatures">
                            <tr>
                                <td>
                                    <div class="signature-line"></div>
                                    <div class="signature-label">Student Signature</div>
                                </td>
                                <td>
                                    <div class="signature-line"></div>
                                    <div class="signature-label">Authorized Representative</div>
                                </td>
                            </tr>
                        </table>

                        <p class="footer">
                            Generated by the Web-based Student Clearance System with Role-Based Approval.
                        </p>
                    </div>
                </body>
            </html>
        ';
    }

    private function qrCodeToDataUri(string $text): ?string
    {
        try {
            $renderer = new ImageRenderer(
                new RendererStyle(220),
                new SvgImageBackEnd,
            );

            $writer = new Writer($renderer);
            $svg = $writer->writeString($text);

            return 'data:image/svg+xml;base64,'.base64_encode($svg);
        } catch (\Throwable) {
            return null;
        }
    }

    private function imageToDataUri(string $path): ?string
    {
        if (! file_exists($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $mimeType = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'application/octet-stream',
        };

        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }
}
