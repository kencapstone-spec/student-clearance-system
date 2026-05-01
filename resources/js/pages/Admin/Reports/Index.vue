<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
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
        return 'bg-red-100 text-red-700';
    }

    if (request.status === 'cleared') {
        return 'bg-green-100 text-green-700';
    }

    return 'bg-blue-100 text-blue-700';
};
</script>

<template>
    <Head title="Admin Reports" />

    <div class="min-h-screen bg-slate-50 p-4 text-slate-900">
        <div class="mx-auto flex max-w-7xl flex-col gap-6">
            <section class="rounded-2xl border bg-white p-6 shadow-sm">
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                >
                    <div>
                        <p class="text-sm font-medium text-blue-700">
                            Admin / OSAS Director Panel
                        </p>

                        <h1 class="mt-2 text-3xl font-bold text-blue-950">
                            Reports
                        </h1>

                        <p class="mt-2 text-slate-600">
                            View clearance request summaries and prepare report
                            data for monitoring.
                        </p>
                    </div>

                    <Link
                        href="/admin/dashboard"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Back to Dashboard
                    </Link>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-4">
                    <div class="rounded-2xl border bg-white p-6 shadow-sm">
                        <p class="font-semibold text-blue-700">
                            Total Requests
                        </p>
                        <p class="mt-2 text-4xl font-bold text-blue-950">
                            {{ summary.totalRequests }}
                        </p>
                    </div>

                    <div class="rounded-2xl border bg-white p-6 shadow-sm">
                        <p class="font-semibold text-green-700">
                            Cleared Requests
                        </p>
                        <p class="mt-2 text-4xl font-bold text-blue-950">
                            {{ summary.clearedRequests }}
                        </p>
                    </div>

                    <div class="rounded-2xl border bg-white p-6 shadow-sm">
                        <p class="font-semibold text-orange-600">
                            Pending Requests
                        </p>
                        <p class="mt-2 text-4xl font-bold text-blue-950">
                            {{ summary.pendingRequests }}
                        </p>
                    </div>

                    <div class="rounded-2xl border bg-white p-6 shadow-sm">
                        <p class="font-semibold text-red-600">
                            Needs Attention
                        </p>
                        <p class="mt-2 text-4xl font-bold text-blue-950">
                            {{ summary.needsAttentionRequests }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border bg-white p-6 shadow-sm">
                <div>
                    <h2 class="text-xl font-bold text-blue-950">
                        Report Filters
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Filter the report by status, course, semester, and
                        school year.
                    </p>
                </div>

                <div class="mt-4 grid gap-4 md:grid-cols-4">
                    <div>
                        <label
                            class="mb-1 block text-sm font-semibold text-blue-950"
                        >
                            Status
                        </label>

                        <select
                            v-model="filterForm.status"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-blue-950 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 focus:outline-none"
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
                            class="mb-1 block text-sm font-semibold text-blue-950"
                        >
                            Course
                        </label>

                        <select
                            v-model="filterForm.course_id"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-blue-950 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 focus:outline-none"
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
                            class="mb-1 block text-sm font-semibold text-blue-950"
                        >
                            Semester
                        </label>

                        <select
                            v-model="filterForm.semester"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-blue-950 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 focus:outline-none"
                        >
                            <option value="">All Semesters</option>
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-semibold text-blue-950"
                        >
                            School Year
                        </label>

                        <input
                            v-model="filterForm.school_year"
                            type="text"
                            placeholder="Example: 2026-2027"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-blue-950 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 focus:outline-none"
                        />
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-3">
                    <button
                        type="button"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                        @click="applyFilters"
                    >
                        Apply Filters
                    </button>

                    <button
                        type="button"
                        class="rounded-xl border bg-white px-4 py-2 text-sm font-semibold text-blue-950 hover:bg-slate-100"
                        @click="clearFilters"
                    >
                        Clear Filters
                    </button>

                    <a
                        :href="`/admin/reports/export-csv?status=${filterForm.status}&course_id=${filterForm.course_id}&semester=${filterForm.semester}&school_year=${filterForm.school_year}`"
                        class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                    >
                        Export CSV
                    </a>

                    <a
                        :href="`/admin/reports/print?status=${filterForm.status}&course_id=${filterForm.course_id}&semester=${filterForm.semester}&school_year=${filterForm.school_year}`"
                        class="rounded-xl bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                    >
                        Print Report
                    </a>
                </div>
            </section>

            <section class="rounded-2xl border bg-white shadow-sm">
                <div class="border-b p-6">
                    <h2 class="text-xl font-bold text-blue-950">
                        Report Results
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Showing up to the latest 100 clearance requests based on
                        the selected filters.
                    </p>
                </div>

                <div v-if="requests.length === 0" class="p-8 text-center">
                    <p class="font-medium text-slate-700">
                        No clearance requests found.
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Try changing or clearing the selected filters.
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
                                    Progress
                                </th>
                                <th class="px-6 py-3 font-semibold">Status</th>
                                <th class="px-6 py-3 font-semibold">
                                    Cleared At
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="request in requests"
                                :key="request.id"
                                class="border-t"
                            >
                                <td class="px-6 py-4 font-medium text-blue-950">
                                    {{ request.student_name }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ request.student_id }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ request.course_code }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ request.semester }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ request.school_year }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ request.approved_regular_approvals }} /
                                    {{ request.total_regular_approvals }}
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="statusClass(request)"
                                    >
                                        {{ statusLabel(request) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    {{
                                        request.cleared_at ?? 'Not cleared yet'
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-2xl border bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-blue-950">
                    Next Report Improvements
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    The next step will add export buttons for printable reports
                    or spreadsheet files.
                </p>
            </section>
        </div>
    </div>
</template>
