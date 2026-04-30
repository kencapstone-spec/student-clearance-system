<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

type ClearanceRequest = {
    id: number;
    semester: string;
    school_year: string;
    status: string;
    submitted_at: string | null;
    cleared_at: string | null;
    receipt_number: string | null;
    verification_code: string | null;
} | null;

type Student = {
    name: string | null;
    student_id: string | null;
    course: string | null;
    course_name: string | null;
} | null;

type PresidentApproval = {
    office: string | null;
    status: string;
    approver: string | null;
    acted_at: string | null;
} | null;

defineProps<{
    isValid: boolean;
    message: string;
    clearanceRequest: ClearanceRequest;
    student: Student;
    presidentApproval: PresidentApproval;
}>();
</script>

<template>
    <Head title="Clearance Verification" />

    <div class="min-h-screen bg-slate-100 px-4 py-10 text-slate-900">
        <div class="mx-auto max-w-3xl">
            <div class="rounded-2xl bg-white p-8 shadow">
                <div class="text-center">
                    <div
                        class="mx-auto flex h-20 w-20 items-center justify-center rounded-full text-4xl"
                        :class="
                            isValid
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700'
                        "
                    >
                        {{ isValid ? '✓' : '!' }}
                    </div>

                    <p class="mt-6 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Talibon Polytechnic College
                    </p>

                    <h1 class="mt-2 text-3xl font-bold text-blue-950">
                        Clearance Verification
                    </h1>

                    <div
                        class="mt-6 rounded-xl border p-4"
                        :class="
                            isValid
                                ? 'border-green-200 bg-green-50 text-green-800'
                                : 'border-red-200 bg-red-50 text-red-800'
                        "
                    >
                        <p class="text-lg font-bold">
                            {{ isValid ? 'Valid Clearance Receipt' : 'Invalid / Not Cleared' }}
                        </p>

                        <p class="mt-1 text-sm">
                            {{ message }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="clearanceRequest && student"
                    class="mt-8 space-y-6"
                >
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500">
                                Receipt Number
                            </p>
                            <p class="mt-1 font-bold text-blue-950">
                                {{ clearanceRequest.receipt_number ?? 'N/A' }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500">
                                Status
                            </p>
                            <p
                                class="mt-1 font-bold uppercase"
                                :class="isValid ? 'text-green-700' : 'text-red-700'"
                            >
                                {{ clearanceRequest.status }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500">
                                Student Name
                            </p>
                            <p class="mt-1 font-bold text-blue-950">
                                {{ student.name ?? 'N/A' }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500">
                                Student ID
                            </p>
                            <p class="mt-1 font-bold text-blue-950">
                                {{ student.student_id ?? 'N/A' }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500">
                                Course
                            </p>
                            <p class="mt-1 font-bold text-blue-950">
                                {{ student.course ?? 'N/A' }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ student.course_name ?? 'N/A' }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500">
                                Semester / School Year
                            </p>
                            <p class="mt-1 font-bold text-blue-950">
                                {{ clearanceRequest.semester }} / {{ clearanceRequest.school_year }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500">
                                Submitted At
                            </p>
                            <p class="mt-1 font-bold text-blue-950">
                                {{ clearanceRequest.submitted_at ?? 'N/A' }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500">
                                Cleared At
                            </p>
                            <p class="mt-1 font-bold text-blue-950">
                                {{ clearanceRequest.cleared_at ?? 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="presidentApproval"
                        class="rounded-xl border border-slate-200 p-4"
                    >
                        <h2 class="text-lg font-bold text-blue-950">
                            Final Approval
                        </h2>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Office
                                </p>
                                <p class="mt-1 font-medium">
                                    {{ presidentApproval.office ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Approved By
                                </p>
                                <p class="mt-1 font-medium">
                                    {{ presidentApproval.approver ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Approval Status
                                </p>
                                <p class="mt-1 font-semibold uppercase text-green-700">
                                    {{ presidentApproval.status }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Date Approved
                                </p>
                                <p class="mt-1 font-medium">
                                    {{ presidentApproval.acted_at ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-dashed border-slate-300 p-4">
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Verification Code
                        </p>
                        <p class="mt-1 break-all font-mono text-sm font-semibold text-slate-800">
                            {{ clearanceRequest.verification_code ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <div
                    v-else
                    class="mt-8 rounded-xl border border-slate-200 bg-slate-50 p-6 text-center"
                >
                    <p class="font-semibold text-slate-700">
                        No clearance information available.
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        Please check the verification code and try again.
                    </p>
                </div>

                <p class="mt-8 text-center text-xs text-slate-500">
                    This page verifies clearance records generated by the Web-based Student Clearance System.
                </p>
            </div>
        </div>
    </div>
</template>