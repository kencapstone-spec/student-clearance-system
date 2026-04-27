<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';
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
    status: 'pending' | 'approved' | 'rejected';
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

    return 'text-slate-500';
};

const submitClearanceRequest = () => {
    router.post('/student/clearance-requests');
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
                            class="flex items-center justify-between border-b pb-3 text-sm"
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

                            <span :class="statusClass(office.status)">
                                {{ statusLabel(office.status) }}
                            </span>
                        </div>

                        <button
                            class="mt-4 w-full rounded-xl bg-blue-600 px-4 py-3 font-semibold text-white transition hover:bg-blue-700"
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
                        clearanceRequest
                            ? 'cursor-not-allowed bg-slate-400'
                            : 'bg-blue-600 hover:bg-blue-700'
                    "
                    :disabled="!!clearanceRequest"
                    @click="submitClearanceRequest"
                >
                    {{
                        clearanceRequest
                            ? 'Clearance Request Submitted'
                            : 'Submit Clearance Request →'
                    }}
                </button>
            </section>
        </div>
    </div>
</template>
