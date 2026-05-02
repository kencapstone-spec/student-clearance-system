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
} from 'lucide-vue-next';
import { reactive } from 'vue';

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
                                    Reports
                                </h1>

                                <p
                                    class="mt-3 max-w-3xl text-base leading-7 text-slate-600"
                                >
                                    Filter clearance request records, review
                                    summary counts, export CSV data, and prepare
                                    printable reports for monitoring and
                                    documentation.
                                </p>
                            </div>

                            <Link
                                href="/admin/dashboard"
                                class="inline-flex w-fit items-center gap-2 whitespace-nowrap rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-md"
                            >
                                <LayoutDashboard class="size-4" />
                                Back to Dashboard
                            </Link>
                        </div>

                        <div
                            class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/80 p-4 text-sm font-medium leading-6 text-blue-900"
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
                                class="text-center text-sm font-black uppercase tracking-[0.18em] text-blue-700"
                            >
                                Reporting Center
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Summary Cards -->
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
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
                                Total Requests
                            </p>

                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ summary.totalRequests }}
                            </p>

                            <p class="text-sm font-medium text-slate-500">
                                All submitted records
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
                                Cleared Requests
                            </p>

                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ summary.clearedRequests }}
                            </p>

                            <p class="text-sm font-medium text-slate-500">
                                Fully completed
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="grid h-14 w-14 place-items-center rounded-2xl bg-orange-50 text-orange-600 shadow-sm"
                        >
                            <FileText class="size-7" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-black uppercase tracking-wide text-orange-600"
                            >
                                Pending Requests
                            </p>

                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ summary.pendingRequests }}
                            </p>

                            <p class="text-sm font-medium text-slate-500">
                                Still in progress
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="grid h-14 w-14 place-items-center rounded-2xl bg-red-50 text-red-600 shadow-sm"
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
                                {{ summary.needsAttentionRequests }}
                            </p>

                            <p class="text-sm font-medium text-slate-500">
                                Rejected or flagged
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Filters -->
            <section
                class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70"
            >
                <div
                    class="flex flex-col gap-4 border-b border-slate-100 pb-5 xl:flex-row xl:items-start xl:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-black uppercase tracking-[0.18em] text-slate-400"
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

                    <div class="flex flex-wrap gap-3">
                        <a
                            :href="`/admin/reports/export-csv?status=${filterForm.status}&course_id=${filterForm.course_id}&semester=${filterForm.semester}&school_year=${filterForm.school_year}`"
                            class="inline-flex items-center gap-2 rounded-2xl bg-green-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-green-700/20 transition hover:-translate-y-0.5 hover:bg-green-800 hover:shadow-xl"
                        >
                            <Download class="size-4" />
                            Export CSV
                        </a>

                        <a
                            :href="`/admin/reports/print?status=${filterForm.status}&course_id=${filterForm.course_id}&semester=${filterForm.semester}&school_year=${filterForm.school_year}`"
                            class="inline-flex items-center gap-2 rounded-2xl bg-slate-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-slate-700/20 transition hover:-translate-y-0.5 hover:bg-slate-800 hover:shadow-xl"
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
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
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
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
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
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
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
                                class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model="filterForm.school_year"
                                type="text"
                                placeholder="Example: 2026-2027"
                                class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm font-black text-slate-700 shadow-sm outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                            />
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-2xl bg-blue-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-700/20 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-xl"
                        @click="applyFilters"
                    >
                        <Search class="size-4" />
                        Apply Filters
                    </button>

                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
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
                <div class="border-b border-slate-200 bg-white px-6 py-5">
                    <p
                        class="text-xs font-black uppercase tracking-[0.18em] text-slate-400"
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

                <div v-if="requests.length === 0" class="p-12 text-center">
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
                                    Progress
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
                                    {{ request.approved_regular_approvals }} /
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
                                        request.cleared_at ?? 'Not cleared yet'
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</template>