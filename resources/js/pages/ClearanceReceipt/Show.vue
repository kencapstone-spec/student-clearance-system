<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
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
}>();

const qrCodeDataUrl = ref<string | null>(null);
const verificationUrl = ref('');

onMounted(async () => {
    verificationUrl.value = `${window.location.origin}/verify-clearance/${props.clearanceRequest.verification_code}`;

    qrCodeDataUrl.value = await QRCode.toDataURL(verificationUrl.value, {
        width: 130,
        margin: 1,
    });
});

const printReceipt = () => {
    const receiptElement = document.getElementById('printable-clearance-receipt');

    if (!receiptElement) {
        window.print();
        return;
    }

    const printWindow = window.open('', '_blank', 'width=900,height=1200');

    if (!printWindow) {
        window.print();
        return;
    }

    const styles = Array.from(
        document.querySelectorAll('link[rel="stylesheet"], style'),
    )
        .map((node) => node.outerHTML)
        .join('');

    printWindow.document.open();
    printWindow.document.write(`
        <!doctype html>
        <html>
            <head>
                <title>Clearance Receipt</title>
                ${styles}
                <style>
                    @page {
                        size: auto;
                        margin: 0.2in;
                    }

                    html,
                    body {
                        margin: 0;
                        padding: 0;
                        background: white;
                    }

                    body {
                        font-family: Arial, sans-serif;
                    }

                    #printable-clearance-receipt {
                        width: 100%;
                        max-width: none;
                        margin: 0;
                        padding: 0;
                        box-shadow: none;
                        border-radius: 0;
                        font-size: 9px;
                        line-height: 1;
                    }

                    table,
                    thead,
                    tbody,
                    tr,
                    th,
                    td {
                        page-break-inside: avoid;
                    }

                    img {
                        print-color-adjust: exact;
                        -webkit-print-color-adjust: exact;
                    }
                </style>
            </head>
            <body>
                ${receiptElement.outerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();

    let hasPrinted = false;

    const triggerPrint = () => {
        if (hasPrinted) {
            return;
        }

        hasPrinted = true;

        setTimeout(() => {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }, 300);
    };

    printWindow.onload = triggerPrint;
    setTimeout(triggerPrint, 700);
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head title="Printable Clearance Receipt" />

    <div class="min-h-screen bg-gray-100 px-4 py-4 text-gray-900 print:bg-white print:px-0 print:py-0">
        <div class="receipt-container mx-auto max-w-3xl">
            <div class="mb-4 flex items-center justify-between print:hidden">
                <button
                    type="button"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                    @click="goBack"
                >
                    Back
                </button>

                <button
                    type="button"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
                    @click="printReceipt"
                >
                    Print Receipt
                </button>
            </div>

            <div
                id="printable-clearance-receipt"
                class="receipt-page rounded-xl bg-white p-5 shadow print:rounded-none print:p-0 print:shadow-none"
            >
                <div class="border-b border-gray-300 pb-2 text-center">
                    <h1 class="text-base font-bold uppercase tracking-wide">
                        Talibon Polytechnic College
                    </h1>
                    <p class="text-xs text-gray-600">
                        San Isidro, Talibon, Bohol
                    </p>

                    <h2 class="mt-2 text-lg font-bold uppercase">
                        Clearance Receipt
                    </h2>
                    <p class="text-[11px] text-gray-600">
                        Official proof of completed student clearance
                    </p>
                </div>

                <div class="mt-2 grid grid-cols-2 gap-x-6 gap-y-1.5 border-b border-gray-300 pb-2 text-[11px]">
                    <div>
                        <p class="font-semibold uppercase text-gray-500">
                            Receipt Number
                        </p>
                        <p class="font-bold">
                            {{ clearanceRequest.receipt_number }}
                        </p>
                    </div>

                    <div>
                        <p class="font-semibold uppercase text-gray-500">
                            Clearance Status
                        </p>
                        <p class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-green-700">
                            {{ clearanceRequest.status }}
                        </p>
                    </div>

                    <div>
                        <p class="font-semibold uppercase text-gray-500">
                            Semester
                        </p>
                        <p class="font-medium">
                            {{ clearanceRequest.semester }}
                        </p>
                    </div>

                    <div>
                        <p class="font-semibold uppercase text-gray-500">
                            School Year
                        </p>
                        <p class="font-medium">
                            {{ clearanceRequest.school_year }}
                        </p>
                    </div>

                    <div>
                        <p class="font-semibold uppercase text-gray-500">
                            Submitted At
                        </p>
                        <p class="font-medium">
                            {{ clearanceRequest.submitted_at ?? 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <p class="font-semibold uppercase text-gray-500">
                            Cleared At
                        </p>
                        <p class="font-medium">
                            {{ clearanceRequest.cleared_at ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <div class="mt-2 border-b border-gray-300 pb-2">
                    <h3 class="text-xs font-bold uppercase">
                        Student Information
                    </h3>

                    <div class="mt-1.5 grid grid-cols-2 gap-x-6 gap-y-1.5 text-[11px]">
                        <div>
                            <p class="font-semibold uppercase text-gray-500">
                                Student Name
                            </p>
                            <p class="font-medium">
                                {{ student.name }}
                            </p>
                        </div>

                        <div>
                            <p class="font-semibold uppercase text-gray-500">
                                Student ID
                            </p>
                            <p class="font-medium">
                                {{ student.student_id }}
                            </p>
                        </div>

                        <div>
                            <p class="font-semibold uppercase text-gray-500">
                                Course Code
                            </p>
                            <p class="font-medium">
                                {{ student.course ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <p class="font-semibold uppercase text-gray-500">
                                Course Name
                            </p>
                            <p class="font-medium">
                                {{ student.course_name ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-2 border-b border-gray-300 pb-2">
                    <h3 class="text-xs font-bold uppercase">
                        Office Clearance Approval Summary
                    </h3>

                    <div class="mt-1.5 overflow-hidden rounded-md border border-gray-300">
                        <table class="w-full border-collapse text-[9.5px] leading-tight">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border-b border-gray-300 px-1.5 py-0.5 text-left font-semibold">
                                        Office
                                    </th>
                                    <th class="border-b border-gray-300 px-1.5 py-0.5 text-left font-semibold">
                                        Status
                                    </th>
                                    <th class="border-b border-gray-300 px-1.5 py-0.5 text-left font-semibold">
                                        Approved By
                                    </th>
                                    <th class="border-b border-gray-300 px-1.5 py-0.5 text-left font-semibold">
                                        Date Approved
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="approval in regularApprovals"
                                    :key="approval.office ?? approval.acted_at ?? approval.status"
                                    class="border-b border-gray-200 last:border-b-0"
                                >
                                    <td class="px-1.5 py-0.5">
                                        {{ approval.office ?? 'N/A' }}
                                    </td>
                                    <td class="px-1.5 py-0.5 font-semibold uppercase text-green-700">
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

                <div v-if="presidentApproval" class="mt-2 border-b border-gray-300 pb-2">
                    <h3 class="text-xs font-bold uppercase">
                        Final Approval
                    </h3>

                    <div class="mt-1.5 grid grid-cols-4 gap-x-4 text-[11px]">
                        <div>
                            <p class="font-semibold uppercase text-gray-500">
                                Office
                            </p>
                            <p class="font-medium">
                                {{ presidentApproval.office ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <p class="font-semibold uppercase text-gray-500">
                                Status
                            </p>
                            <p class="font-semibold uppercase text-green-700">
                                {{ presidentApproval.status }}
                            </p>
                        </div>

                        <div>
                            <p class="font-semibold uppercase text-gray-500">
                                Approved By
                            </p>
                            <p class="font-medium">
                                {{ presidentApproval.approver ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <p class="font-semibold uppercase text-gray-500">
                                Date Approved
                            </p>
                            <p class="font-medium">
                                {{ presidentApproval.acted_at ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-2">
                    <h3 class="text-xs font-bold uppercase">
                        Verification Information
                    </h3>

                    <div class="mt-1.5 grid grid-cols-[130px_1fr] gap-3 rounded-md border border-dashed border-gray-400 p-2">
                        <div class="flex flex-col items-center justify-center">
                            <img
                                v-if="qrCodeDataUrl"
                                :src="qrCodeDataUrl"
                                alt="Clearance verification QR code"
                                class="h-32 w-32"
                            />

                            <div
                                v-else
                                class="flex h-32 w-32 items-center justify-center rounded-md border border-gray-300 text-xs text-gray-500"
                            >
                                Generating QR...
                            </div>

                            <p class="mt-0.5 text-center text-[9px] font-semibold text-gray-600">
                                Scan to verify
                            </p>
                        </div>

                        <div class="text-[10px]">
                            <p class="font-semibold uppercase text-gray-500">
                                Verification Code
                            </p>
                            <p class="break-all font-mono text-[9.5px] font-semibold">
                                {{ clearanceRequest.verification_code }}
                            </p>

                            <p class="mt-1.5 font-semibold uppercase text-gray-500">
                                Verification Link
                            </p>
                            <p class="break-all font-mono text-[9.5px] font-semibold text-blue-700">
                                {{ verificationUrl }}
                            </p>

                            <p class="mt-1.5 text-[9.5px] text-gray-600">
                                This receipt is valid only if the QR verification page confirms that the clearance request is cleared.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-8 text-center">
                    <div>
                        <div class="mx-auto h-6 w-52 border-b border-gray-900"></div>
                        <p class="mt-1 text-[10px] font-semibold">
                            Student Signature
                        </p>
                    </div>

                    <div>
                        <div class="mx-auto h-6 w-52 border-b border-gray-900"></div>
                        <p class="mt-1 text-[10px] font-semibold">
                            Authorized Representative
                        </p>
                    </div>
                </div>

                <p class="mt-2 text-center text-[9px] text-gray-500">
                    Generated by the Web-based Student Clearance System with Role-Based Approval.
                </p>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    @page {
        size: auto;
        margin: 0.2in;
    }

    html,
    body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }

    .receipt-container {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .receipt-page {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        font-size: 9px;
        line-height: 1;
        page-break-inside: avoid;
    }

    table,
    thead,
    tbody,
    tr,
    th,
    td {
        page-break-inside: avoid !important;
    }
}
</style>