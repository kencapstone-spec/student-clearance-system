<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
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
    role: string;
    course?: Course | null;
};

type Office = {
    id: number;
    name: string;
    group: string;
    sort_order: number;
    is_final_approver: boolean;
};

type Approval = {
    id: number;
    office_id: number;
    status: 'not_requested' | 'pending' | 'approved' | 'rejected';
    remarks?: string | null;
    office?: Office;
};

type ClearanceRequest = {
    id: number;
    semester: string;
    school_year: string;
    status: 'pending' | 'cleared';
    approvals: Approval[];
} | null;

const props = defineProps<{
    student: Student;
    offices: Office[];
    clearanceRequest: ClearanceRequest;
}>();

const approvals = computed(() => props.clearanceRequest?.approvals ?? []);

const approvedCount = computed(() => {
    return approvals.value.filter((approval) => approval.status === 'approved')
        .length;
});

const pendingCount = computed(() => {
    if (!props.clearanceRequest) {
        return 0;
    }

    return approvals.value.filter((approval) => approval.status === 'pending')
        .length;
});

const notClearedCount = computed(() => {
    if (!props.clearanceRequest) {
        return props.offices.length;
    }

    return props.offices.length - approvedCount.value;
});

const regularApprovals = computed(() => {
    if (!props.clearanceRequest) {
        return [];
    }

    return props.clearanceRequest.approvals.filter((approval) => {
        return !approval.office?.is_final_approver;
    });
});

const presidentApproval = computed(() => {
    if (!props.clearanceRequest) {
        return null;
    }

    return (
        props.clearanceRequest.approvals.find((approval) => {
            return approval.office?.is_final_approver;
        }) ?? null
    );
});

const isFullyCleared = computed(() => {
    if (!props.clearanceRequest) {
        return false;
    }

    if (regularApprovals.value.length === 0) {
        return false;
    }

    const allRegularApproved = regularApprovals.value.every((approval) => {
        return approval.status === 'approved';
    });

    const presidentApproved = presidentApproval.value?.status === 'approved';

    return allRegularApproved && presidentApproved;
});

const finalClearanceLabel = computed(() => {
    if (isFullyCleared.value) {
        return 'Fully Cleared';
    }

    if (props.clearanceRequest) {
        return 'In Progress';
    }

    return 'Not Started';
});

const progressPercentage = computed(() => {
    if (isFullyCleared.value) {
        return 100;
    }

    if (props.offices.length === 0) {
        return 0;
    }

    return Math.round((approvedCount.value / props.offices.length) * 100);
});

const officeStatuses = computed(() => {
    return props.offices.map((office) => {
        const approval = approvals.value.find(
            (item) => item.office_id === office.id,
        );

        return {
            ...office,
            approvalId: approval?.id ?? null,
            status: approval?.status ?? 'not_started',
            remarks: approval?.remarks ?? null,
        };
    });
});

const progressMessage = computed(() => {
    if (!props.clearanceRequest) {
        return 'Submit your first clearance request to start your clearance progress.';
    }

    if (isFullyCleared.value) {
        return 'Your clearance is fully cleared and approved by the College President.';
    }

    if (progressPercentage.value === 100) {
        return 'All offices have approved your clearance request. Waiting for final approval.';
    }

    return 'Keep going! Your clearance request is being processed.';
});

const statusLabel = (status: string) => {
    if (status === 'approved') {
        return 'Cleared';
    }

    if (status === 'pending') {
        return 'Pending';
    }

    if (status === 'rejected') {
        return 'Not Cleared';
    }

    if (status === 'not_requested') {
        return 'Not Requested';
    }

    return 'Not Started';
};

const statusClass = (status: string) => {
    if (status === 'approved') {
        return 'text-green-700';
    }

    if (status === 'pending') {
        return 'text-orange-600';
    }

    if (status === 'rejected') {
        return 'text-red-600';
    }

    if (status === 'not_requested') {
        return 'text-slate-500';
    }

    return 'text-slate-500';
};

const showClearanceDetailsModal = ref(false);

const showSubmitRequestModal = ref(false);

const selectedOfficeIds = ref<number[]>([]);

const selectedCompliedApproval = ref<{
    approvalId: number | null;
    officeName: string;
} | null>(null);

const openClearanceDetailsModal = () => {
    showClearanceDetailsModal.value = true;
};

const closeClearanceDetailsModal = () => {
    showClearanceDetailsModal.value = false;
};

const regularOffices = computed(() => {
    return props.offices.filter((office) => !office.is_final_approver);
});

const requestableOffices = computed(() => {
    if (!props.clearanceRequest) {
        return regularOffices.value;
    }

    return officeStatuses.value.filter((office) => {
        return !office.is_final_approver && office.status === 'not_requested';
    });
});

const openSubmitRequestModal = () => {
    selectedOfficeIds.value = [];
    showSubmitRequestModal.value = true;
};

const closeSubmitRequestModal = () => {
    selectedOfficeIds.value = [];
    showSubmitRequestModal.value = false;
};

const toggleOfficeSelection = (officeId: number) => {
    if (selectedOfficeIds.value.includes(officeId)) {
        selectedOfficeIds.value = selectedOfficeIds.value.filter(
            (id) => id !== officeId,
        );
        return;
    }

    selectedOfficeIds.value = [...selectedOfficeIds.value, officeId];
};

const openMarkAsCompliedModal = (
    approvalId: number | null,
    officeName: string,
) => {
    selectedCompliedApproval.value = {
        approvalId,
        officeName,
    };
};

const closeMarkAsCompliedModal = () => {
    selectedCompliedApproval.value = null;
};

const confirmMarkAsComplied = () => {
    if (!selectedCompliedApproval.value) {
        return;
    }

    markAsComplied(selectedCompliedApproval.value.approvalId);
    closeMarkAsCompliedModal();
};

const markAsComplied = (approvalId: number | null) => {
    if (!approvalId) {
        return;
    }

    router.patch(`/student/clearance-approvals/${approvalId}/mark-as-complied`);
};

const submitClearanceRequest = () => {
    if (selectedOfficeIds.value.length === 0) {
        return;
    }

    const payload = {
        office_ids: selectedOfficeIds.value,
    };

    const options = {
        onSuccess: () => {
            closeSubmitRequestModal();
        },
    };

    if (props.clearanceRequest) {
        router.patch(
            '/student/clearance-requests/request-more-offices',
            payload,
            options,
        );

        return;
    }

    router.post('/student/clearance-requests', payload, options);
};
</script>

<template>
    <Head title="Student Dashboard" />

    <div class="min-h-screen bg-slate-50 p-4 text-slate-900">
        <div class="mx-auto flex max-w-7xl flex-col gap-6">
            <!-- Welcome Banner -->
            <section
                class="overflow-hidden rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-100 via-blue-50 to-white shadow-sm"
            >
                <div class="grid gap-6 p-6 md:grid-cols-[1fr_280px] md:p-8">
                    <div class="flex flex-col justify-center gap-4">
                        <div>
                            <p class="text-sm font-medium text-blue-700">
                                Student Clearance System
                            </p>

                            <h1 class="mt-2 text-3xl font-bold text-blue-950">
                                Welcome, {{ student.name }}!
                            </h1>

                            <p class="mt-2 max-w-xl text-blue-900/80">
                                Track your clearance progress and submit
                                requests as needed.
                            </p>

                            <p class="mt-2 text-sm text-blue-800/70">
                                Student ID: {{ student.student_id }}
                                <span v-if="student.course">
                                    • {{ student.course.code }}
                                </span>
                            </p>
                        </div>

                        <div
                            class="rounded-2xl bg-white p-5 shadow-sm md:max-w-xl"
                        >
                            <div class="mb-3 flex items-center gap-2">
                                <span class="text-lg font-bold text-green-700">
                                    {{ progressPercentage }}% Completed
                                </span>
                            </div>

                            <div
                                class="h-3 overflow-hidden rounded-full bg-slate-100"
                            >
                                <div
                                    class="h-full rounded-full bg-green-600 transition-all"
                                    :style="{ width: `${progressPercentage}%` }"
                                ></div>
                            </div>

                            <p class="mt-3 text-sm text-slate-600">
                                {{ progressMessage }}
                            </p>

                            <div
                                v-if="isFullyCleared"
                                class="mt-4 rounded-xl border border-green-200 bg-green-50 p-4 text-green-800"
                            >
                                <p class="text-sm font-semibold">
                                    Final Clearance Status
                                </p>

                                <h2 class="mt-1 text-2xl font-bold">
                                    Fully Cleared
                                </h2>

                                <p class="mt-1 text-sm">
                                    Congratulations! Your clearance has been
                                    approved by all required offices and
                                    received final approval from the College
                                    President.
                                </p>
                            </div>

                            <div
                                v-else
                                class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4 text-blue-800"
                            >
                                <p class="text-sm font-semibold">
                                    Final Clearance Status
                                </p>

                                <h2 class="mt-1 text-2xl font-bold">
                                    {{ finalClearanceLabel }}
                                </h2>

                                <p class="mt-1 text-sm">
                                    Your clearance is not yet fully cleared.
                                    Please wait for all required offices and the
                                    College President final approval.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="hidden items-center justify-center md:flex">
                        <div
                            class="flex h-56 w-56 items-center justify-center rounded-full bg-blue-200 text-7xl"
                        >
                            🎓
                        </div>
                    </div>
                </div>
            </section>

            <!-- Summary Cards -->
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-2xl"
                        >
                            👥
                        </div>
                        <div>
                            <p class="font-semibold text-blue-700">
                                Total Cleared
                            </p>
                            <p class="mt-1 text-3xl font-bold text-blue-950">
                                {{ approvedCount }} / {{ offices.length }}
                            </p>
                            <p class="text-sm text-slate-500">Departments</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-100 text-2xl"
                        >
                            ⏰
                        </div>
                        <div>
                            <p class="font-semibold text-orange-600">
                                Pending Requests
                            </p>
                            <p class="mt-1 text-3xl font-bold text-blue-950">
                                {{ pendingCount }}
                            </p>
                            <p class="text-sm text-slate-500">Departments</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-green-100 text-2xl"
                        >
                            ✅
                        </div>
                        <div>
                            <p class="font-semibold text-green-700">Approved</p>
                            <p class="mt-1 text-3xl font-bold text-blue-950">
                                {{ approvedCount }}
                            </p>
                            <p class="text-sm text-slate-500">Departments</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100 text-2xl"
                        >
                            ❌
                        </div>
                        <div>
                            <p class="font-semibold text-red-600">
                                Not Cleared
                            </p>
                            <p class="mt-1 text-3xl font-bold text-blue-950">
                                {{ notClearedCount }}
                            </p>
                            <p class="text-sm text-slate-500">Departments</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main Content -->
            <section class="grid gap-6 xl:grid-cols-2">
                <!-- Recent Activity -->
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-blue-950">
                            Recent Activity
                        </h2>
                        <button class="text-sm font-medium text-blue-600">
                            View All
                        </button>
                    </div>

                    <div
                        v-if="!clearanceRequest"
                        class="rounded-xl border border-dashed p-6 text-center"
                    >
                        <p class="font-medium text-slate-700">
                            No recent activity yet.
                        </p>
                        <p class="mt-1 text-sm text-slate-500">
                            Your clearance updates will appear here.
                        </p>
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="office in officeStatuses.slice(0, 5)"
                            :key="office.id"
                            class="flex items-center justify-between border-b pb-3 text-sm"
                        >
                            <div>
                                <p class="font-medium text-blue-950">
                                    {{ office.name }}
                                </p>
                                <p class="text-slate-500">
                                    {{ statusLabel(office.status) }}
                                </p>
                            </div>

                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                :class="statusClass(office.status)"
                            >
                                {{ statusLabel(office.status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- My Clearance Status -->
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-blue-950">
                            My Clearance Status
                        </h2>
                        <button class="text-sm font-medium text-blue-600">
                            View All
                        </button>
                    </div>

                    <div class="max-h-96 space-y-3 overflow-y-auto pr-2">
                        <div
                            v-for="office in officeStatuses"
                            :key="office.id"
                            class="flex items-start justify-between gap-4 border-b pb-3 text-sm"
                        >
                            <div>
                                <span class="font-medium text-blue-950">
                                    {{ office.name }}
                                </span>

                                <p
                                    v-if="office.remarks"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{ office.remarks }}
                                </p>
                            </div>

                            <div class="flex flex-col items-end gap-2">
                                <span :class="statusClass(office.status)">
                                    {{ statusLabel(office.status) }}
                                </span>

                                <button
                                    v-if="office.status === 'rejected'"
                                    type="button"
                                    class="rounded-lg bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-700 transition hover:bg-orange-200"
                                    @click="
                                        openMarkAsCompliedModal(
                                            office.approvalId,
                                            office.name,
                                        )
                                    "
                                >
                                    Mark as Complied
                                </button>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="mt-4 w-full rounded-xl bg-blue-600 px-4 py-3 font-semibold text-white transition hover:bg-blue-700"
                            @click="openClearanceDetailsModal"
                        >
                            View Clearance Details →
                        </button>
                    </div>
                </div>
            </section>

            <!-- Submit Request -->
            <section
                class="flex flex-col gap-4 rounded-2xl border bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-2xl"
                    >
                        📄
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-blue-950">
                            Submit New Clearance Request
                        </h2>
                        <p class="text-sm text-slate-500">
                            Need to request clearance from all required offices?
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="rounded-xl px-6 py-3 font-semibold text-white transition"
                    :class="
                        requestableOffices.length === 0
                            ? 'cursor-not-allowed bg-slate-400'
                            : 'bg-blue-600 hover:bg-blue-700'
                    "
                    :disabled="requestableOffices.length === 0"
                    @click="openSubmitRequestModal"
                >
                    {{
                        !clearanceRequest
                            ? 'Submit Clearance Request →'
                            : requestableOffices.length > 0
                              ? 'Request More Offices →'
                              : 'All Offices Requested'
                    }}
                </button>
            </section>

            <!-- Submit Clearance Request Office Selection Modal -->
            <div
                v-if="showSubmitRequestModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6"
            >
                <div
                    class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white shadow-xl"
                >
                    <div
                        class="flex items-start justify-between border-b border-slate-200 px-6 py-4"
                    >
                        <div>
                            <h2 class="text-xl font-bold text-blue-950">
                                Select Offices to Request
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Choose the offices you want to request clearance
                                from. Only offices that are still Not Requested
                                will appear here.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg px-3 py-1 text-lg font-bold text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                            @click="closeSubmitRequestModal"
                        >
                            ×
                        </button>
                    </div>

                    <div class="space-y-4 px-6 py-5">
                        <div
                            class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800"
                        >
                            Select at least one office. Selected offices will
                            become
                            <span class="font-semibold">Pending</span>.
                            Unselected offices will stay as
                            <span class="font-semibold">Not Requested</span>.
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <button
                                v-for="office in requestableOffices"
                                :key="office.id"
                                type="button"
                                class="rounded-xl border p-4 text-left transition"
                                :class="
                                    selectedOfficeIds.includes(office.id)
                                        ? 'border-blue-600 bg-blue-50 text-blue-900'
                                        : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                "
                                @click="toggleOfficeSelection(office.id)"
                            >
                                <div class="flex items-start gap-3">
                                    <div
                                        class="mt-1 flex h-5 w-5 items-center justify-center rounded border"
                                        :class="
                                            selectedOfficeIds.includes(
                                                office.id,
                                            )
                                                ? 'border-blue-600 bg-blue-600 text-white'
                                                : 'border-slate-300 bg-white'
                                        "
                                    >
                                        <span
                                            v-if="
                                                selectedOfficeIds.includes(
                                                    office.id,
                                                )
                                            "
                                            class="text-xs font-bold"
                                        >
                                            ✓
                                        </span>
                                    </div>

                                    <div>
                                        <p class="font-semibold">
                                            {{ office.name }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ office.group }}
                                        </p>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div
                        class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p class="text-sm text-slate-500">
                            Selected offices:
                            <span class="font-semibold text-blue-700">
                                {{ selectedOfficeIds.length }}
                            </span>
                        </p>

                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                class="rounded-xl border border-slate-300 px-4 py-2 font-semibold text-slate-700 transition hover:bg-slate-100"
                                @click="closeSubmitRequestModal"
                            >
                                Cancel
                            </button>

                            <button
                                type="button"
                                class="rounded-xl px-4 py-2 font-semibold text-white transition"
                                :class="
                                    selectedOfficeIds.length === 0
                                        ? 'cursor-not-allowed bg-slate-400'
                                        : 'bg-blue-600 hover:bg-blue-700'
                                "
                                :disabled="selectedOfficeIds.length === 0"
                                @click="submitClearanceRequest"
                            >
                                Submit Selected Offices
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clearance Details Modal -->
            <div
                v-if="showClearanceDetailsModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6"
            >
                <div
                    class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-2xl bg-white shadow-xl"
                >
                    <div
                        class="flex items-start justify-between border-b border-slate-200 px-6 py-4"
                    >
                        <div>
                            <h2 class="text-xl font-bold text-blue-950">
                                Clearance Details
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                View your complete clearance request status and
                                office approval breakdown.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg px-3 py-1 text-lg font-bold text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                            @click="closeClearanceDetailsModal"
                        >
                            ×
                        </button>
                    </div>

                    <div class="space-y-6 px-6 py-5">
                        <!-- Student Information -->
                        <div class="grid gap-4 md:grid-cols-3">
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Student Name
                                </p>
                                <p class="mt-1 font-bold text-blue-950">
                                    {{ student.name }}
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Student ID
                                </p>
                                <p class="mt-1 font-bold text-blue-950">
                                    {{ student.student_id }}
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Course
                                </p>
                                <p class="mt-1 font-bold text-blue-950">
                                    {{ student.course?.code ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <!-- Request Information -->
                        <div class="grid gap-4 md:grid-cols-4">
                            <div class="rounded-xl border p-4">
                                <p
                                    class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Semester
                                </p>
                                <p class="mt-1 font-bold text-blue-950">
                                    {{
                                        clearanceRequest?.semester ??
                                        'No request yet'
                                    }}
                                </p>
                            </div>

                            <div class="rounded-xl border p-4">
                                <p
                                    class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    School Year
                                </p>
                                <p class="mt-1 font-bold text-blue-950">
                                    {{
                                        clearanceRequest?.school_year ??
                                        'No request yet'
                                    }}
                                </p>
                            </div>

                            <div class="rounded-xl border p-4">
                                <p
                                    class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Progress
                                </p>
                                <p class="mt-1 font-bold text-blue-950">
                                    {{ approvedCount }} / {{ offices.length }}
                                </p>
                            </div>

                            <div class="rounded-xl border p-4">
                                <p
                                    class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Final Status
                                </p>
                                <span
                                    class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-bold"
                                    :class="
                                        isFullyCleared
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-blue-100 text-blue-700'
                                    "
                                >
                                    {{ finalClearanceLabel }}
                                </span>
                            </div>
                        </div>

                        <!-- Office Breakdown -->
                        <div>
                            <h3 class="mb-3 text-lg font-bold text-blue-950">
                                Office Approval Breakdown
                            </h3>

                            <div
                                class="overflow-hidden rounded-xl border border-slate-200"
                            >
                                <table
                                    class="min-w-full divide-y divide-slate-200"
                                >
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-bold tracking-wide text-slate-500 uppercase"
                                            >
                                                Office
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-bold tracking-wide text-slate-500 uppercase"
                                            >
                                                Status
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-bold tracking-wide text-slate-500 uppercase"
                                            >
                                                Remarks
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody
                                        class="divide-y divide-slate-200 bg-white"
                                    >
                                        <tr
                                            v-for="office in officeStatuses"
                                            :key="office.id"
                                            class="hover:bg-slate-50"
                                        >
                                            <td
                                                class="px-4 py-3 text-sm font-semibold text-blue-950"
                                            >
                                                {{ office.name }}
                                            </td>

                                            <td class="px-4 py-3 text-sm">
                                                <span
                                                    class="font-semibold"
                                                    :class="
                                                        statusClass(
                                                            office.status,
                                                        )
                                                    "
                                                >
                                                    {{
                                                        statusLabel(
                                                            office.status,
                                                        )
                                                    }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-3 text-sm">
                                                <span
                                                    v-if="office.remarks"
                                                    class="text-red-600"
                                                >
                                                    {{ office.remarks }}
                                                </span>

                                                <span
                                                    v-else
                                                    class="text-slate-400"
                                                >
                                                    No remarks
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex justify-end border-t border-slate-200 px-6 py-4"
                    >
                        <button
                            type="button"
                            class="rounded-xl bg-slate-900 px-5 py-2 font-semibold text-white transition hover:bg-slate-800"
                            @click="closeClearanceDetailsModal"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mark as Complied Confirmation Modal -->
            <div
                v-if="selectedCompliedApproval"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6"
            >
                <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2 class="text-xl font-bold text-blue-950">
                            Mark as Complied?
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Confirm that you have already complied with the
                            requirement for this office.
                        </p>
                    </div>

                    <div class="space-y-4 px-6 py-5">
                        <div
                            class="rounded-xl border border-orange-200 bg-orange-50 p-4"
                        >
                            <p class="text-sm font-semibold text-orange-700">
                                Office
                            </p>

                            <p class="mt-1 font-bold text-orange-900">
                                {{ selectedCompliedApproval.officeName }}
                            </p>
                        </div>

                        <p class="text-sm text-slate-600">
                            Once confirmed, this office status will return to
                            <span class="font-semibold text-orange-600"
                                >Pending</span
                            >
                            so the assigned staff can review your clearance
                            again.
                        </p>
                    </div>

                    <div
                        class="flex justify-end gap-3 border-t border-slate-200 px-6 py-4"
                    >
                        <button
                            type="button"
                            class="rounded-xl border border-slate-300 px-4 py-2 font-semibold text-slate-700 transition hover:bg-slate-100"
                            @click="closeMarkAsCompliedModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            class="rounded-xl bg-orange-600 px-4 py-2 font-semibold text-white transition hover:bg-orange-700"
                            @click="confirmMarkAsComplied"
                        >
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
