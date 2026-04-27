<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

type Course = {
    id: number;
    code: string;
    name: string;
};

type Student = {
    id: number;
    name: string;
    student_id: string;
    course: Course | null;
};

type Office = {
    id: number;
    name: string;
    group: string;
    is_final_approver: boolean;
};

type Approval = {
    id: number;
    status: 'pending' | 'approved' | 'rejected';
    remarks: string | null;
    office: Office;
};

type ClearanceRequest = {
    id: number;
    semester: string;
    school_year: string;
    user: Student;
    approvals: Approval[];
};

defineProps<{
    requests: ClearanceRequest[];
}>();

const selectedRequest = ref<ClearanceRequest | null>(null);
const showFinalApproveModal = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const openFinalApproveModal = (request: ClearanceRequest) => {
    successMessage.value = '';
    errorMessage.value = '';
    selectedRequest.value = request;
    showFinalApproveModal.value = true;
};

const closeFinalApproveModal = () => {
    selectedRequest.value = null;
    showFinalApproveModal.value = false;
};

const confirmFinalApproval = () => {
    if (!selectedRequest.value) return;

    successMessage.value = '';
    errorMessage.value = '';

    router.patch(
        `/president/final-approvals/${selectedRequest.value.id}/approve`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                closeFinalApproveModal();
                successMessage.value =
                    'Final clearance approval completed successfully.';
            },
            onError: () => {
                errorMessage.value =
                    'Unable to complete final approval. Please try again.';
            },
        },
    );
};
</script>

<template>
    <Head title="President Final Approvals" />

    <div class="min-h-screen bg-slate-50 p-4 text-slate-900">
        <div class="mx-auto flex max-w-7xl flex-col gap-6">
            <section class="rounded-2xl border bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-blue-700">
                    President / Final Approver Panel
                </p>

                <h1 class="mt-2 text-3xl font-bold text-blue-950">
                    Final Clearance Approvals
                </h1>

                <p class="mt-2 text-slate-600">
                    Review students whose clearance requests have been approved
                    by all regular offices and are ready for final approval.
                </p>

                <div
                    class="mt-4 rounded-xl bg-blue-50 p-4 text-sm text-blue-900"
                >
                    Only requests approved by all regular offices are shown
                    here. Confirming final approval will complete the
                    President clearance step.
                </div>

                <div
                    v-if="successMessage"
                    class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700"
                >
                    {{ successMessage }}
                </div>

                <div
                    v-if="errorMessage"
                    class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"
                >
                    {{ errorMessage }}
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-blue-700">
                        Ready for Final Approval
                    </p>

                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ requests.length }}
                    </p>
                </div>
            </section>

            <section class="rounded-2xl border bg-white shadow-sm">
                <div class="border-b p-6">
                    <h2 class="text-xl font-bold text-blue-950">
                        Clearance Requests
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        These requests already passed all regular office
                        approvals.
                    </p>
                </div>

                <div v-if="requests.length === 0" class="p-8 text-center">
                    <p class="font-medium text-slate-700">
                        No requests ready for final approval.
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Requests will appear here after all regular offices have
                        approved them.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100 text-slate-600">
                            <tr>
                                <th class="px-6 py-3 font-semibold">
                                    Student
                                </th>

                                <th class="px-6 py-3 font-semibold">
                                    Student ID
                                </th>

                                <th class="px-6 py-3 font-semibold">
                                    Course
                                </th>

                                <th class="px-6 py-3 font-semibold">
                                    Semester
                                </th>

                                <th class="px-6 py-3 font-semibold">
                                    School Year
                                </th>

                                <th class="px-6 py-3 font-semibold">
                                    Status
                                </th>

                                <th class="px-6 py-3 text-right font-semibold">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="request in requests"
                                :key="request.id"
                                class="border-t"
                            >
                                <td class="px-6 py-4 font-medium text-blue-950">
                                    {{ request.user.name }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ request.user.student_id }}
                                </td>

                                <td class="px-6 py-4">
                                    {{
                                        request.user.course
                                            ? request.user.course.code
                                            : 'N/A'
                                    }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ request.semester }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ request.school_year }}
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700"
                                    >
                                        Ready for Final Approval
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <button
                                        type="button"
                                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                                        @click="openFinalApproveModal(request)"
                                    >
                                        Final Approve
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <div
        v-if="showFinalApproveModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @click.self="closeFinalApproveModal"
    >
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h2 class="text-xl font-bold text-blue-950">
                Confirm Final Approval
            </h2>

            <p class="mt-2 text-sm text-slate-600">
                Are you sure you want to give final clearance approval to this
                student?
            </p>

            <div
                v-if="selectedRequest"
                class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-700"
            >
                <p>
                    <span class="font-semibold">Student:</span>
                    {{ selectedRequest.user.name }}
                </p>

                <p>
                    <span class="font-semibold">Student ID:</span>
                    {{ selectedRequest.user.student_id }}
                </p>

                <p>
                    <span class="font-semibold">Course:</span>
                    {{
                        selectedRequest.user.course
                            ? selectedRequest.user.course.code
                            : 'N/A'
                    }}
                </p>

                <p>
                    <span class="font-semibold">Semester:</span>
                    {{ selectedRequest.semester }}
                </p>

                <p>
                    <span class="font-semibold">School Year:</span>
                    {{ selectedRequest.school_year }}
                </p>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                    @click="closeFinalApproveModal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                    @click="confirmFinalApproval"
                >
                    Confirm Final Approval
                </button>
            </div>
        </div>
    </div>
</template>