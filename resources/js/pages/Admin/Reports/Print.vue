<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

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
    status_label: string;
    cleared_at: string | null;
    approved_regular_approvals: number;
    total_regular_approvals: number;
};

defineProps<{
    filters: Filters;
    summary: Summary;
    requests: ReportRequest[];
    generatedAt: string;
}>();

const printPage = () => {
    window.print();
};
</script>

<template>
    <Head title="Printable Clearance Report" />

    <div class="min-h-screen bg-slate-100 p-4 text-slate-900 print:bg-white print:p-0">
        <div class="mx-auto max-w-7xl bg-white p-8 shadow-sm print:shadow-none">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3 print:hidden">
                <Link
                    href="/admin/reports"
                    class="rounded-xl border bg-white px-4 py-2 text-sm font-semibold text-blue-950 hover:bg-slate-100"
                >
                    Back to Reports
                </Link>

                <button
                    type="button"
                    class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    @click="printPage"
                >
                    Print / Save as PDF
                </button>
            </div>

            <header class="border-b pb-5 text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">
                    Talibon Polytechnic College
                </p>

                <h1 class="mt-2 text-2xl font-bold text-blue-950">
                    Student Clearance Report
                </h1>

                <p class="mt-1 text-sm text-slate-600">
                    Generated on {{ generatedAt }}
                </p>
            </header>

            <section class="mt-6 grid gap-3 text-sm sm:grid-cols-4">
                <div class="rounded-lg border p-3">
                    <p class="font-semibold text-slate-600">Status Filter</p>
                    <p class="mt-1 text-blue-950">
                        {{ filters.status || 'all' }}
                    </p>
                </div>

                <div class="rounded-lg border p-3">
                    <p class="font-semibold text-slate-600">Course ID Filter</p>
                    <p class="mt-1 text-blue-950">
                        {{ filters.course_id || 'All Courses' }}
                    </p>
                </div>

                <div class="rounded-lg border p-3">
                    <p class="font-semibold text-slate-600">Semester Filter</p>
                    <p class="mt-1 text-blue-950">
                        {{ filters.semester || 'All Semesters' }}
                    </p>
                </div>

                <div class="rounded-lg border p-3">
                    <p class="font-semibold text-slate-600">School Year Filter</p>
                    <p class="mt-1 text-blue-950">
                        {{ filters.school_year || 'All School Years' }}
                    </p>
                </div>
            </section>

            <section class="mt-6 grid gap-3 text-sm sm:grid-cols-4">
                <div class="rounded-lg border p-3">
                    <p class="font-semibold text-slate-600">Total Records</p>
                    <p class="mt-1 text-xl font-bold text-blue-950">
                        {{ summary.totalRequests }}
                    </p>
                </div>

                <div class="rounded-lg border p-3">
                    <p class="font-semibold text-green-700">Cleared</p>
                    <p class="mt-1 text-xl font-bold text-blue-950">
                        {{ summary.clearedRequests }}
                    </p>
                </div>

                <div class="rounded-lg border p-3">
                    <p class="font-semibold text-orange-600">Pending</p>
                    <p class="mt-1 text-xl font-bold text-blue-950">
                        {{ summary.pendingRequests }}
                    </p>
                </div>

                <div class="rounded-lg border p-3">
                    <p class="font-semibold text-red-600">Needs Attention</p>
                    <p class="mt-1 text-xl font-bold text-blue-950">
                        {{ summary.needsAttentionRequests }}
                    </p>
                </div>
            </section>

            <section class="mt-6">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-blue-950">
                        Report Results
                    </h2>

                    <p class="text-sm text-slate-600">
                        Total Records: {{ requests.length }}
                    </p>
                </div>

                <div v-if="requests.length === 0" class="rounded-xl border p-8 text-center">
                    <p class="font-medium text-slate-700">
                        No clearance requests found.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-xs">
                        <thead>
                            <tr class="bg-slate-100">
                                <th class="border px-3 py-2">Student</th>
                                <th class="border px-3 py-2">Student ID</th>
                                <th class="border px-3 py-2">Course</th>
                                <th class="border px-3 py-2">Semester</th>
                                <th class="border px-3 py-2">School Year</th>
                                <th class="border px-3 py-2">Progress</th>
                                <th class="border px-3 py-2">Status</th>
                                <th class="border px-3 py-2">Cleared At</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="request in requests"
                                :key="request.id"
                            >
                                <td class="border px-3 py-2">
                                    {{ request.student_name }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ request.student_id }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ request.course_code }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ request.semester }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ request.school_year }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ request.approved_regular_approvals }} /
                                    {{ request.total_regular_approvals }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ request.status_label }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ request.cleared_at ?? 'Not cleared yet' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <footer class="mt-8 border-t pt-4 text-center text-xs text-slate-500">
                This report was generated from the Web-based Student Clearance System.
            </footer>
        </div>
    </div>
</template>