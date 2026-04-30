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
        width: 220,
        margin: 2,
    });
});

const printReceipt = () => {
    window.print();
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head title="Printable Clearance Receipt" />

    <div class="min-h-screen bg-gray-100 px-4 py-6 text-gray-900 print:bg-white print:px-0 print:py-0">
        <div class="mx-auto max-w-4xl">
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

            <div class="rounded-xl bg-white p-8 shadow print:rounded-none print:p-0 print:shadow-none">
                <div class="border-b border-gray-300 pb-6 text-center">
                    <h1 class="text-lg font-bold uppercase tracking-wide">
                        Talibon Polytechnic College
                    </h1>
                    <p class="text-sm text-gray-600">
                        San Isidro, Talibon, Bohol
                    </p>

                    <h2 class="mt-6 text-2xl font-bold uppercase">
                        Clearance Receipt
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Official proof of completed student clearance
                    </p>
                </div>

                <div class="mt-6 grid gap-4 border-b border-gray-300 pb-6 md:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">
                            Receipt Number
                        </p>
                        <p class="mt-1 text-lg font-bold">
                            {{ clearanceRequest.receipt_number }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">
                            Clearance Status
                        </p>
                        <p class="mt-1 inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-semibold uppercase text-green-700">
                            {{ clearanceRequest.status }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">
                            Semester
                        </p>
                        <p class="mt-1 font-medium">
                            {{ clearanceRequest.semester }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">
                            School Year
                        </p>
                        <p class="mt-1 font-medium">
                            {{ clearanceRequest.school_year }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">
                            Submitted At
                        </p>
                        <p class="mt-1 font-medium">
                            {{ clearanceRequest.submitted_at ?? 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">
                            Cleared At
                        </p>
                        <p class="mt-1 font-medium">
                            {{ clearanceRequest.cleared_at ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 border-b border-gray-300 pb-6">
                    <h3 class="text-base font-bold uppercase">
                        Student Information
                    </h3>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500">
                                Student Name
                            </p>
                            <p class="mt-1 font-medium">
                                {{ student.name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500">
                                Student ID
                            </p>
                            <p class="mt-1 font-medium">
                                {{ student.student_id }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500">
                                Course Code
                            </p>
                            <p class="mt-1 font-medium">
                                {{ student.course ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500">
                                Course Name
                            </p>
                            <p class="mt-1 font-medium">
                                {{ student.course_name ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 border-b border-gray-300 pb-6">
                    <h3 class="text-base font-bold uppercase">
                        Office Clearance Approval Summary
                    </h3>

                    <div class="mt-4 overflow-hidden rounded-lg border border-gray-300">
                        <table class="w-full border-collapse text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border-b border-gray-300 px-3 py-2 text-left font-semibold">
                                        Office
                                    </th>
                                    <th class="border-b border-gray-300 px-3 py-2 text-left font-semibold">
                                        Status
                                    </th>
                                    <th class="border-b border-gray-300 px-3 py-2 text-left font-semibold">
                                        Approved By
                                    </th>
                                    <th class="border-b border-gray-300 px-3 py-2 text-left font-semibold">
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
                                    <td class="px-3 py-2">
                                        {{ approval.office ?? 'N/A' }}
                                    </td>
                                    <td class="px-3 py-2 font-semibold uppercase text-green-700">
                                        {{ approval.status }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ approval.approver ?? 'N/A' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ approval.acted_at ?? 'N/A' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="presidentApproval" class="mt-6 border-b border-gray-300 pb-6">
                    <h3 class="text-base font-bold uppercase">
                        Final Approval
                    </h3>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500">
                                Office
                            </p>
                            <p class="mt-1 font-medium">
                                {{ presidentApproval.office ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500">
                                Status
                            </p>
                            <p class="mt-1 font-semibold uppercase text-green-700">
                                {{ presidentApproval.status }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500">
                                Approved By
                            </p>
                            <p class="mt-1 font-medium">
                                {{ presidentApproval.approver ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500">
                                Date Approved
                            </p>
                            <p class="mt-1 font-medium">
                                {{ presidentApproval.acted_at ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-base font-bold uppercase">
                        Verification Information
                    </h3>

                    <div class="mt-4 grid gap-6 rounded-lg border border-dashed border-gray-400 p-4 md:grid-cols-[220px_1fr]">
                        <div class="flex flex-col items-center justify-center">
                            <img
                                v-if="qrCodeDataUrl"
                                :src="qrCodeDataUrl"
                                alt="Clearance verification QR code"
                                class="h-52 w-52"
                            />

                            <div
                                v-else
                                class="flex h-52 w-52 items-center justify-center rounded-lg border border-gray-300 text-sm text-gray-500"
                            >
                                Generating QR...
                            </div>

                            <p class="mt-2 text-center text-xs font-semibold text-gray-600">
                                Scan to verify
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500">
                                Verification Code
                            </p>
                            <p class="mt-1 break-all font-mono text-sm font-semibold">
                                {{ clearanceRequest.verification_code }}
                            </p>

                            <p class="mt-4 text-xs font-semibold uppercase text-gray-500">
                                Verification Link
                            </p>
                            <p class="mt-1 break-all font-mono text-sm font-semibold text-blue-700">
                                {{ verificationUrl }}
                            </p>

                            <p class="mt-4 text-xs text-gray-600">
                                This receipt is valid only if the QR verification page confirms that the clearance request is cleared.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-10 grid gap-8 text-center md:grid-cols-2">
                    <div>
                        <div class="mx-auto h-12 w-64 border-b border-gray-900"></div>
                        <p class="mt-2 text-sm font-semibold">
                            Student Signature
                        </p>
                    </div>

                    <div>
                        <div class="mx-auto h-12 w-64 border-b border-gray-900"></div>
                        <p class="mt-2 text-sm font-semibold">
                            Authorized Representative
                        </p>
                    </div>
                </div>

                <p class="mt-8 text-center text-xs text-gray-500">
                    Generated by the Web-based Student Clearance System with Role-Based Approval.
                </p>
            </div>
        </div>
    </div>
</template>