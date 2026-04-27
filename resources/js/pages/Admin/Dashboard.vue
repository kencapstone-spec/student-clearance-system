<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

type Stats = {
    students: number;
    staff: number;
    courses: number;
    offices: number;
    clearanceRequests: number;
    pendingApprovals: number;
    approvedApprovals: number;
    rejectedApprovals: number;
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

defineProps<{
    stats: Stats;
    recentRequests: ClearanceRequest[];
}>();
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="min-h-screen bg-slate-50 p-4 text-slate-900">
        <div class="mx-auto flex max-w-7xl flex-col gap-6">
            <section class="rounded-2xl border bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-blue-700">
                    Admin / OSAS Director Panel
                </p>

                <h1 class="mt-2 text-3xl font-bold text-blue-950">
                    Admin Dashboard
                </h1>

                <p class="mt-2 text-slate-600">
                    Monitor student clearance activity, approval progress,
                    courses, offices, and system users.
                </p>

                <div
                    class="mt-4 rounded-xl bg-blue-50 p-4 text-sm text-blue-900"
                >
                    You are logged in as the system administrator. Future admin
                    tools will be added here for managing users, staff accounts,
                    courses, offices, reports, and approval monitoring.
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                    <Link
                        href="/admin/users"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Manage Users
                    </Link>

                    <Link
                        href="/admin/clearance-requests"
                        class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                    >
                        Monitor Clearances
                    </Link>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-blue-700">Students</p>
                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ stats.students }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-indigo-700">Staff</p>
                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ stats.staff }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-green-700">Courses</p>
                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ stats.courses }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-purple-700">Offices</p>
                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ stats.offices }}
                    </p>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-blue-700">
                        Clearance Requests
                    </p>
                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ stats.clearanceRequests }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-orange-600">
                        Pending Approvals
                    </p>
                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ stats.pendingApprovals }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-green-700">
                        Approved Approvals
                    </p>
                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ stats.approvedApprovals }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-red-600">Rejected Approvals</p>
                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ stats.rejectedApprovals }}
                    </p>
                </div>
            </section>

            <section class="rounded-2xl border bg-white shadow-sm">
                <div class="border-b p-6">
                    <h2 class="text-xl font-bold text-blue-950">
                        Recent Clearance Requests
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Latest student clearance requests submitted in the
                        system.
                    </p>
                </div>

                <div v-if="recentRequests.length === 0" class="p-8 text-center">
                    <p class="font-medium text-slate-700">
                        No clearance requests yet.
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Student requests will appear here after submission.
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
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="request in recentRequests"
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
                                    {{ request.user.course?.code ?? 'N/A' }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ request.semester }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ request.school_year }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</template>
