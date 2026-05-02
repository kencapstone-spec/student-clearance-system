<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    BarChart3,
    CheckCircle2,
    ClipboardCheck,
    Clock3,
    Eye,
    Filter,
    LayoutDashboard,
    RotateCcw,
    Search,
    ShieldCheck,
    UserRoundCog,
    X,
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
        return 'border-green-200 bg-green-50 text-green-700';
    }

    if (isReadyForFinalApproval(request)) {
        return 'border-purple-200 bg-purple-50 text-purple-700';
    }

    return 'border-orange-200 bg-orange-50 text-orange-700';
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
    if (status === 'approved') {
        return 'Approved';
    }

    if (status === 'rejected') {
        return 'Rejected';
    }

    if (status === 'cleared') {
        return 'Cleared';
    }

    return 'Pending';
};

const statusBadgeClass = (status: string) => {
    if (status === 'approved' || status === 'cleared') {
        return 'border-green-200 bg-green-50 text-green-700';
    }

    if (status === 'rejected') {
        return 'border-red-200 bg-red-50 text-red-700';
    }

    return 'border-orange-200 bg-orange-50 text-orange-700';
};

const filterButtonClass = (filter: StatusFilter) => {
    if (activeFilter.value === filter) {
        return 'border-blue-700 bg-blue-700 text-white shadow-lg shadow-blue-700/20';
    }

    return 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700';
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
                            Admin / OSAS Director Panel
                        </div>

                        <div
                            class="mt-5 flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between"
                        >
                            <div>
                                <h1
                                    class="text-4xl font-black tracking-tight text-blue-950"
                                >
                                    Clearance Monitoring
                                </h1>

                                <p
                                    class="mt-3 max-w-3xl text-base leading-7 text-slate-600"
                                >
                                    Monitor student clearance requests, office
                                    approval progress, final approval readiness,
                                    and cleared status from one admin view.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <Link
                                    href="/admin/users"
                                    class="inline-flex items-center gap-2 rounded-2xl bg-blue-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-700/20 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-xl"
                                >
                                    <UserRoundCog class="size-4" />
                                    Manage Users
                                </Link>

                                <Link
                                    href="/admin/dashboard"
                                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-md"
                                >
                                    <LayoutDashboard class="size-4" />
                                    Back to Dashboard
                                </Link>
                            </div>
                        </div>

                        <div
                            class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/80 p-4 text-sm font-medium leading-6 text-blue-900"
                        >
                            Click View to inspect the full office approval
                            breakdown, approver, timestamp, remarks, and final
                            clearance readiness for each student request.
                        </div>
                    </div>

                    <div class="hidden items-center justify-center lg:flex">
                        <div
                            class="relative grid h-52 w-52 place-items-center rounded-4xl border border-blue-100 bg-linear-to-br from-white to-blue-50 shadow-2xl shadow-slate-300/70"
                        >
                            <div
                                class="grid h-20 w-20 place-items-center rounded-3xl bg-blue-700 text-white shadow-xl shadow-blue-700/25"
                            >
                                <BarChart3 class="size-10" />
                            </div>

                            <p
                                class="text-center text-sm font-black uppercase tracking-[0.18em] text-blue-700"
                            >
                                Monitoring Hub
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Summary Cards -->
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div
                    class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="flex items-center gap-4 xl:block">
                        <div
                            class="grid h-14 w-14 place-items-center rounded-2xl bg-blue-50 text-blue-700 shadow-sm xl:mb-4"
                        >
                            <ClipboardCheck class="size-7" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-black uppercase tracking-wide text-blue-700"
                            >
                                Total Requests
                            </p>

                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ clearanceRequests.length }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="flex items-center gap-4 xl:block">
                        <div
                            class="grid h-14 w-14 place-items-center rounded-2xl bg-orange-50 text-orange-600 shadow-sm xl:mb-4"
                        >
                            <Clock3 class="size-7" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-black uppercase tracking-wide text-orange-600"
                            >
                                Pending
                            </p>

                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ pendingCount }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="flex items-center gap-4 xl:block">
                        <div
                            class="grid h-14 w-14 place-items-center rounded-2xl bg-green-50 text-green-700 shadow-sm xl:mb-4"
                        >
                            <CheckCircle2 class="size-7" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-black uppercase tracking-wide text-green-700"
                            >
                                Cleared
                            </p>

                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ clearedCount }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="flex items-center gap-4 xl:block">
                        <div
                            class="grid h-14 w-14 place-items-center rounded-2xl bg-red-50 text-red-600 shadow-sm xl:mb-4"
                        >
                            <AlertTriangle class="size-7" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-black uppercase tracking-wide text-red-600"
                            >
                                Needs Attention
                            </p>

                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ needsAttentionCount }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:col-span-2 xl:col-span-1"
                >
                    <div class="flex items-center gap-4 xl:block">
                        <div
                            class="grid h-14 w-14 place-items-center rounded-2xl bg-purple-50 text-purple-700 shadow-sm xl:mb-4"
                        >
                            <ShieldCheck class="size-7" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-black uppercase tracking-wide text-purple-700"
                            >
                                Ready for Final
                            </p>

                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ readyForFinalApprovalCount }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Records -->
            <section
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-sm shadow-slate-200/70"
            >
                <div
                    class="flex flex-col gap-5 border-b border-slate-200 bg-white px-6 py-5 xl:flex-row xl:items-start xl:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-black uppercase tracking-[0.18em] text-slate-400"
                        >
                            Clearance Request Registry
                        </p>

                        <h2 class="mt-1 text-xl font-black text-blue-950">
                            Clearance Requests
                        </h2>

                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Showing {{ filteredRequests.length }} of
                            {{ clearanceRequests.length }} clearance requests.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 xl:items-end">
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                                :class="filterButtonClass('all')"
                                @click="setFilter('all')"
                            >
                                <Filter class="size-4" />
                                All
                            </button>

                            <button
                                type="button"
                                class="inline-flex cursor-pointer items-center rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                                :class="filterButtonClass('pending')"
                                @click="setFilter('pending')"
                            >
                                Pending
                            </button>

                            <button
                                type="button"
                                class="inline-flex cursor-pointer items-center rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                                :class="filterButtonClass('cleared')"
                                @click="setFilter('cleared')"
                            >
                                Cleared
                            </button>

                            <button
                                type="button"
                                class="inline-flex cursor-pointer items-center rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                                :class="filterButtonClass('needs_attention')"
                                @click="setFilter('needs_attention')"
                            >
                                Needs Attention
                            </button>

                            <button
                                type="button"
                                class="inline-flex cursor-pointer items-center rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                                :class="
                                    filterButtonClass(
                                        'ready_for_final_approval',
                                    )
                                "
                                @click="setFilter('ready_for_final_approval')"
                            >
                                Ready for Final Approval
                            </button>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row">
                            <div class="relative">
                                <Search
                                    class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    class="w-full rounded-2xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm font-medium text-slate-900 shadow-sm outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 sm:w-64"
                                    placeholder="Search name, ID, or course"
                                />
                            </div>

                            <select
                                v-model="selectedCourse"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
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
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                @click="clearFilters"
                            >
                                <RotateCcw class="size-4" />
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    v-if="filteredRequests.length === 0"
                    class="p-12 text-center"
                >
                    <div
                        class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-blue-50 text-blue-700"
                    >
                        <ClipboardCheck class="size-8" />
                    </div>

                    <p class="mt-4 font-black text-slate-700">
                        No clearance requests found.
                    </p>

                    <p class="mt-1 text-sm font-medium text-slate-500">
                        Adjust your filters or wait for students to submit
                        clearance requests.
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
                                    Office Progress
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                >
                                    Cleared At
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
                                v-for="request in filteredRequests"
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
                                            Request #{{ request.id }}
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
                                    <div class="min-w-36">
                                        <div
                                            class="mb-1 flex justify-between text-xs font-black text-slate-500"
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
                                            class="h-2.5 overflow-hidden rounded-full bg-slate-100"
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
                                            class="w-fit rounded-full border px-3 py-1 text-xs font-black"
                                            :class="
                                                displayStatusBadgeClass(request)
                                            "
                                        >
                                            {{ displayStatusLabel(request) }}
                                        </span>

                                        <span
                                            v-if="hasRejectedApproval(request)"
                                            class="w-fit rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-black text-red-700"
                                        >
                                            Needs Attention
                                        </span>
                                    </div>
                                </td>

                                <td
                                    class="px-6 py-4 font-semibold text-slate-700"
                                >
                                    {{ formatDateTime(request.cleared_at) }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-blue-700 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-blue-700/20 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-lg"
                                        @click="openDetailsModal(request)"
                                    >
                                        <Eye class="size-4" />
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

    <!-- Details Modal -->
    <div
        v-if="showDetailsModal && selectedRequest"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
        @click.self="closeDetailsModal"
    >
        <div
            class="max-h-[90vh] w-full max-w-6xl overflow-hidden rounded-4xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20"
        >
            <div
                class="flex items-start justify-between gap-4 border-b border-slate-200 bg-white p-6"
            >
                <div>
                    <p
                        class="text-xs font-black uppercase tracking-[0.18em] text-blue-700"
                    >
                        Clearance Request Details
                    </p>

                    <h2 class="mt-1 text-2xl font-black text-blue-950">
                        {{ selectedRequest.user.name }}
                    </h2>

                    <p class="mt-1 text-sm font-medium text-slate-500">
                        Student ID: {{ selectedRequest.user.student_id }}
                        <span v-if="selectedRequest.user.course">
                            • {{ selectedRequest.user.course.code }}
                        </span>
                    </p>
                </div>

                <button
                    type="button"
                    class="grid size-10 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                    @click="closeDetailsModal"
                >
                    <X class="size-5" />
                </button>
            </div>

            <div class="max-h-[75vh] overflow-y-auto p-6">
                <section class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="font-black text-blue-700">Request Info</p>

                        <p class="mt-3 text-sm text-slate-600">
                            Semester:
                            <span class="font-black text-blue-950">
                                {{ selectedRequest.semester }}
                            </span>
                        </p>

                        <p class="mt-2 text-sm text-slate-600">
                            School Year:
                            <span class="font-black text-blue-950">
                                {{ selectedRequest.school_year }}
                            </span>
                        </p>

                        <p class="mt-2 text-sm text-slate-600">
                            Cleared At:
                            <span class="font-black text-blue-950">
                                {{ formatDateTime(selectedRequest.cleared_at) }}
                            </span>
                        </p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="font-black text-green-700">
                            Overall Status
                        </p>

                        <div class="mt-3">
                            <span
                                class="rounded-full border px-3 py-1 text-xs font-black"
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

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="font-black text-purple-700">
                            Office Progress
                        </p>

                        <p class="mt-2 text-3xl font-black text-blue-950">
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

                <section
                    class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/70"
                >
                    <div class="border-b border-slate-200 p-5">
                        <h3 class="text-lg font-black text-blue-950">
                            Office Approval Breakdown
                        </h3>

                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Full office status, approver, timestamp, and
                            remarks.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                    >
                                        Office
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                    >
                                        Type
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                    >
                                        Status
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                    >
                                        Approver
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                    >
                                        Acted At
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                    >
                                        Remarks
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="approval in selectedRequest.approvals"
                                    :key="approval.id"
                                    class="transition hover:bg-blue-50/50"
                                >
                                    <td
                                        class="px-6 py-4 font-black text-blue-950"
                                    >
                                        {{ approval.office.name }}
                                    </td>

                                    <td
                                        class="px-6 py-4 font-semibold text-slate-700"
                                    >
                                        {{ officeTypeLabel(approval.office) }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full border px-3 py-1 text-xs font-black"
                                            :class="
                                                statusBadgeClass(
                                                    approval.status,
                                                )
                                            "
                                        >
                                            {{ statusLabel(approval.status) }}
                                        </span>
                                    </td>

                                    <td
                                        class="px-6 py-4 font-semibold text-slate-700"
                                    >
                                        {{
                                            approval.approver
                                                ? approval.approver.name
                                                : 'N/A'
                                        }}
                                    </td>

                                    <td
                                        class="px-6 py-4 font-semibold text-slate-700"
                                    >
                                        {{ formatDateTime(approval.acted_at) }}
                                    </td>

                                    <td
                                        class="max-w-xs px-6 py-4 font-medium text-slate-700"
                                    >
                                        {{ approval.remarks ?? '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        class="rounded-2xl bg-slate-700 px-5 py-3 text-sm font-black text-white shadow-md shadow-slate-700/20 transition hover:bg-slate-800"
                        @click="closeDetailsModal"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>