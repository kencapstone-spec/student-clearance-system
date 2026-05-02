<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Building2,
    CheckCircle2,
    ClipboardCheck,
    Clock3,
    Filter,
    Inbox,
    ShieldCheck,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Office = {
    id: number;
    name: string;
    group: string;
};

type Course = {
    id: number;
    code: string;
    name: string;
};

type Student = {
    id: number;
    name: string;
    student_id: string;
    course?: Course | null;
};

type ClearanceRequest = {
    id: number;
    semester: string;
    school_year: string;
    user: Student;
};

type Approval = {
    id: number;
    status: 'pending' | 'approved' | 'rejected';
    remarks?: string | null;
    clearance_request: ClearanceRequest;
    office: Office;
};

type Staff = {
    id: number;
    name: string;
    student_id: string;
    role: string;
    office?: Office | null;
};

type FilterStatus = 'all' | 'pending' | 'approved' | 'rejected';

const props = defineProps<{
    staff: Staff;
    approvals: Approval[];
}>();

const activeFilter = ref<FilterStatus>('pending');

const successMessage = ref('');
const errorMessage = ref('');

const selectedRejectApprovalId = ref<number | null>(null);
const rejectRemarks = ref('');
const showRejectModal = ref(false);

const selectedApprovalForApproval = ref<Approval | null>(null);
const showApproveModal = ref(false);

const clearMessages = () => {
    successMessage.value = '';
    errorMessage.value = '';
};

const setFilter = (filter: FilterStatus) => {
    activeFilter.value = filter;
};

const pendingApprovals = computed(() => {
    return props.approvals.filter((approval) => approval.status === 'pending');
});

const approvedApprovals = computed(() => {
    return props.approvals.filter((approval) => approval.status === 'approved');
});

const rejectedApprovals = computed(() => {
    return props.approvals.filter((approval) => approval.status === 'rejected');
});

const filteredApprovals = computed(() => {
    if (activeFilter.value === 'pending') {
        return pendingApprovals.value;
    }

    if (activeFilter.value === 'approved') {
        return approvedApprovals.value;
    }

    if (activeFilter.value === 'rejected') {
        return rejectedApprovals.value;
    }

    return props.approvals;
});

const openApproveModal = (approval: Approval) => {
    clearMessages();
    selectedApprovalForApproval.value = approval;
    showApproveModal.value = true;
};

const closeApproveModal = () => {
    selectedApprovalForApproval.value = null;
    showApproveModal.value = false;
};

const confirmApprove = () => {
    if (!selectedApprovalForApproval.value) {
        return;
    }

    clearMessages();

    router.patch(
        `/staff/clearance-approvals/${selectedApprovalForApproval.value.id}/approve`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                closeApproveModal();
                successMessage.value = 'Request approved successfully.';
            },
            onError: () => {
                errorMessage.value =
                    'Unable to approve request. Please try again.';
            },
        },
    );
};

const rejectRequest = (approvalId: number) => {
    clearMessages();
    selectedRejectApprovalId.value = approvalId;
    rejectRemarks.value = '';
    showRejectModal.value = true;
};

const closeRejectModal = () => {
    selectedRejectApprovalId.value = null;
    rejectRemarks.value = '';
    showRejectModal.value = false;
};

const submitRejectRequest = () => {
    if (selectedRejectApprovalId.value === null) {
        return;
    }

    if (!rejectRemarks.value.trim()) {
        errorMessage.value = 'Remarks are required.';

        return;
    }

    clearMessages();

    router.patch(
        `/staff/clearance-approvals/${selectedRejectApprovalId.value}/reject`,
        {
            remarks: rejectRemarks.value.trim(),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                closeRejectModal();
                successMessage.value = 'Request rejected successfully.';
            },
            onError: () => {
                errorMessage.value =
                    'Unable to reject request. Please try again.';
            },
        },
    );
};

const statusLabel = (status: string) => {
    if (status === 'approved') {
        return 'Approved';
    }

    if (status === 'rejected') {
        return 'Rejected';
    }

    return 'Pending';
};

const statusBadgeClass = (status: string) => {
    if (status === 'approved') {
        return 'border-green-200 bg-green-50 text-green-700';
    }

    if (status === 'rejected') {
        return 'border-red-200 bg-red-50 text-red-700';
    }

    return 'border-orange-200 bg-orange-50 text-orange-700';
};

const filterButtonClass = (filter: FilterStatus) => {
    if (activeFilter.value === filter) {
        return 'border-blue-700 bg-blue-700 text-white shadow-lg shadow-blue-700/20';
    }

    return 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700';
};
</script>

<template>
    <Head title="Staff Clearance Requests" />

    <div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-blue-50/40 p-4 text-slate-900 md:p-6">
        <div class="mx-auto flex max-w-7xl flex-col gap-6">
            <!-- Hero -->
            <section class="overflow-hidden rounded-4xl border border-slate-200 bg-white/95 shadow-xl shadow-slate-200/70">
                <div class="grid gap-8 p-6 lg:grid-cols-[1fr_300px] lg:p-8">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-blue-700">
                            <ShieldCheck class="size-4" />
                            Staff / Approver Panel
                        </div>

                        <h1 class="mt-5 text-4xl font-black tracking-tight text-blue-950">
                            Clearance Requests
                        </h1>

                        <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600">
                            Review, approve, or reject student clearance requests assigned to your office.
                        </p>

                        <div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/80 p-4 text-sm font-medium leading-6 text-blue-900">
                            <span class="font-black">Logged in as:</span>
                            {{ staff.name }}
                            <span v-if="staff.office">
                                - {{ staff.office.name }}
                            </span>
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
                        <div class="relative grid h-52 w-52 place-items-center rounded-4xl border border-blue-100 bg-linear-to-br from-white to-blue-50 shadow-2xl shadow-slate-300/70">
                            <div class="grid h-20 w-20 place-items-center rounded-3xl bg-blue-700 text-white shadow-xl shadow-blue-700/25">
                                <ClipboardCheck class="size-10" />
                            </div>

                            <p class="text-center text-sm font-black uppercase tracking-[0.18em] text-blue-700">
                                Office Review
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Summary Cards -->
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="grid h-14 w-14 place-items-center rounded-2xl bg-blue-50 text-blue-700 shadow-sm">
                            <Building2 class="size-7" />
                        </div>

                        <div>
                            <p class="text-sm font-black uppercase tracking-wide text-blue-700">
                                Assigned Office
                            </p>
                            <p class="mt-1 text-xl font-black text-blue-950">
                                {{ staff.office?.name ?? 'No Office Assigned' }}
                            </p>
                            <p class="text-sm font-medium text-slate-500">
                                Current responsibility
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="grid h-14 w-14 place-items-center rounded-2xl bg-orange-50 text-orange-600 shadow-sm">
                            <Clock3 class="size-7" />
                        </div>

                        <div>
                            <p class="text-sm font-black uppercase tracking-wide text-orange-600">
                                Pending
                            </p>
                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ pendingApprovals.length }}
                            </p>
                            <p class="text-sm font-medium text-slate-500">
                                Waiting review
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="grid h-14 w-14 place-items-center rounded-2xl bg-green-50 text-green-700 shadow-sm">
                            <CheckCircle2 class="size-7" />
                        </div>

                        <div>
                            <p class="text-sm font-black uppercase tracking-wide text-green-700">
                                Approved
                            </p>
                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ approvedApprovals.length }}
                            </p>
                            <p class="text-sm font-medium text-slate-500">
                                Completed reviews
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="grid h-14 w-14 place-items-center rounded-2xl bg-red-50 text-red-600 shadow-sm">
                            <XCircle class="size-7" />
                        </div>

                        <div>
                            <p class="text-sm font-black uppercase tracking-wide text-red-600">
                                Rejected
                            </p>
                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ rejectedApprovals.length }}
                            </p>
                            <p class="text-sm font-medium text-slate-500">
                                Needs correction
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Records -->
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-sm shadow-slate-200/70">
                <div class="flex flex-col gap-4 border-b border-slate-200 bg-white px-6 py-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                            Office Queue
                        </p>

                        <h2 class="mt-1 text-xl font-black text-blue-950">
                            Office Clearance Records
                        </h2>

                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Filter pending, approved, and rejected requests assigned to your office.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                            :class="filterButtonClass('all')"
                            @click.prevent.stop="setFilter('all')"
                        >
                            <Filter class="size-4" />
                            All
                        </button>

                        <button
                            type="button"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                            :class="filterButtonClass('pending')"
                            @click.prevent.stop="setFilter('pending')"
                        >
                            Pending
                        </button>

                        <button
                            type="button"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                            :class="filterButtonClass('approved')"
                            @click.prevent.stop="setFilter('approved')"
                        >
                            Approved
                        </button>

                        <button
                            type="button"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                            :class="filterButtonClass('rejected')"
                            @click.prevent.stop="setFilter('rejected')"
                        >
                            Rejected
                        </button>
                    </div>
                </div>

                <div
                    v-if="filteredApprovals.length === 0"
                    class="p-12 text-center"
                >
                    <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-blue-50 text-blue-700">
                        <Inbox class="size-8" />
                    </div>

                    <p class="mt-4 font-black text-slate-700">
                        No records found.
                    </p>

                    <p class="mt-1 text-sm font-medium text-slate-500">
                        Records will appear here based on the selected filter.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-6 py-4 text-xs font-black uppercase tracking-wide">
                                    Student
                                </th>
                                <th class="px-6 py-4 text-xs font-black uppercase tracking-wide">
                                    Student ID
                                </th>
                                <th class="px-6 py-4 text-xs font-black uppercase tracking-wide">
                                    Course
                                </th>
                                <th class="px-6 py-4 text-xs font-black uppercase tracking-wide">
                                    Semester
                                </th>
                                <th class="px-6 py-4 text-xs font-black uppercase tracking-wide">
                                    Status
                                </th>
                                <th class="px-6 py-4 text-xs font-black uppercase tracking-wide">
                                    Remarks
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-black uppercase tracking-wide">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="approval in filteredApprovals"
                                :key="approval.id"
                                class="transition hover:bg-blue-50/50"
                            >
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-black text-blue-950">
                                            {{
                                                approval.clearance_request.user
                                                    .name
                                            }}
                                        </p>
                                        <p class="mt-1 text-xs font-medium text-slate-500">
                                            Request #{{ approval.clearance_request.id }}
                                        </p>
                                    </div>
                                </td>

                                <td class="px-6 py-4 font-semibold text-slate-700">
                                    {{
                                        approval.clearance_request.user
                                            .student_id
                                    }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">
                                        {{
                                            approval.clearance_request.user.course
                                                ?.code ?? 'N/A'
                                        }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 font-semibold text-slate-700">
                                    {{ approval.clearance_request.semester }},
                                    {{ approval.clearance_request.school_year }}
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                        :class="
                                            statusBadgeClass(approval.status)
                                        "
                                    >
                                        {{ statusLabel(approval.status) }}
                                    </span>
                                </td>

                                <td class="max-w-xs px-6 py-4 text-sm font-medium text-slate-600">
                                    {{ approval.remarks ?? '-' }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div
                                        v-if="approval.status === 'pending'"
                                        class="flex justify-end gap-2"
                                    >
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-2xl bg-green-700 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:-translate-y-0.5 hover:bg-green-800 hover:shadow-lg"
                                            @click="openApproveModal(approval)"
                                        >
                                            <CheckCircle2 class="size-4" />
                                            Approve
                                        </button>

                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-2xl bg-red-600 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-red-600/20 transition hover:-translate-y-0.5 hover:bg-red-700 hover:shadow-lg"
                                            @click="rejectRequest(approval.id)"
                                        >
                                            <XCircle class="size-4" />
                                            Reject
                                        </button>
                                    </div>

                                    <span
                                        v-else
                                        class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500"
                                    >
                                        Completed
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <!-- Reject Modal -->
    <div
        v-if="showRejectModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
        @click.self="closeRejectModal"
    >
        <div class="w-full max-w-lg rounded-4xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-900/20">
            <div class="flex items-start gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-red-50 text-red-600">
                    <XCircle class="size-6" />
                </div>

                <div>
                    <h3 class="text-xl font-black text-blue-950">
                        Reject Clearance Request
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Provide a clear reason why this clearance request is being rejected. The student will use this remark as their guide for correction.
                    </p>
                </div>
            </div>

            <div class="mt-5">
                <label
                    for="reject-remarks"
                    class="text-sm font-black text-slate-700"
                >
                    Rejection Remarks
                </label>

                <textarea
                    id="reject-remarks"
                    v-model="rejectRemarks"
                    rows="5"
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-white p-4 text-sm font-medium text-slate-900 shadow-sm outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                    placeholder="Example: Please settle your library clearance before requesting approval."
                ></textarea>

                <p class="mt-2 text-xs font-medium text-slate-500">
                    Remarks are required before submitting a rejection.
                </p>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    @click="closeRejectModal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="rounded-2xl bg-red-600 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-red-600/20 transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!rejectRemarks.trim()"
                    @click="submitRejectRequest"
                >
                    Submit Rejection
                </button>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div
        v-if="showApproveModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
        @click.self="closeApproveModal"
    >
        <div class="w-full max-w-md rounded-4xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-900/20">
            <div class="flex items-start gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-green-50 text-green-700">
                    <CheckCircle2 class="size-6" />
                </div>

                <div>
                    <h2 class="text-xl font-black text-blue-950">
                        Confirm Clearance Approval
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Are you sure you want to approve this clearance request? This will mark the student as cleared for your assigned office.
                    </p>
                </div>
            </div>

            <div
                v-if="selectedApprovalForApproval"
                class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700"
            >
                <div class="grid gap-3">
                    <p>
                        <span class="font-black">Student:</span>
                        {{
                            selectedApprovalForApproval.clearance_request.user
                                .name
                        }}
                    </p>

                    <p>
                        <span class="font-black">Student ID:</span>
                        {{
                            selectedApprovalForApproval.clearance_request.user
                                .student_id
                        }}
                    </p>

                    <p>
                        <span class="font-black">Semester:</span>
                        {{
                            selectedApprovalForApproval.clearance_request
                                .semester
                        }}
                    </p>

                    <p>
                        <span class="font-black">School Year:</span>
                        {{
                            selectedApprovalForApproval.clearance_request
                                .school_year
                        }}
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    @click="closeApproveModal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="rounded-2xl bg-green-700 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:bg-green-800"
                    @click="confirmApprove"
                >
                    Confirm Approval
                </button>
            </div>
        </div>
    </div>
</template>