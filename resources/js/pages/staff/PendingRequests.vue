<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
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
        return 'bg-green-100 text-green-700';
    }

    if (status === 'rejected') {
        return 'bg-red-100 text-red-700';
    }

    return 'bg-orange-100 text-orange-700';
};

const filterButtonClass = (filter: FilterStatus) => {
    if (activeFilter.value === filter) {
        return 'bg-blue-600 text-white';
    }

    return 'bg-white text-slate-700 hover:bg-slate-100';
};
</script>

<template>
    <Head title="Staff Clearance Requests" />

    <div class="min-h-screen bg-slate-50 p-4 text-slate-900">
        <div class="mx-auto flex max-w-7xl flex-col gap-6">
            <section class="rounded-2xl border bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-blue-700">
                    Staff / Approver Panel
                </p>

                <h1 class="mt-2 text-3xl font-bold text-blue-950">
                    Clearance Requests
                </h1>

                <p class="mt-2 text-slate-600">
                    Review and manage student clearance requests assigned to
                    your office.
                </p>

                <div
                    class="mt-4 rounded-xl bg-blue-50 p-4 text-sm text-blue-900"
                >
                    <strong>Logged in as:</strong> {{ staff.name }}
                    <span v-if="staff.office"> • {{ staff.office.name }}</span>
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

            <section class="grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-blue-700">Assigned Office</p>
                    <p class="mt-2 text-2xl font-bold text-blue-950">
                        {{ staff.office?.name ?? 'No Office Assigned' }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-orange-600">Pending</p>
                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ pendingApprovals.length }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-green-700">Approved</p>
                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ approvedApprovals.length }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-red-600">Rejected</p>
                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ rejectedApprovals.length }}
                    </p>
                </div>
            </section>

            <section class="rounded-2xl border bg-white shadow-sm">
                <div class="border-b p-6">
                    <div
                        class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <h2 class="text-xl font-bold text-blue-950">
                                Office Clearance Records
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Filter pending, approved, and rejected requests
                                assigned to your office.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="cursor-pointer rounded-xl px-4 py-2 text-sm font-semibold transition"
                                :class="filterButtonClass('all')"
                                @click.prevent.stop="setFilter('all')"
                            >
                                All
                            </button>

                            <button
                                type="button"
                                class="cursor-pointer rounded-xl px-4 py-2 text-sm font-semibold transition"
                                :class="filterButtonClass('pending')"
                                @click.prevent.stop="setFilter('pending')"
                            >
                                Pending
                            </button>

                            <button
                                type="button"
                                class="cursor-pointer rounded-xl px-4 py-2 text-sm font-semibold transition"
                                :class="filterButtonClass('approved')"
                                @click.prevent.stop="setFilter('approved')"
                            >
                                Approved
                            </button>

                            <button
                                type="button"
                                class="cursor-pointer rounded-xl px-4 py-2 text-sm font-semibold transition"
                                :class="filterButtonClass('rejected')"
                                @click.prevent.stop="setFilter('rejected')"
                            >
                                Rejected
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    v-if="filteredApprovals.length === 0"
                    class="p-8 text-center"
                >
                    <p class="font-medium text-slate-700">No records found.</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Records will appear here based on the selected filter.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100 text-slate-600">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Student</th>
                                <th class="px-6 py-3 font-semibold">
                                    Student ID
                                </th>
                                <th class="px-6 py-3 font-semibold">Course</th>
                                <th class="px-6 py-3 font-semibold">
                                    Semester
                                </th>
                                <th class="px-6 py-3 font-semibold">Status</th>
                                <th class="px-6 py-3 font-semibold">Remarks</th>
                                <th class="px-6 py-3 text-right font-semibold">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="approval in filteredApprovals"
                                :key="approval.id"
                                class="border-t"
                            >
                                <td class="px-6 py-4 font-medium text-blue-950">
                                    {{ approval.clearance_request.user.name }}
                                </td>

                                <td class="px-6 py-4">
                                    {{
                                        approval.clearance_request.user
                                            .student_id
                                    }}
                                </td>

                                <td class="px-6 py-4">
                                    {{
                                        approval.clearance_request.user.course
                                            ?.code ?? 'N/A'
                                    }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ approval.clearance_request.semester }},
                                    {{ approval.clearance_request.school_year }}
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="
                                            statusBadgeClass(approval.status)
                                        "
                                    >
                                        {{ statusLabel(approval.status) }}
                                    </span>
                                </td>

                                <td class="max-w-xs px-6 py-4 text-slate-600">
                                    {{ approval.remarks ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div
                                        v-if="approval.status === 'pending'"
                                        class="flex justify-end gap-2"
                                    >
                                        <button
                                            type="button"
                                            class="rounded-lg bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-700"
                                            @click="openApproveModal(approval)"
                                        >
                                            Approve
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-lg bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-700"
                                            @click="rejectRequest(approval.id)"
                                        >
                                            Reject
                                        </button>
                                    </div>

                                    <span v-else class="text-sm text-slate-400">
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

    <div
        v-if="showRejectModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @click.self="closeRejectModal"
    >
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
            <h3 class="text-xl font-bold text-blue-950">
                Reject Clearance Request
            </h3>

            <p class="mt-2 text-sm text-slate-600">
                Please provide a clear reason why this clearance request is
                being rejected. The student will use this remark as their guide
                for what needs to be corrected.
            </p>

            <div class="mt-5">
                <label
                    for="reject-remarks"
                    class="text-sm font-semibold text-slate-700"
                >
                    Rejection Remarks
                </label>

                <textarea
                    id="reject-remarks"
                    v-model="rejectRemarks"
                    rows="5"
                    class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    placeholder="Example: Please settle your library clearance before requesting approval."
                ></textarea>

                <p class="mt-2 text-xs text-slate-500">
                    Remarks are required before submitting a rejection.
                </p>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    class="rounded-xl border px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                    @click="closeRejectModal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!rejectRemarks.trim()"
                    @click="submitRejectRequest"
                >
                    Submit Rejection
                </button>
            </div>
        </div>
    </div>

    <div
        v-if="showApproveModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
        @click.self="closeApproveModal"
    >
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h2 class="text-lg font-bold text-gray-900">
                Confirm Clearance Approval
            </h2>

            <p class="mt-2 text-sm text-gray-600">
                Are you sure you want to approve this clearance request? This
                will mark the student as cleared for your assigned office.
            </p>

            <div
                v-if="selectedApprovalForApproval"
                class="mt-4 rounded-xl bg-gray-50 p-4 text-sm text-gray-700"
            >
                <p>
                    <span class="font-semibold">Student:</span>
                    {{
                        selectedApprovalForApproval.clearance_request.user.name
                    }}
                </p>

                <p>
                    <span class="font-semibold">Student ID:</span>
                    {{
                        selectedApprovalForApproval.clearance_request.user
                            .student_id
                    }}
                </p>

                <p>
                    <span class="font-semibold">Semester:</span>
                    {{ selectedApprovalForApproval.clearance_request.semester }}
                </p>

                <p>
                    <span class="font-semibold">School Year:</span>
                    {{
                        selectedApprovalForApproval.clearance_request
                            .school_year
                    }}
                </p>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100"
                    @click="closeApproveModal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                    @click="confirmApprove"
                >
                    Confirm Approval
                </button>
            </div>
        </div>
    </div>
</template>