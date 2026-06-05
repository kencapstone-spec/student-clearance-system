<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    BarChart3,
    CheckCircle2,
    ClipboardCheck,
    Download,
    FileText,
    LayoutDashboard,
    Printer,
    RotateCcw,
    Search,
    ShieldCheck,
    UserRoundCog,
    X,
} from 'lucide-vue-next';
import { reactive, ref } from 'vue';

type Course = {
    id: number;
    code: string;
    name: string;
};

type Filters = {
    status: string;
    course_id: string | number | null;
    semester: string | null;
    school_year: string | null;
};

type Summary = {
    totalRequests: number;
    clearedRequests: number;
    pendingRequests: number;
    needsAttentionRequests: number;
};

type ReportRequest = {
    id: number;
    student_name: string;
    student_id: string;
    course_code: string;
    semester: string;
    school_year: string;
    status: string;
    cleared_at: string | null;
    approved_regular_approvals: number;
    total_regular_approvals: number;
    has_rejected_approval: boolean;
};

const props = defineProps<{
    courses: Course[];
    filters: Filters;
    summary: Summary;
    requests: ReportRequest[];
}>();

const filterForm = reactive({
    status: props.filters.status ?? 'all',
    course_id: props.filters.course_id ?? '',
    semester: props.filters.semester ?? '',
    school_year: props.filters.school_year ?? '',
});

const showAdminMobileMoreMenu = ref(false);

const applyFilters = () => {
    router.get(
        '/admin/reports',
        {
            status: filterForm.status,
            course_id: filterForm.course_id,
            semester: filterForm.semester,
            school_year: filterForm.school_year,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const clearFilters = () => {
    router.get('/admin/reports');
};

const toggleAdminMobileMoreMenu = () => {
    showAdminMobileMoreMenu.value = !showAdminMobileMoreMenu.value;
};

const closeAdminMobileMoreMenu = () => {
    showAdminMobileMoreMenu.value = false;
};

const statusLabel = (request: ReportRequest) => {
    if (request.has_rejected_approval) {
        return 'Needs Attention';
    }

    if (request.status === 'cleared') {
        return 'Cleared';
    }

    return 'Pending';
};

const statusClass = (request: ReportRequest) => {
    if (request.has_rejected_approval) {
        return 'border-red-200 bg-red-50 text-red-700';
    }

    if (request.status === 'cleared') {
        return 'border-green-200 bg-green-50 text-green-700';
    }

    return 'border-orange-200 bg-orange-50 text-orange-700';
};
</script>

<template>
    <Head title="Admin Reports" />

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
                            Admin / OSAS Director Panel
                        </div>

                        <div
                            class="mt-5 flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between"
                        >
                            <div>
                                <h1
                                    class="text-3xl font-black tracking-tight text-blue-950 sm:text-4xl"
                                >
                                    Reports
                                </h1>

                                <p
                                    class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base sm:leading-7"
                                >
                                    Filter clearance request records, review
                                    summary counts, export CSV data, and prepare
                                    printable reports for monitoring and
                                    documentation.
                                </p>
                            </div>

                            <Link
                                href="/admin/dashboard"
                                class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black whitespace-nowrap text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-md sm:w-auto"
                            >
                                <LayoutDashboard class="size-4" />
                                Back to Dashboard
                            </Link>
                        </div>

                        <div
                            class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/80 p-4 text-sm leading-6 font-medium text-blue-900"
                        >
                            Use the filters below to narrow the report by
                            status, course, semester, and school year. Exported
                            and printable reports follow the selected filter
                            values.
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
                                class="text-center text-sm font-black tracking-[0.18em] text-blue-700 uppercase"
                            >
                                Reporting Center
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Summary Cards -->
            <section
                class="grid grid-cols-2 gap-3 md:grid-cols-2 md:gap-4 xl:grid-cols-4"
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

                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-blue-700 uppercase sm:text-sm"
                            >
                                Total Requests
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ summary.totalRequests }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                All submitted records
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

                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-green-700 uppercase sm:text-sm"
                            >
                                Cleared Requests
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ summary.clearedRequests }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Fully completed
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:rounded-3xl md:p-6"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600 shadow-sm md:h-14 md:w-14"
                        >
                            <FileText class="size-6 md:size-7" />
                        </div>

                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-orange-600 uppercase sm:text-sm"
                            >
                                Pending Requests
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ summary.pendingRequests }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Still in progress
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:rounded-3xl md:p-6"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-red-50 text-red-600 shadow-sm md:h-14 md:w-14"
                        >
                            <AlertTriangle class="size-6 md:size-7" />
                        </div>

                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-red-600 uppercase sm:text-sm"
                            >
                                Needs Attention
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ summary.needsAttentionRequests }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Rejected or flagged
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Filters -->
            <section
                class="rounded-3xl border border-slate-200 bg-white/95 p-4 shadow-sm shadow-slate-200/70 sm:p-6"
            >
                <div
                    class="flex flex-col gap-4 border-b border-slate-100 pb-5 xl:flex-row xl:items-start xl:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-black tracking-[0.18em] text-slate-400 uppercase"
                        >
                            Report Controls
                        </p>

                        <h2 class="mt-1 text-xl font-black text-blue-950">
                            Report Filters
                        </h2>

                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Filter the report by status, course, semester, and
                            school year before exporting or printing.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <a
                            :href="`/admin/reports/export-csv?status=${filterForm.status}&course_id=${filterForm.course_id}&semester=${filterForm.semester}&school_year=${filterForm.school_year}`"
                            class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl bg-green-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-green-700/20 transition hover:-translate-y-0.5 hover:bg-green-800 hover:shadow-xl"
                        >
                            <Download class="size-4" />
                            Export CSV
                        </a>

                        <a
                            :href="`/admin/reports/print?status=${filterForm.status}&course_id=${filterForm.course_id}&semester=${filterForm.semester}&school_year=${filterForm.school_year}`"
                            class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl bg-slate-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-slate-700/20 transition hover:-translate-y-0.5 hover:bg-slate-800 hover:shadow-xl"
                        >
                            <Printer class="size-4" />
                            Print Report
                        </a>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label
                            class="mb-2 block text-sm font-black text-blue-950"
                        >
                            Status
                        </label>

                        <select
                            v-model="filterForm.status"
                            class="min-h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                        >
                            <option value="all">All Statuses</option>
                            <option value="cleared">Cleared</option>
                            <option value="pending">Pending</option>
                            <option value="needs_attention">
                                Needs Attention
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-black text-blue-950"
                        >
                            Course
                        </label>

                        <select
                            v-model="filterForm.course_id"
                            class="min-h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                        >
                            <option value="">All Courses</option>

                            <option
                                v-for="course in courses"
                                :key="course.id"
                                :value="course.id"
                            >
                                {{ course.code }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-black text-blue-950"
                        >
                            Semester
                        </label>

                        <select
                            v-model="filterForm.semester"
                            class="min-h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                        >
                            <option value="">All Semesters</option>
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-black text-blue-950"
                        >
                            School Year
                        </label>

                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model="filterForm.school_year"
                                type="text"
                                placeholder="Example: 2026-2027"
                                class="min-h-11 w-full rounded-2xl border border-slate-200 bg-white py-3 pr-4 pl-10 text-sm font-black text-slate-700 shadow-sm outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                            />
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:flex sm:flex-wrap">
                    <button
                        type="button"
                        class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl bg-blue-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-700/20 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-xl sm:w-auto"
                        @click="applyFilters"
                    >
                        <Search class="size-4" />
                        Apply Filters
                    </button>

                    <button
                        type="button"
                        class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 sm:w-auto"
                        @click="clearFilters"
                    >
                        <RotateCcw class="size-4" />
                        Clear Filters
                    </button>
                </div>
            </section>

            <!-- Results -->
            <section
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-sm shadow-slate-200/70"
            >
                <div
                    class="border-b border-slate-200 bg-white px-4 py-5 sm:px-6"
                >
                    <p
                        class="text-xs font-black tracking-[0.18em] text-slate-400 uppercase"
                    >
                        Report Output
                    </p>

                    <h2 class="mt-1 text-xl font-black text-blue-950">
                        Report Results
                    </h2>

                    <p class="mt-1 text-sm font-medium text-slate-500">
                        Showing up to the latest 100 clearance requests based on
                        the selected filters.
                    </p>
                </div>

                <div
                    v-if="requests.length === 0"
                    class="p-8 text-center sm:p-12"
                >
                    <div
                        class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-blue-50 text-blue-700"
                    >
                        <FileText class="size-8" />
                    </div>

                    <p class="mt-4 font-black text-slate-700">
                        No clearance requests found.
                    </p>

                    <p class="mt-1 text-sm font-medium text-slate-500">
                        Try changing or clearing the selected filters.
                    </p>
                </div>

                <div v-else>
                    <!-- Mobile Card List -->
                    <div class="grid gap-3 p-4 lg:hidden">
                        <article
                            v-for="request in requests"
                            :key="request.id"
                            class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3
                                        class="truncate text-base font-black text-blue-950"
                                    >
                                        {{ request.student_name }}
                                    </h3>

                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-600"
                                    >
                                        {{ request.student_id }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs font-medium text-slate-500"
                                    >
                                        Request #{{ request.id }}
                                    </p>
                                </div>

                                <span
                                    class="shrink-0 rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700"
                                >
                                    {{ request.course_code }}
                                </span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span
                                    class="rounded-full border px-3 py-1 text-xs font-black"
                                    :class="statusClass(request)"
                                >
                                    {{ statusLabel(request) }}
                                </span>
                            </div>

                            <div
                                class="mt-4 grid grid-cols-2 gap-2 text-sm font-semibold text-slate-700"
                            >
                                <div
                                    class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2"
                                >
                                    <p class="text-xs text-slate-500">
                                        Semester
                                    </p>

                                    <p class="mt-1 font-black text-blue-950">
                                        {{ request.semester }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2"
                                >
                                    <p class="text-xs text-slate-500">
                                        School Year
                                    </p>

                                    <p class="mt-1 font-black text-blue-950">
                                        {{ request.school_year }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="mt-3 grid grid-cols-2 gap-2 text-sm font-semibold text-slate-700"
                            >
                                <div
                                    class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2"
                                >
                                    <p class="text-xs text-slate-500">
                                        Progress
                                    </p>

                                    <p class="mt-1 font-black text-blue-950">
                                        {{ request.approved_regular_approvals }}
                                        /
                                        {{ request.total_regular_approvals }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2"
                                >
                                    <p class="text-xs text-slate-500">
                                        Cleared At
                                    </p>

                                    <p
                                        class="mt-1 text-xs font-black text-blue-950"
                                    >
                                        {{
                                            request.cleared_at ??
                                            'Not cleared yet'
                                        }}
                                    </p>
                                </div>
                            </div>
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
                                        Progress
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        Status
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        Cleared At
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="request in requests"
                                    :key="request.id"
                                    class="transition hover:bg-blue-50/50"
                                >
                                    <td class="px-6 py-4">
                                        <p class="font-black text-blue-950">
                                            {{ request.student_name }}
                                        </p>
                                    </td>

                                    <td
                                        class="px-6 py-4 font-semibold text-slate-700"
                                    >
                                        {{ request.student_id }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700"
                                        >
                                            {{ request.course_code }}
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

                                    <td
                                        class="px-6 py-4 font-semibold text-slate-700"
                                    >
                                        {{ request.approved_regular_approvals }}
                                        /
                                        {{ request.total_regular_approvals }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full border px-3 py-1 text-xs font-black"
                                            :class="statusClass(request)"
                                        >
                                            {{ statusLabel(request) }}
                                        </span>
                                    </td>

                                    <td
                                        class="px-6 py-4 font-semibold text-slate-700"
                                    >
                                        {{
                                            request.cleared_at ??
                                            'Not cleared yet'
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Admin Mobile More Sheet -->
    <div
        v-if="showAdminMobileMoreMenu"
        class="fixed inset-x-3 bottom-24 z-40 rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl shadow-slate-900/20 md:hidden"
    >
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-black text-blue-950">Admin Tools</p>

                <p class="mt-1 text-xs font-semibold text-slate-500">
                    Reports and course module shortcuts.
                </p>
            </div>

            <button
                type="button"
                class="grid size-10 place-items-center rounded-xl border border-slate-200 text-slate-500"
                @click="closeAdminMobileMoreMenu"
            >
                <X class="size-5" />
            </button>
        </div>

        <div class="mt-4 grid gap-2">
            <Link
                href="/admin/reports"
                class="flex min-h-12 items-center justify-between rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-black text-blue-700"
            >
                <span>Reports</span>
                <span>Current</span>
            </Link>

            <Link
                href="/admin/course-modules"
                class="flex min-h-12 items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700"
            >
                <span>Course Modules</span>
                <span>→</span>
            </Link>
        </div>
    </div>

    <!-- Admin Mobile Thumb Navigation -->
    <nav
        class="fixed inset-x-3 bottom-3 z-30 rounded-2xl border border-blue-200 bg-blue-950/95 p-2 shadow-2xl shadow-blue-950/25 backdrop-blur md:hidden"
    >
        <div class="grid grid-cols-4 gap-1">
            <Link
                href="/admin/dashboard"
                class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-[0.65rem] font-black text-white transition hover:bg-white/10"
            >
                <LayoutDashboard class="size-4" />
                <span>Home</span>
            </Link>

            <Link
                href="/admin/users"
                class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-[0.65rem] font-black text-white transition hover:bg-white/10"
            >
                <UserRoundCog class="size-4" />
                <span>Users</span>
            </Link>

            <Link
                href="/admin/clearance-requests"
                class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-[0.65rem] font-black text-white transition hover:bg-white/10"
            >
                <ShieldCheck class="size-4" />
                <span>Requests</span>
            </Link>

            <button
                type="button"
                class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-[0.65rem] font-black text-white transition hover:bg-white/10"
                :class="
                    showAdminMobileMoreMenu
                        ? 'bg-white/15 ring-1 ring-blue-200/40'
                        : ''
                "
                @click="toggleAdminMobileMoreMenu"
            >
                <span class="text-base leading-none">•••</span>
                <span>More</span>
            </button>
        </div>
    </nav>
</template>
