<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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

type Approver = {
    id: number;
    name: string;
    student_id: string;
    role: string;
} | null;

type Approval = {
    id: number;
    status: 'pending' | 'approved' | 'rejected';
    remarks: string | null;
    acted_at: string | null;
    office: Office;
    approver: Approver;
};

type ClearanceRequest = {
    id: number;
    semester: string;
    school_year: string;
    status: 'pending' | 'cleared';
    submitted_at: string | null;
    cleared_at: string | null;
    created_at: string;
    user: Student;
    approvals: Approval[];
};

type StatusFilter =
    | 'all'
    | 'pending'
    | 'cleared'
    | 'needs_attention'
    | 'ready_for_final_approval';

const props = defineProps<{
    clearanceRequests: ClearanceRequest[];
}>();

const activeFilter = ref<StatusFilter>('all');
const selectedCourse = ref('all');
const searchQuery = ref('');
const selectedRequest = ref<ClearanceRequest | null>(null);
const showDetailsModal = ref(false);

const setFilter = (filter: StatusFilter) => {
    activeFilter.value = filter;
};

const clearFilters = () => {
    activeFilter.value = 'all';
    selectedCourse.value = 'all';
    searchQuery.value = '';
};

const openDetailsModal = (request: ClearanceRequest) => {
    selectedRequest.value = request;
    showDetailsModal.value = true;
};

const closeDetailsModal = () => {
    selectedRequest.value = null;
    showDetailsModal.value = false;
};

const availableCourses = computed(() => {
    const courseCodes = props.clearanceRequests
        .map((request) => request.user.course?.code)
        .filter((courseCode): courseCode is string => Boolean(courseCode));

    return [...new Set(courseCodes)].sort();
});

const rejectedApprovalCount = (request: ClearanceRequest) => {
    return request.approvals.filter((approval) => {
        return approval.status === 'rejected';
    }).length;
};

const hasRejectedApproval = (request: ClearanceRequest) => {
    return rejectedApprovalCount(request) > 0;
};

const regularApprovals = (request: ClearanceRequest) => {
    return request.approvals.filter((approval) => {
        return !approval.office.is_final_approver;
    });
};

const presidentApproval = (request: ClearanceRequest) => {
    return (
        request.approvals.find((approval) => {
            return approval.office.is_final_approver;
        }) ?? null
    );
};

const regularApprovedCount = (request: ClearanceRequest) => {
    return regularApprovals(request).filter((approval) => {
        return approval.status === 'approved';
    }).length;
};

const regularProgressPercentage = (request: ClearanceRequest) => {
    const regularApprovalList = regularApprovals(request);

    if (regularApprovalList.length === 0) {
        return 0;
    }

    return Math.round(
        (regularApprovedCount(request) / regularApprovalList.length) * 100,
    );
};

const isReadyForFinalApproval = (request: ClearanceRequest) => {
    const regularApprovalList = regularApprovals(request);
    const finalApproval = presidentApproval(request);

    return (
        request.status === 'pending' &&
        regularApprovalList.length > 0 &&
        regularApprovalList.every((approval) => {
            return approval.status === 'approved';
        }) &&
        finalApproval?.status === 'pending'
    );
};

const displayStatusLabel = (request: ClearanceRequest) => {
    if (request.status === 'cleared') {
        return 'Cleared';
    }

    if (isReadyForFinalApproval(request)) {
        return 'Ready for Final Approval';
    }

    return 'Pending';
};

const displayStatusBadgeClass = (request: ClearanceRequest) => {
    if (request.status === 'cleared') {
        return 'bg-green-100 text-green-700';
    }

    if (isReadyForFinalApproval(request)) {
        return 'bg-purple-100 text-purple-700';
    }

    return 'bg-orange-100 text-orange-700';
};

const filteredRequests = computed(() => {
    return props.clearanceRequests.filter((request) => {
        const matchesStatus =
            activeFilter.value === 'all' ||
            request.status === activeFilter.value ||
            (activeFilter.value === 'needs_attention' &&
                hasRejectedApproval(request)) ||
            (activeFilter.value === 'ready_for_final_approval' &&
                isReadyForFinalApproval(request));

        const courseCode = request.user.course?.code ?? 'N/A';

        const matchesCourse =
            selectedCourse.value === 'all' ||
            courseCode === selectedCourse.value;

        const searchValue = searchQuery.value.toLowerCase().trim();

        const matchesSearch =
            searchValue === '' ||
            request.user.name.toLowerCase().includes(searchValue) ||
            request.user.student_id.toLowerCase().includes(searchValue) ||
            courseCode.toLowerCase().includes(searchValue);

        return matchesStatus && matchesCourse && matchesSearch;
    });
});

const pendingCount = computed(() => {
    return props.clearanceRequests.filter((request) => {
        return request.status === 'pending';
    }).length;
});

const clearedCount = computed(() => {
    return props.clearanceRequests.filter((request) => {
        return request.status === 'cleared';
    }).length;
});

const needsAttentionCount = computed(() => {
    return props.clearanceRequests.filter((request) => {
        return hasRejectedApproval(request);
    }).length;
});

const readyForFinalApprovalCount = computed(() => {
    return props.clearanceRequests.filter((request) => {
        return isReadyForFinalApproval(request);
    }).length;
});

const statusLabel = (status: string) => {
    if (status === 'approved') return 'Approved';
    if (status === 'rejected') return 'Rejected';
    if (status === 'cleared') return 'Cleared';

    return 'Pending';
};

const statusBadgeClass = (status: string) => {
    if (status === 'approved' || status === 'cleared') {
        return 'bg-green-100 text-green-700';
    }

    if (status === 'rejected') {
        return 'bg-red-100 text-red-700';
    }

    return 'bg-orange-100 text-orange-700';
};

const filterButtonClass = (filter: StatusFilter) => {
    if (activeFilter.value === filter) {
        return 'bg-blue-600 text-white';
    }

    return 'bg-white text-slate-700 hover:bg-slate-100';
};

const officeTypeLabel = (office: Office) => {
    if (office.is_final_approver) {
        return 'Final Approver';
    }

    return 'Regular Office';
};

const formatDateTime = (value: string | null) => {
    if (!value) {
        return 'N/A';
    }

    const normalizedValue = value.replace(/\.\d+Z$/, 'Z');
    const date = new Date(normalizedValue);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    }).format(date);
};
</script>

<template>
    <Head title="Clearance Monitoring" />

    <div class="min-h-screen bg-slate-50 p-4 text-slate-900">
        <div class="mx-auto flex max-w-7xl flex-col gap-6">
            <section class="rounded-2xl border bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-blue-700">
                    Admin / OSAS Director Panel
                </p>

                <div
                    class="mt-2 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <h1 class="text-3xl font-bold text-blue-950">
                            Clearance Monitoring
                        </h1>

                        <p class="mt-2 text-slate-600">
                            Monitor all student clearance requests, progress,
                            and final cleared status.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <Link
                            href="/admin/users"
                            class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                        >
                            Manage Users
                        </Link>

                        <Link
                            href="/admin/dashboard"
                            class="rounded-xl bg-slate-600 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
                        >
                            Back to Dashboard
                        </Link>
                    </div>
                </div>

                <div
                    class="mt-4 rounded-xl bg-blue-50 p-4 text-sm text-blue-900"
                >
                    This page gives the admin an overview of pending and cleared
                    student clearance requests. Click View to inspect the full
                    office approval breakdown.
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-5">
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-blue-700">Total Requests</p>

                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ clearanceRequests.length }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-orange-600">
                        Pending Requests
                    </p>

                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ pendingCount }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-green-700">Cleared Requests</p>

                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ clearedCount }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-red-600">Needs Attention</p>

                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ needsAttentionCount }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-purple-700">
                        Ready for Final Approval
                    </p>

                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ readyForFinalApprovalCount }}
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
                                Clearance Requests
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Showing {{ filteredRequests.length }} of
                                {{ clearanceRequests.length }} clearance
                                requests.
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 md:items-end">
                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="cursor-pointer rounded-xl px-4 py-2 text-sm font-semibold transition"
                                    :class="filterButtonClass('all')"
                                    @click="setFilter('all')"
                                >
                                    All
                                </button>

                                <button
                                    type="button"
                                    class="cursor-pointer rounded-xl px-4 py-2 text-sm font-semibold transition"
                                    :class="filterButtonClass('pending')"
                                    @click="setFilter('pending')"
                                >
                                    Pending
                                </button>

                                <button
                                    type="button"
                                    class="cursor-pointer rounded-xl px-4 py-2 text-sm font-semibold transition"
                                    :class="filterButtonClass('cleared')"
                                    @click="setFilter('cleared')"
                                >
                                    Cleared
                                </button>

                                <button
                                    type="button"
                                    class="cursor-pointer rounded-xl px-4 py-2 text-sm font-semibold transition"
                                    :class="
                                        filterButtonClass('needs_attention')
                                    "
                                    @click="setFilter('needs_attention')"
                                >
                                    Needs Attention
                                </button>

                                <button
                                    type="button"
                                    class="cursor-pointer rounded-xl px-4 py-2 text-sm font-semibold transition"
                                    :class="
                                        filterButtonClass(
                                            'ready_for_final_approval',
                                        )
                                    "
                                    @click="
                                        setFilter('ready_for_final_approval')
                                    "
                                >
                                    Ready for Final Approval
                                </button>
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    placeholder="Search name, ID, or course"
                                />

                                <select
                                    v-model="selectedCourse"
                                    class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                >
                                    <option value="all">All Courses</option>

                                    <option
                                        v-for="courseCode in availableCourses"
                                        :key="courseCode"
                                        :value="courseCode"
                                    >
                                        {{ courseCode }}
                                    </option>
                                </select>

                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                                    @click="clearFilters"
                                >
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="filteredRequests.length === 0"
                    class="p-8 text-center"
                >
                    <p class="font-medium text-slate-700">
                        No clearance requests found.
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Requests will appear here after students submit
                        clearance requests.
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

                                <th class="px-6 py-3 font-semibold">
                                    School Year
                                </th>

                                <th class="px-6 py-3 font-semibold">
                                    Office Progress
                                </th>

                                <th class="px-6 py-3 font-semibold">Status</th>

                                <th class="px-6 py-3 font-semibold">
                                    Cleared At
                                </th>

                                <th class="px-6 py-3 text-right font-semibold">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="request in filteredRequests"
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
                                    <div class="min-w-32">
                                        <div
                                            class="mb-1 flex justify-between text-xs text-slate-500"
                                        >
                                            <span>
                                                {{
                                                    regularApprovedCount(
                                                        request,
                                                    )
                                                }}
                                                /
                                                {{
                                                    regularApprovals(request)
                                                        .length
                                                }}
                                            </span>

                                            <span>
                                                {{
                                                    regularProgressPercentage(
                                                        request,
                                                    )
                                                }}%
                                            </span>
                                        </div>

                                        <div
                                            class="h-2 overflow-hidden rounded-full bg-slate-100"
                                        >
                                            <div
                                                class="h-full rounded-full bg-green-600"
                                                :style="{
                                                    width: `${regularProgressPercentage(
                                                        request,
                                                    )}%`,
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="w-fit rounded-full px-3 py-1 text-xs font-semibold"
                                            :class="
                                                displayStatusBadgeClass(request)
                                            "
                                        >
                                            {{ displayStatusLabel(request) }}
                                        </span>

                                        <span
                                            v-if="hasRejectedApproval(request)"
                                            class="w-fit rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700"
                                        >
                                            Needs Attention
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    {{ formatDateTime(request.cleared_at) }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <button
                                        type="button"
                                        class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                                        @click="openDetailsModal(request)"
                                    >
                                        View
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
        v-if="showDetailsModal && selectedRequest"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @click.self="closeDetailsModal"
    >
        <div
            class="max-h-[90vh] w-full max-w-6xl overflow-hidden rounded-2xl bg-white shadow-xl"
        >
            <div class="flex items-start justify-between gap-4 border-b p-6">
                <div>
                    <p class="text-sm font-medium text-blue-700">
                        Clearance Request Details
                    </p>

                    <h2 class="mt-1 text-2xl font-bold text-blue-950">
                        {{ selectedRequest.user.name }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Student ID: {{ selectedRequest.user.student_id }}
                        <span v-if="selectedRequest.user.course">
                            • {{ selectedRequest.user.course.code }}
                        </span>
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-lg px-3 py-1 text-xl font-bold text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                    @click="closeDetailsModal"
                >
                    ×
                </button>
            </div>

            <div class="max-h-[75vh] overflow-y-auto p-6">
                <section class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border bg-slate-50 p-5">
                        <p class="font-semibold text-blue-700">Request Info</p>

                        <p class="mt-2 text-sm text-slate-600">
                            Semester:
                            <span class="font-semibold text-blue-950">
                                {{ selectedRequest.semester }}
                            </span>
                        </p>

                        <p class="mt-2 text-sm text-slate-600">
                            School Year:
                            <span class="font-semibold text-blue-950">
                                {{ selectedRequest.school_year }}
                            </span>
                        </p>

                        <p class="mt-2 text-sm text-slate-600">
                            Cleared At:
                            <span class="font-semibold text-blue-950">
                                {{ formatDateTime(selectedRequest.cleared_at) }}
                            </span>
                        </p>
                    </div>

                    <div class="rounded-2xl border bg-slate-50 p-5">
                        <p class="font-semibold text-green-700">
                            Overall Status
                        </p>

                        <div class="mt-3">
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                :class="
                                    displayStatusBadgeClass(selectedRequest)
                                "
                            >
                                {{ displayStatusLabel(selectedRequest) }}
                            </span>
                        </div>

                        <p class="mt-4 text-sm text-slate-500">
                            {{ regularApprovedCount(selectedRequest) }}
                            /
                            {{ regularApprovals(selectedRequest).length }}
                            regular office approvals completed
                        </p>
                    </div>

                    <div class="rounded-2xl border bg-slate-50 p-5">
                        <p class="font-semibold text-purple-700">
                            Office Progress
                        </p>

                        <p class="mt-2 text-3xl font-bold text-blue-950">
                            {{ regularProgressPercentage(selectedRequest) }}%
                        </p>

                        <div
                            class="mt-3 h-3 overflow-hidden rounded-full bg-slate-200"
                        >
                            <div
                                class="h-full rounded-full bg-green-600"
                                :style="{
                                    width: `${regularProgressPercentage(
                                        selectedRequest,
                                    )}%`,
                                }"
                            ></div>
                        </div>
                    </div>
                </section>

                <section class="mt-6 rounded-2xl border bg-white shadow-sm">
                    <div class="border-b p-5">
                        <h3 class="text-lg font-bold text-blue-950">
                            Office Approval Breakdown
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Full office status, approver, timestamp, and
                            remarks.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-100 text-slate-600">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">
                                        Office
                                    </th>

                                    <th class="px-6 py-3 font-semibold">
                                        Type
                                    </th>

                                    <th class="px-6 py-3 font-semibold">
                                        Status
                                    </th>

                                    <th class="px-6 py-3 font-semibold">
                                        Approver
                                    </th>

                                    <th class="px-6 py-3 font-semibold">
                                        Acted At
                                    </th>

                                    <th class="px-6 py-3 font-semibold">
                                        Remarks
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="approval in selectedRequest.approvals"
                                    :key="approval.id"
                                    class="border-t"
                                >
                                    <td
                                        class="px-6 py-4 font-medium text-blue-950"
                                    >
                                        {{ approval.office.name }}
                                    </td>

                                    <td class="px-6 py-4 text-slate-700">
                                        {{ officeTypeLabel(approval.office) }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-semibold"
                                            :class="
                                                statusBadgeClass(
                                                    approval.status,
                                                )
                                            "
                                        >
                                            {{ statusLabel(approval.status) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-slate-700">
                                        {{
                                            approval.approver
                                                ? approval.approver.name
                                                : 'N/A'
                                        }}
                                    </td>

                                    <td class="px-6 py-4 text-slate-700">
                                        {{ formatDateTime(approval.acted_at) }}
                                    </td>

                                    <td
                                        class="max-w-xs px-6 py-4 text-slate-700"
                                    >
                                        {{ approval.remarks ?? '—' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        class="rounded-xl bg-slate-600 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
                        @click="closeDetailsModal"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>