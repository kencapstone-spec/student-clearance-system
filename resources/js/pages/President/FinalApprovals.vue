<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    ClipboardCheck,
    FileCheck2,
    ShieldCheck,
    Sparkles,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

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

const props = defineProps<{
    clearanceRequests?: ClearanceRequest[];
    requests?: ClearanceRequest[];
    readyCount?: number;
}>();

const approvalRequests = computed(() => {
    return props.clearanceRequests ?? props.requests ?? [];
});

const readyApprovalCount = computed(() => {
    return props.readyCount ?? approvalRequests.value.length;
});

const selectedRequest = ref<ClearanceRequest | null>(null);
const showFinalApproveModal = ref(false);
const showAutoApproveModal = ref(false);
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

const openAutoApproveModal = () => {
    if (readyApprovalCount.value === 0) {
        return;
    }

    successMessage.value = '';
    errorMessage.value = '';
    showAutoApproveModal.value = true;
};

const closeAutoApproveModal = () => {
    showAutoApproveModal.value = false;
};

const confirmFinalApproval = () => {
    if (!selectedRequest.value) {
        return;
    }

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

const confirmAutoApproveAll = () => {
    successMessage.value = '';
    errorMessage.value = '';

    router.patch(
        '/president/final-approvals/approve-all',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                closeAutoApproveModal();
                successMessage.value =
                    'All ready clearance requests have been automatically approved.';
            },
            onError: () => {
                errorMessage.value =
                    'Unable to auto approve ready requests. Please try again.';
            },
        },
    );
};
</script>

<template>
    <Head title="President Final Approvals" />

    <div
        class="min-h-screen bg-linear-to-br from-slate-50 via-white to-blue-50/40 p-4 text-slate-900 md:p-6"
    >
        <div class="mx-auto flex max-w-7xl flex-col gap-6">
            <!-- Hero -->
            <section
                class="overflow-hidden rounded-4xl border border-slate-200 bg-white/95 shadow-xl shadow-slate-200/70"
            >
                <div class="grid gap-8 p-6 lg:grid-cols-[1fr_300px] lg:p-8">
                    <div>
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-blue-700"
                        >
                            <ShieldCheck class="size-4" />
                            President / Final Approver Panel
                        </div>

                        <div
                            class="mt-5 flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                        >
                            <div>
                                <h1
                                    class="text-4xl font-black tracking-tight text-blue-950"
                                >
                                    Final Clearance Approvals
                                </h1>

                                <p
                                    class="mt-3 max-w-3xl text-base leading-7 text-slate-600"
                                >
                                    Review students whose clearance requests
                                    have passed all regular office approvals and
                                    are ready for final clearance approval.
                                </p>
                            </div>

                            <button
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black text-white shadow-lg transition disabled:translate-y-0 disabled:shadow-none"
                                :class="
                                    readyApprovalCount === 0
                                        ? 'cursor-not-allowed bg-slate-400 shadow-slate-400/20'
                                        : 'bg-green-700 shadow-green-700/20 hover:-translate-y-0.5 hover:bg-green-800 hover:shadow-xl'
                                "
                                :disabled="readyApprovalCount === 0"
                                @click="openAutoApproveModal"
                            >
                                <Sparkles class="size-4" />
                                Auto Approve All Ready Requests
                            </button>
                        </div>

                        <div
                            class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/80 p-4 text-sm font-medium leading-6 text-blue-900"
                        >
                            Only requests approved by all regular offices are
                            shown here. You may approve one student manually, or
                            use Auto Approve All to approve every currently
                            ready request.
                        </div>

                        <div
                            v-if="successMessage"
                            class="mt-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-black text-green-700"
                        >
                            {{ successMessage }}
                        </div>

                        <div
                            v-if="errorMessage"
                            class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-black text-red-700"
                        >
                            {{ errorMessage }}
                        </div>
                    </div>

                    <div class="hidden items-center justify-center lg:flex">
                        <div
                            class="relative grid h-52 w-52 place-items-center rounded-4xl border border-blue-100 bg-linear-to-br from-white to-blue-50 shadow-2xl shadow-slate-300/70"
                        >
                            <div
                                class="grid h-20 w-20 place-items-center rounded-3xl bg-blue-700 text-white shadow-xl shadow-blue-700/25"
                            >
                                <ShieldCheck class="size-10" />
                            </div>

                            <p
                                class="text-center text-sm font-black uppercase tracking-[0.18em] text-blue-700"
                            >
                                Final Authority
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Summary Cards -->
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div
                    class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="grid h-14 w-14 place-items-center rounded-2xl bg-blue-50 text-blue-700 shadow-sm"
                        >
                            <ClipboardCheck class="size-7" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-black uppercase tracking-wide text-blue-700"
                            >
                                Ready for Final Approval
                            </p>

                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ readyApprovalCount }}
                            </p>

                            <p class="text-sm font-medium text-slate-500">
                                Requests awaiting final action
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="grid h-14 w-14 place-items-center rounded-2xl bg-green-50 text-green-700 shadow-sm"
                        >
                            <CheckCircle2 class="size-7" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-black uppercase tracking-wide text-green-700"
                            >
                                Available Actions
                            </p>

                            <p class="mt-1 text-lg font-black text-blue-950">
                                Manual or Bulk Approval
                            </p>

                            <p class="text-sm font-medium text-slate-500">
                                Approve one request or all ready requests.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:col-span-2 xl:col-span-1"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="grid h-14 w-14 place-items-center rounded-2xl bg-indigo-50 text-indigo-700 shadow-sm"
                        >
                            <FileCheck2 class="size-7" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-black uppercase tracking-wide text-indigo-700"
                            >
                                Finalization Result
                            </p>

                            <p class="mt-1 text-lg font-black text-blue-950">
                                Receipt + QR Generation
                            </p>

                            <p class="text-sm font-medium text-slate-500">
                                Approval clears the request and enables receipt
                                verification.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Requests -->
            <section
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-sm shadow-slate-200/70"
            >
                <div class="border-b border-slate-200 bg-white px-6 py-5">
                    <p
                        class="text-xs font-black uppercase tracking-[0.18em] text-slate-400"
                    >
                        Final Approval Queue
                    </p>

                    <h2 class="mt-1 text-xl font-black text-blue-950">
                        Clearance Requests
                    </h2>

                    <p class="mt-1 text-sm font-medium text-slate-500">
                        These requests already passed all regular office
                        approvals.
                    </p>
                </div>

                <div
                    v-if="approvalRequests.length === 0"
                    class="p-12 text-center"
                >
                    <div
                        class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-blue-50 text-blue-700"
                    >
                        <Users class="size-8" />
                    </div>

                    <p class="mt-4 font-black text-slate-700">
                        No requests ready for final approval.
                    </p>

                    <p class="mt-1 text-sm font-medium text-slate-500">
                        Requests will appear here after all regular offices have
                        approved them.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                >
                                    Student
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                >
                                    Student ID
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                >
                                    Course
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                >
                                    Semester
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                >
                                    School Year
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-black uppercase tracking-wide"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="request in approvalRequests"
                                :key="request.id"
                                class="transition hover:bg-blue-50/50"
                            >
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-black text-blue-950">
                                            {{ request.user.name }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs font-medium text-slate-500"
                                        >
                                            Clearance request #{{ request.id }}
                                        </p>
                                    </div>
                                </td>

                                <td
                                    class="px-6 py-4 font-semibold text-slate-700"
                                >
                                    {{ request.user.student_id }}
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700"
                                    >
                                        {{
                                            request.user.course
                                                ? request.user.course.code
                                                : 'N/A'
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="px-6 py-4 font-semibold text-slate-700"
                                >
                                    {{ request.semester }}
                                </td>

                                <td
                                    class="px-6 py-4 font-semibold text-slate-700"
                                >
                                    {{ request.school_year }}
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-black text-blue-700"
                                    >
                                        Ready for Final Approval
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-green-700 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:-translate-y-0.5 hover:bg-green-800 hover:shadow-lg"
                                        @click="openFinalApproveModal(request)"
                                    >
                                        <CheckCircle2 class="size-4" />
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

    <!-- Single Final Approval Modal -->
    <div
        v-if="showFinalApproveModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
        @click.self="closeFinalApproveModal"
    >
        <div
            class="w-full max-w-md rounded-4xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-900/20"
        >
            <div class="flex items-start gap-4">
                <div
                    class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-green-50 text-green-700"
                >
                    <CheckCircle2 class="size-6" />
                </div>

                <div>
                    <h2 class="text-xl font-black text-blue-950">
                        Confirm Final Approval
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Are you sure you want to give final clearance approval
                        to this student?
                    </p>
                </div>
            </div>

            <div
                v-if="selectedRequest"
                class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700"
            >
                <div class="grid gap-3">
                    <p>
                        <span class="font-black">Student:</span>
                        {{ selectedRequest.user.name }}
                    </p>

                    <p>
                        <span class="font-black">Student ID:</span>
                        {{ selectedRequest.user.student_id }}
                    </p>

                    <p>
                        <span class="font-black">Course:</span>
                        {{
                            selectedRequest.user.course
                                ? selectedRequest.user.course.code
                                : 'N/A'
                        }}
                    </p>

                    <p>
                        <span class="font-black">Semester:</span>
                        {{ selectedRequest.semester }}
                    </p>

                    <p>
                        <span class="font-black">School Year:</span>
                        {{ selectedRequest.school_year }}
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    @click="closeFinalApproveModal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="rounded-2xl bg-green-700 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:bg-green-800"
                    @click="confirmFinalApproval"
                >
                    Confirm Final Approval
                </button>
            </div>
        </div>
    </div>

    <!-- Auto Approve All Modal -->
    <div
        v-if="showAutoApproveModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
        @click.self="closeAutoApproveModal"
    >
        <div
            class="w-full max-w-md rounded-4xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-900/20"
        >
            <div class="flex items-start gap-4">
                <div
                    class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-green-50 text-green-700"
                >
                    <Sparkles class="size-6" />
                </div>

                <div>
                    <h2 class="text-xl font-black text-blue-950">
                        Auto Approve All Ready Requests?
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        This will give final approval to all clearance requests
                        currently ready for President approval.
                    </p>
                </div>
            </div>

            <div
                class="mt-5 rounded-2xl border border-green-200 bg-green-50 p-4"
            >
                <p class="text-sm font-black text-green-800">
                    Requests to approve:
                </p>

                <p class="mt-1 text-4xl font-black text-green-900">
                    {{ readyApprovalCount }}
                </p>

                <p class="mt-2 text-sm leading-6 text-green-800">
                    Each request will be marked as cleared, assigned a receipt
                    number, assigned a verification code, and the student will
                    receive a notification.
                </p>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    @click="closeAutoApproveModal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="rounded-2xl bg-green-700 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:bg-green-800"
                    @click="confirmAutoApproveAll"
                >
                    Confirm Auto Approve All
                </button>
            </div>
        </div>
    </div>
</template>