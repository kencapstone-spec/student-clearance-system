<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    ClipboardCheck,
    FileCheck2,
    ShieldCheck,
    Sparkles,
    Users,
    X,
} from 'lucide-vue-next';
import { computed, ref, onMounted, onUnmounted } from 'vue';

let pollingInterval: ReturnType<typeof setInterval>;

onMounted(() => {
    pollingInterval = setInterval(() => {
        router.reload({
            data: { _t: Date.now() },
            only: ['clearanceRequests'],
        });
    }, 5000);
});

onUnmounted(() => {
    clearInterval(pollingInterval);
});

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

const clearMessages = () => {
    successMessage.value = '';
    errorMessage.value = '';
};

const openFinalApproveModal = (request: ClearanceRequest) => {
    clearMessages();
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

    clearMessages();
    showAutoApproveModal.value = true;
};

const closeAutoApproveModal = () => {
    showAutoApproveModal.value = false;
};

const confirmFinalApproval = () => {
    if (!selectedRequest.value) {
        return;
    }

    clearMessages();

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
    clearMessages();

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

const scrollToTop = () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    });
};

const scrollToQueue = () => {
    document.getElementById('final-approval-queue')?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    });
};
</script>

<template>
    <Head title="President Final Approvals" />

    <div
        class="min-h-screen bg-linear-to-br from-slate-50 via-white to-blue-50/40 p-3 pb-28 text-slate-900 sm:p-4 sm:pb-28 md:p-6 md:pb-6"
    >
        <div class="mx-auto flex max-w-7xl flex-col gap-4 md:gap-6">
            <!-- Hero -->
            <section
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-xl shadow-slate-200/70 md:rounded-4xl"
            >
                <div
                    class="grid gap-6 p-4 sm:p-6 lg:grid-cols-[1fr_300px] lg:p-8"
                >
                    <div>
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-2 text-[0.65rem] font-black tracking-[0.14em] text-blue-700 uppercase sm:px-4 sm:text-xs sm:tracking-[0.18em]"
                        >
                            <ShieldCheck class="size-4" />
                            President / Final Approver Panel
                        </div>

                        <div
                            class="mt-5 flex flex-col gap-5 md:flex-row md:items-start md:justify-between"
                        >
                            <div class="min-w-0">
                                <h1
                                    class="text-3xl font-black tracking-tight text-blue-950 sm:text-4xl"
                                >
                                    Final Clearance Approvals
                                </h1>

                                <p
                                    class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base sm:leading-7"
                                >
                                    Review students whose clearance requests
                                    have passed all regular office approvals and
                                    are ready for final clearance approval.
                                </p>
                            </div>

                            <button
                                type="button"
                                class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black text-white shadow-lg transition disabled:translate-y-0 disabled:cursor-not-allowed disabled:shadow-none md:w-auto"
                                :class="
                                    readyApprovalCount === 0
                                        ? 'bg-slate-400 shadow-slate-400/20'
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
                            class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/80 p-4 text-sm leading-6 font-medium text-blue-900"
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
                                class="text-center text-sm font-black tracking-[0.18em] text-blue-700 uppercase"
                            >
                                Final Authority
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Summary Cards -->
            <section
                class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3"
            >
                <div
                    class="rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:rounded-3xl md:p-6"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-blue-50 text-blue-700 shadow-sm md:h-14 md:w-14"
                        >
                            <ClipboardCheck class="size-6 md:size-7" />
                        </div>

                        <div class="min-w-0">
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-blue-700 uppercase sm:text-sm"
                            >
                                Ready for Final Approval
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ readyApprovalCount }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Requests awaiting final action
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:rounded-3xl md:p-6"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-green-50 text-green-700 shadow-sm md:h-14 md:w-14"
                        >
                            <CheckCircle2 class="size-6 md:size-7" />
                        </div>

                        <div class="min-w-0">
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-green-700 uppercase sm:text-sm"
                            >
                                Available Actions
                            </p>

                            <p
                                class="mt-1 text-lg font-black text-blue-950 md:text-xl"
                            >
                                Manual or Bulk Approval
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Approve one request or all ready requests.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl sm:col-span-2 md:rounded-3xl md:p-6 xl:col-span-1"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-indigo-50 text-indigo-700 shadow-sm md:h-14 md:w-14"
                        >
                            <FileCheck2 class="size-6 md:size-7" />
                        </div>

                        <div class="min-w-0">
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-indigo-700 uppercase sm:text-sm"
                            >
                                Finalization Result
                            </p>

                            <p
                                class="mt-1 text-lg font-black text-blue-950 md:text-xl"
                            >
                                Receipt + QR Generation
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Approval clears the request and enables receipt
                                verification.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Requests -->
            <section
                id="final-approval-queue"
                class="scroll-mt-4 overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-sm shadow-slate-200/70"
            >
                <div
                    class="flex flex-col gap-4 border-b border-slate-200 bg-white px-4 py-5 sm:px-6 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-black tracking-[0.18em] text-slate-400 uppercase"
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

                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-black text-white shadow-md transition disabled:cursor-not-allowed disabled:opacity-60"
                        :class="
                            readyApprovalCount === 0
                                ? 'bg-slate-400 shadow-slate-400/20'
                                : 'bg-green-700 shadow-green-700/20 hover:-translate-y-0.5 hover:bg-green-800 hover:shadow-lg'
                        "
                        :disabled="readyApprovalCount === 0"
                        @click="openAutoApproveModal"
                    >
                        <Sparkles class="size-4" />
                        Auto Approve All
                    </button>
                </div>

                <div
                    v-if="approvalRequests.length === 0"
                    class="p-8 text-center sm:p-12"
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

                <div v-else>
                    <!-- Mobile Card List -->
                    <div class="grid gap-3 p-4 lg:hidden">
                        <article
                            v-for="request in approvalRequests"
                            :key="request.id"
                            class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3
                                        class="line-clamp-2 text-base leading-tight font-black break-words text-blue-950"
                                    >
                                        {{ request.user.name }}
                                    </h3>

                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-600"
                                    >
                                        {{ request.user.student_id }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs font-medium text-slate-500"
                                    >
                                        Clearance request #{{ request.id }}
                                    </p>
                                </div>

                                <span
                                    class="shrink-0 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-black text-blue-700"
                                >
                                    Ready
                                </span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span
                                    class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700"
                                >
                                    {{
                                        request.user.course
                                            ? request.user.course.code
                                            : 'N/A'
                                    }}
                                </span>

                                <span
                                    class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-600"
                                >
                                    {{ request.semester }}
                                </span>

                                <span
                                    class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-600"
                                >
                                    {{ request.school_year }}
                                </span>
                            </div>

                            <div
                                class="mt-3 rounded-xl border border-green-100 bg-green-50 px-3 py-2 text-sm leading-6 font-medium text-green-800"
                            >
                                <span class="font-black">
                                    Office approval status:
                                </span>
                                All regular office approvals are complete.
                            </div>

                            <button
                                type="button"
                                class="mt-4 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-2xl bg-green-700 px-4 py-3 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:bg-green-800"
                                @click="openFinalApproveModal(request)"
                            >
                                <CheckCircle2 class="size-4" />
                                Final Approve
                            </button>
                        </article>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden overflow-x-auto lg:block">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        Student
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        Student ID
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        Course
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        Semester
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        School Year
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        Status
                                    </th>

                                    <th
                                        class="px-6 py-4 text-right text-xs font-black tracking-wide uppercase"
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
                                                Clearance request #{{
                                                    request.id
                                                }}
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
                                            class="inline-flex min-h-11 items-center gap-2 rounded-2xl bg-green-700 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:-translate-y-0.5 hover:bg-green-800 hover:shadow-lg"
                                            @click="
                                                openFinalApproveModal(request)
                                            "
                                        >
                                            <CheckCircle2 class="size-4" />
                                            Final Approve
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Single Final Approval Modal -->
    <div
        v-if="showFinalApproveModal"
        class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/50 p-3 backdrop-blur-sm sm:items-center sm:p-4"
        @click.self="closeFinalApproveModal"
    >
        <div
            class="flex max-h-[92dvh] w-full max-w-md flex-col overflow-hidden rounded-t-4xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20 sm:max-h-[90vh] sm:rounded-4xl"
        >
            <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-start gap-3 sm:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-green-50 text-green-700 sm:h-12 sm:w-12"
                        >
                            <CheckCircle2 class="size-6" />
                        </div>

                        <div class="min-w-0">
                            <h2
                                class="text-lg font-black text-blue-950 sm:text-xl"
                            >
                                Confirm Final Approval
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Are you sure you want to give final clearance
                                approval to this student?
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="grid size-10 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                        @click="closeFinalApproveModal"
                    >
                        <X class="size-5" />
                    </button>
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

                <div
                    class="mt-4 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm leading-6 font-medium text-green-800"
                >
                    This action will mark the request as approved and enable the
                    student's clearance receipt and public QR verification.
                </div>
            </div>

            <div class="shrink-0 border-t border-slate-200 bg-white p-4 sm:p-6">
                <div class="grid grid-cols-2 gap-3 sm:flex sm:justify-end">
                    <button
                        type="button"
                        class="min-h-11 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="closeFinalApproveModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="min-h-11 rounded-2xl bg-green-700 px-4 py-3 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:bg-green-800"
                        @click="confirmFinalApproval"
                    >
                        Confirm Approval
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto Approve All Modal -->
    <div
        v-if="showAutoApproveModal"
        class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/50 p-3 backdrop-blur-sm sm:items-center sm:p-4"
        @click.self="closeAutoApproveModal"
    >
        <div
            class="flex max-h-[92dvh] w-full max-w-md flex-col overflow-hidden rounded-t-4xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20 sm:max-h-[90vh] sm:rounded-4xl"
        >
            <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-start gap-3 sm:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-green-50 text-green-700 sm:h-12 sm:w-12"
                        >
                            <Sparkles class="size-6" />
                        </div>

                        <div class="min-w-0">
                            <h2
                                class="text-lg font-black text-blue-950 sm:text-xl"
                            >
                                Auto Approve All Ready Requests?
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                This will give final approval to all clearance
                                requests currently ready for President approval.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="grid size-10 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                        @click="closeAutoApproveModal"
                    >
                        <X class="size-5" />
                    </button>
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
                        Each request will be marked as approved, assigned a
                        receipt number, assigned a verification code, and the
                        student will receive a notification.
                    </p>
                </div>

                <div
                    class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 font-medium text-amber-900"
                >
                    Manual final approval is still available if you want to
                    review each student one by one before approving.
                </div>
            </div>

            <div class="shrink-0 border-t border-slate-200 bg-white p-4 sm:p-6">
                <div class="grid grid-cols-2 gap-3 sm:flex sm:justify-end">
                    <button
                        type="button"
                        class="min-h-11 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="closeAutoApproveModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="min-h-11 rounded-2xl bg-green-700 px-4 py-3 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:bg-green-800"
                        @click="confirmAutoApproveAll"
                    >
                        Confirm Auto Approve All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- President Mobile Thumb Navigation -->
    <nav
        class="fixed inset-x-3 bottom-3 z-30 rounded-2xl border border-blue-200 bg-blue-950/95 p-2 shadow-2xl shadow-blue-950/25 backdrop-blur md:hidden"
    >
        <div class="grid grid-cols-3 gap-1">
            <button
                type="button"
                class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-[0.65rem] font-black text-white transition hover:bg-white/10"
                @click="scrollToTop"
            >
                <ShieldCheck class="size-4" />
                <span>Top</span>
            </button>

            <button
                type="button"
                class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-[0.65rem] font-black text-white transition hover:bg-white/10"
                @click="scrollToQueue"
            >
                <ClipboardCheck class="size-4" />
                <span>Queue</span>
            </button>

            <button
                type="button"
                class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-[0.65rem] font-black text-white transition hover:bg-white/10 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="readyApprovalCount === 0"
                @click="openAutoApproveModal"
            >
                <Sparkles class="size-4" />
                <span>Auto All</span>
            </button>
        </div>
    </nav>
</template>
