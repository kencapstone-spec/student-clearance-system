<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import QRCode from 'qrcode';
import { onMounted, ref } from 'vue';

type ClearanceRequest = {
    id: number;
    semester: string;
    school_year: string;
    status: string;
    submitted_at: string | null;
    cleared_at: string | null;
    receipt_number: string;
    verification_code: string;
};

type Student = {
    name: string;
    student_id: string;
    course: string | null;
    course_name: string | null;
};

type Approval = {
    office: string | null;
    status: string;
    approver: string | null;
    acted_at: string | null;
};

const props = defineProps<{
    clearanceRequest: ClearanceRequest;
    student: Student;
    regularApprovals: Approval[];
    presidentApproval: Approval | null;
    headerImageDataUri?: string | null;
}>();

const qrCodeDataUrl = ref<string | null>(null);
const verificationUrl = ref('');

onMounted(async () => {
    verificationUrl.value = `${window.location.origin}/verify-clearance/${props.clearanceRequest.verification_code}`;

    qrCodeDataUrl.value = await QRCode.toDataURL(verificationUrl.value, {
        width: 120,
        margin: 1,
    });
});

const getReceiptStyles = () => {
    return String.raw`
        @page {
            size: A4 portrait;
            margin: 0.25in;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: white;
            color: #111827;
            font-family: Arial, sans-serif;
        }

        * {
            box-sizing: border-box;
        }

#printable-clearance-receipt {
    width: 100%;
    max-width: 7.25in;
    margin: 0 auto;
    padding: 0.12in 0.16in 0;
    box-shadow: none;
    border-radius: 0;
    background: white;
    font-size: 9px;
    line-height: 1.08;
    page-break-inside: avoid;
}

.receipt-header-image {
    display: block;
    width: calc(100% + 0.32in);
    height: 72px;
    max-height: none;
    object-fit: fill;
    margin: -0.12in -0.16in 7px;
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
}

        .receipt-title-block {
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 7px;
            text-align: center;
        }

        .receipt-title {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .receipt-subtitle {
            margin: 3px 0 0;
            font-size: 10px;
            color: #4b5563;
        }

        .receipt-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            column-gap: 36px;
            row-gap: 5px;
            border-bottom: 1px solid #d1d5db;
            padding: 7px 0;
            font-size: 10px;
        }

        .receipt-section {
            border-bottom: 1px solid #d1d5db;
            padding: 7px 0;
        }

        .receipt-section.no-border {
            border-bottom: 0;
        }

        .section-title {
            margin: 0;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .student-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            column-gap: 36px;
            row-gap: 5px;
            margin-top: 6px;
            font-size: 10px;
        }

        .final-approval-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr 1.2fr 1.1fr;
            column-gap: 18px;
            margin-top: 6px;
            font-size: 10px;
        }

        .label {
            margin: 0;
            color: #4b5563;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .value {
            margin: 2px 0 0;
            font-weight: 600;
        }

        .status-pill {
            display: inline-flex;
            border-radius: 999px;
            background: #dcfce7;
            color: #15803d;
            padding: 1px 6px;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .approval-table-wrap {
            margin-top: 6px;
            overflow: hidden;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            line-height: 1.05;
        }

        thead {
            background: #f3f4f6;
        }

        th,
        td {
            padding: 3px 5px;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            font-weight: 700;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .approved-text {
            color: #15803d;
            font-weight: 700;
            text-transform: uppercase;
        }

        .verification-box {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: 12px;
            margin-top: 6px;
            padding: 8px;
            border: 1px dashed #9ca3af;
            border-radius: 6px;
        }

        .qr-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .qr-image {
            width: 105px;
            height: 105px;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .qr-placeholder {
            display: flex;
            width: 105px;
            height: 105px;
            align-items: center;
            justify-content: center;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            color: #6b7280;
            font-size: 10px;
        }

        .scan-text {
            margin: 3px 0 0;
            color: #4b5563;
            font-size: 8.5px;
            font-weight: 700;
            text-align: center;
        }

        .verification-text {
            font-size: 9px;
        }

        .verification-code,
        .verification-link {
            margin: 2px 0 0;
            overflow-wrap: anywhere;
            word-break: break-word;
            font-family: "Courier New", monospace;
            font-size: 8.5px;
            font-weight: 700;
        }

        .verification-link {
            color: #1d4ed8;
        }

        .verification-note {
            margin: 6px 0 0;
            color: #4b5563;
            font-size: 8.5px;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 48px;
            margin-top: 18px;
            text-align: center;
        }

        .signature-line {
            width: 210px;
            height: 18px;
            margin: 0 auto;
            border-bottom: 1px solid #111827;
        }

        .signature-label {
            margin: 5px 0 0;
            font-size: 9px;
            font-weight: 700;
        }

        .generated-text {
            margin: 12px 0 0;
            color: #6b7280;
            font-size: 8.5px;
            text-align: center;
        }

        table,
        thead,
        tbody,
        tr,
        th,
        td,
        img,
        .receipt-section,
        .verification-box,
        .signature-grid {
            page-break-inside: avoid;
        }
    `;
};

const printReceiptDocument = (title: string) => {
    const receiptElement = document.getElementById(
        'printable-clearance-receipt',
    );

    if (!receiptElement) {
        window.print();

        return;
    }

    const existingIframe = document.getElementById('receipt-print-frame');

    if (existingIframe) {
        existingIframe.remove();
    }

    const printFrame = document.createElement('iframe');

    printFrame.id = 'receipt-print-frame';
    printFrame.title = title;
    printFrame.style.position = 'fixed';
    printFrame.style.right = '0';
    printFrame.style.bottom = '0';
    printFrame.style.width = '0';
    printFrame.style.height = '0';
    printFrame.style.border = '0';
    printFrame.style.visibility = 'hidden';

    document.body.appendChild(printFrame);

    const frameWindow = printFrame.contentWindow;
    const frameDocument = printFrame.contentDocument ?? frameWindow?.document;

    if (!frameWindow || !frameDocument) {
        printFrame.remove();
        window.print();

        return;
    }

    frameDocument.open();
    frameDocument.write(`
        <!doctype html>
        <html>
            <head>
                <title>${title}</title>
                <style>
                    ${getReceiptStyles()}
                </style>
            </head>
            <body>
                ${receiptElement.outerHTML}
            </body>
        </html>
    `);
    frameDocument.close();

    let hasPrinted = false;
    let fallbackTimer: number | null = null;

    const cleanup = () => {
        if (fallbackTimer !== null) {
            window.clearTimeout(fallbackTimer);
            fallbackTimer = null;
        }

        window.setTimeout(() => {
            printFrame.remove();
        }, 1000);
    };

    const printFrameContent = () => {
        if (hasPrinted) {
            return;
        }

        hasPrinted = true;

        frameWindow.focus();
        frameWindow.print();
        cleanup();
    };

    const images = Array.from(frameDocument.images);

    if (images.length === 0) {
        fallbackTimer = window.setTimeout(printFrameContent, 300);

        return;
    }

    let loadedImages = 0;

    const markImageLoaded = () => {
        loadedImages += 1;

        if (loadedImages >= images.length) {
            if (fallbackTimer !== null) {
                window.clearTimeout(fallbackTimer);
                fallbackTimer = null;
            }

            fallbackTimer = window.setTimeout(printFrameContent, 300);
        }
    };

    images.forEach((image) => {
        if (image.complete) {
            markImageLoaded();

            return;
        }

        image.onload = markImageLoaded;
        image.onerror = markImageLoaded;
    });

    fallbackTimer = window.setTimeout(printFrameContent, 1500);
};

const printReceipt = () => {
    printReceiptDocument('Clearance Receipt - Print');
};

const downloadPdf = () => {
    window.location.href = `/clearance-receipts/${props.clearanceRequest.id}/download`;
};

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit('/dashboard');
    }
};
</script>
<template>
    <Head title="Printable Clearance Receipt" />

    <div
        class="min-h-screen bg-slate-100/70 p-3 text-slate-900 sm:p-4 md:p-6 print:min-h-0 print:bg-white print:p-0"
    >
        <div class="receipt-container mx-auto max-w-3xl">
            <div
                class="mb-4 flex flex-wrap items-center justify-between gap-2 print:hidden"
            >
                <button
                    type="button"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                    @click="goBack"
                >
                    Back
                </button>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
                        @click="printReceipt"
                    >
                        Print
                    </button>

                    <button
                        type="button"
                        class="rounded-lg border border-blue-600 bg-white px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm hover:bg-blue-50"
                        @click="downloadPdf"
                    >
                        Download PDF
                    </button>
                </div>
            </div>

            <div
                id="printable-clearance-receipt"
                class="receipt-page rounded-xl bg-white p-5 shadow print:rounded-none print:p-0 print:shadow-none"
            >
                <img
                    :src="props.headerImageDataUri || ''"
                    alt="Talibon Polytechnic College"
                    class="receipt-header-image mb-3 h-[72px] w-full object-fill"
                />

                <div
                    class="receipt-title-block border-b border-gray-300 pb-2 text-center"
                >
                    <h2 class="receipt-title text-lg font-bold uppercase">
                        Clearance Receipt
                    </h2>

                    <p class="receipt-subtitle text-[11px] text-gray-600">
                        Official proof of completed student clearance
                    </p>
                </div>

                <div
                    class="receipt-info-grid mt-2 grid grid-cols-2 gap-x-6 gap-y-1.5 border-b border-gray-300 pb-2 text-[11px]"
                >
                    <div>
                        <p class="label font-semibold text-gray-500 uppercase">
                            Receipt Number
                        </p>
                        <p class="value font-bold">
                            {{ clearanceRequest.receipt_number }}
                        </p>
                    </div>

                    <div>
                        <p class="label font-semibold text-gray-500 uppercase">
                            Clearance Status
                        </p>
                        <p
                            class="status-pill inline-flex rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-700 uppercase"
                        >
                            {{ clearanceRequest.status }}
                        </p>
                    </div>

                    <div>
                        <p class="label font-semibold text-gray-500 uppercase">
                            Semester
                        </p>
                        <p class="value font-medium">
                            {{ clearanceRequest.semester }}
                        </p>
                    </div>

                    <div>
                        <p class="label font-semibold text-gray-500 uppercase">
                            School Year
                        </p>
                        <p class="value font-medium">
                            {{ clearanceRequest.school_year }}
                        </p>
                    </div>

                    <div>
                        <p class="label font-semibold text-gray-500 uppercase">
                            Submitted At
                        </p>
                        <p class="value font-medium">
                            {{ clearanceRequest.submitted_at ?? 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <p class="label font-semibold text-gray-500 uppercase">
                            Approved At
                        </p>
                        <p class="value font-medium">
                            {{ clearanceRequest.cleared_at ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <div class="receipt-section mt-2 border-b border-gray-300 pb-2">
                    <h3 class="section-title text-xs font-bold uppercase">
                        Student Information
                    </h3>

                    <div
                        class="student-grid mt-1.5 grid grid-cols-2 gap-x-6 gap-y-1.5 text-[11px]"
                    >
                        <div>
                            <p
                                class="label font-semibold text-gray-500 uppercase"
                            >
                                Student Name
                            </p>
                            <p class="value font-medium">
                                {{ student.name }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="label font-semibold text-gray-500 uppercase"
                            >
                                Student ID
                            </p>
                            <p class="value font-medium">
                                {{ student.student_id }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="label font-semibold text-gray-500 uppercase"
                            >
                                Course Code
                            </p>
                            <p class="value font-medium">
                                {{ student.course ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="label font-semibold text-gray-500 uppercase"
                            >
                                Course Name
                            </p>
                            <p class="value font-medium">
                                {{ student.course_name ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="receipt-section mt-2 border-b border-gray-300 pb-2">
                    <h3 class="section-title text-xs font-bold uppercase">
                        Office Clearance Approval Summary
                    </h3>

                    <div
                        class="approval-table-wrap mt-1.5 overflow-hidden rounded-md border border-gray-300"
                    >
                        <table
                            class="w-full border-collapse text-[9.5px] leading-tight"
                        >
                            <thead class="bg-gray-100">
                                <tr>
                                    <th
                                        class="border-b border-gray-300 px-1.5 py-0.5 text-left font-semibold"
                                    >
                                        Office
                                    </th>
                                    <th
                                        class="border-b border-gray-300 px-1.5 py-0.5 text-left font-semibold"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="border-b border-gray-300 px-1.5 py-0.5 text-left font-semibold"
                                    >
                                        Approved By
                                    </th>
                                    <th
                                        class="border-b border-gray-300 px-1.5 py-0.5 text-left font-semibold"
                                    >
                                        Date Approved
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="approval in regularApprovals"
                                    :key="
                                        approval.office ??
                                        approval.acted_at ??
                                        approval.status
                                    "
                                    class="border-b border-gray-200 last:border-b-0"
                                >
                                    <td class="px-1.5 py-0.5">
                                        {{ approval.office ?? 'N/A' }}
                                    </td>
                                    <td
                                        class="approved-text px-1.5 py-0.5 font-semibold text-green-700 uppercase"
                                    >
                                        {{ approval.status }}
                                    </td>
                                    <td class="px-1.5 py-0.5">
                                        {{ approval.approver ?? 'N/A' }}
                                    </td>
                                    <td class="px-1.5 py-0.5">
                                        {{ approval.acted_at ?? 'N/A' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div
                    v-if="presidentApproval"
                    class="receipt-section mt-2 border-b border-gray-300 pb-2"
                >
                    <h3 class="section-title text-xs font-bold uppercase">
                        Final Approval
                    </h3>

                    <div
                        class="final-approval-grid mt-1.5 grid grid-cols-4 gap-x-4 text-[11px]"
                    >
                        <div>
                            <p
                                class="label font-semibold text-gray-500 uppercase"
                            >
                                Office
                            </p>
                            <p class="value font-medium">
                                {{ presidentApproval.office ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="label font-semibold text-gray-500 uppercase"
                            >
                                Status
                            </p>
                            <p
                                class="approved-text value font-semibold text-green-700 uppercase"
                            >
                                {{ presidentApproval.status }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="label font-semibold text-gray-500 uppercase"
                            >
                                Approved By
                            </p>
                            <p class="value font-medium">
                                {{ presidentApproval.approver ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="label font-semibold text-gray-500 uppercase"
                            >
                                Date Approved
                            </p>
                            <p class="value font-medium">
                                {{ presidentApproval.acted_at ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="receipt-section no-border mt-2">
                    <h3 class="section-title text-xs font-bold uppercase">
                        Verification Information
                    </h3>

                    <div
                        class="verification-box mt-1.5 grid grid-cols-[120px_1fr] gap-3 rounded-md border border-dashed border-gray-400 p-2"
                    >
                        <div
                            class="qr-area flex flex-col items-center justify-center"
                        >
                            <img
                                v-if="qrCodeDataUrl"
                                :src="qrCodeDataUrl"
                                alt="Clearance verification QR code"
                                class="qr-image h-28 w-28"
                            />

                            <div
                                v-else
                                class="qr-placeholder flex h-28 w-28 items-center justify-center rounded-md border border-gray-300 text-xs text-gray-500"
                            >
                                Generating QR...
                            </div>

                            <p
                                class="scan-text mt-0.5 text-center text-[9px] font-semibold text-gray-600"
                            >
                                Scan to verify
                            </p>
                        </div>

                        <div class="verification-text text-[10px]">
                            <p
                                class="label font-semibold text-gray-500 uppercase"
                            >
                                Verification Code
                            </p>
                            <p
                                class="verification-code font-mono text-[9.5px] font-semibold break-all"
                            >
                                {{ clearanceRequest.verification_code }}
                            </p>

                            <p
                                class="label mt-1.5 font-semibold text-gray-500 uppercase"
                            >
                                Verification Link
                            </p>
                            <p
                                class="verification-link font-mono text-[9.5px] font-semibold break-all text-blue-700"
                            >
                                {{ verificationUrl }}
                            </p>

                            <p
                                class="verification-note mt-1.5 text-[9.5px] text-gray-600"
                            >
                                This receipt is valid only if the QR
                                verification page confirms that the clearance
                                request is approved.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="signature-grid mt-3 grid grid-cols-2 gap-8 text-center"
                >
                    <div>
                        <div
                            class="signature-line mx-auto h-6 w-52 border-b border-gray-900"
                        ></div>
                        <p
                            class="signature-label mt-1 text-[10px] font-semibold"
                        >
                            Student Signature
                        </p>
                    </div>

                    <div>
                        <div
                            class="signature-line mx-auto h-6 w-52 border-b border-gray-900"
                        ></div>
                        <p
                            class="signature-label mt-1 text-[10px] font-semibold"
                        >
                            Authorized Representative
                        </p>
                    </div>
                </div>

                <p
                    class="generated-text mt-2 text-center text-[9px] text-gray-500"
                >
                    Generated by the Web-based Student Clearance System with
                    Role-Based Approval.
                </p>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 0;
    }

    html,
    body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }

    body * {
        visibility: hidden !important;
    }

    #printable-clearance-receipt,
    #printable-clearance-receipt * {
        visibility: visible !important;
    }

    #printable-clearance-receipt {
        position: relative !important;
        top: auto !important;
        left: auto !important;
        width: 100% !important;
        max-width: 7.25in !important;
        margin: 0 auto !important;
        padding: 0.12in 0.16in 0 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        font-size: 9px;
        line-height: 1.08;
        page-break-inside: avoid;
    }

    .receipt-container {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .receipt-page {
        width: 100% !important;
        max-width: 7.25in !important;
        margin: 0 auto !important;
        padding: 0.12in 0.16in 0 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        font-size: 9px;
        line-height: 1.08;
        page-break-inside: avoid;
    }

    .receipt-header-image {
        display: block !important;
        width: calc(100% + 0.32in) !important;
        height: 72px !important;
        max-height: none !important;
        object-fit: fill !important;
        margin: -0.12in -0.16in 7px !important;
    }

    table,
    thead,
    tbody,
    tr,
    th,
    td,
    img {
        page-break-inside: avoid !important;
    }
}
</style>
