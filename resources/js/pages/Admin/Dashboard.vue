<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BarChart3,
    Building2,
    CheckCircle2,
    ClipboardCheck,
    FileText,
    GraduationCap,
    Settings,
    ShieldCheck,
    UserRoundCog,
    Users,
    XCircle,
} from 'lucide-vue-next';

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

    <div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-blue-50/40 p-4 text-slate-900 md:p-6">
        <div class="mx-auto flex max-w-7xl flex-col gap-6">
            <!-- Hero -->
            <section class="overflow-hidden rounded-4xl border border-slate-200 bg-white/95 shadow-xl shadow-slate-200/70">
                <div class="grid gap-8 p-6 lg:grid-cols-[1fr_320px] lg:p-8">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-blue-700">
                            <ShieldCheck class="size-4" />
                            Admin / OSAS Director Panel
                        </div>

                        <h1 class="mt-5 text-4xl font-black tracking-tight text-blue-950">
                            Admin Dashboard
                        </h1>

                        <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600">
                            Monitor student clearance activity, approval progress, course modules, offices, reports, and system users from one control center.
                        </p>

                        <div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/80 p-4 text-sm font-medium leading-6 text-blue-900">
                            You are logged in as the system administrator. Use this panel to manage users, monitor clearance progress, review reports, and maintain course-office module assignments.
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <Link
                                href="/admin/users"
                                class="inline-flex items-center gap-2 rounded-2xl bg-blue-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-700/20 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-xl"
                            >
                                <Users class="size-4" />
                                Manage Users
                            </Link>

                            <Link
                                href="/admin/clearance-requests"
                                class="inline-flex items-center gap-2 rounded-2xl bg-green-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-green-700/20 transition hover:-translate-y-0.5 hover:bg-green-800 hover:shadow-xl"
                            >
                                <ClipboardCheck class="size-4" />
                                Monitor Clearances
                            </Link>

                            <Link
                                href="/admin/reports"
                                class="inline-flex items-center gap-2 rounded-2xl bg-purple-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-purple-700/20 transition hover:-translate-y-0.5 hover:bg-purple-800 hover:shadow-xl"
                            >
                                <FileText class="size-4" />
                                View Reports
                            </Link>

                            <Link
                                href="/admin/course-modules"
                                class="inline-flex items-center gap-2 rounded-2xl bg-indigo-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-indigo-700/20 transition hover:-translate-y-0.5 hover:bg-indigo-800 hover:shadow-xl"
                            >
                                <Settings class="size-4" />
                                Course Modules
                            </Link>
                        </div>
                    </div>

                    <div class="hidden items-center justify-center lg:flex">
                        <div class="relative flex h-64 w-64 items-center justify-center">
                            <div class="absolute inset-0 rounded-full bg-blue-100 blur-2xl"></div>

                            <div class="relative grid h-52 w-52 place-items-center rounded-4xl border border-blue-100 bg-linear-to-br from-white to-blue-50 shadow-2xl shadow-slate-300/70">
                                <div class="grid h-20 w-20 place-items-center rounded-3xl bg-blue-700 text-white shadow-xl shadow-blue-700/25">
                                    <BarChart3 class="size-10" />
                                </div>

                                <p class="text-center text-sm font-black uppercase tracking-[0.18em] text-blue-700">
                                    System Overview
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- User and Structure Stats -->
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="grid h-14 w-14 place-items-center rounded-2xl bg-blue-50 text-blue-700 shadow-sm">
                            <GraduationCap class="size-7" />
                        </div>

                        <div>
                            <p class="text-sm font-black uppercase tracking-wide text-blue-700">
                                Students
                            </p>
                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ stats.students }}
                            </p>
                            <p class="text-sm font-medium text-slate-500">
                                Registered accounts
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="grid h-14 w-14 place-items-center rounded-2xl bg-indigo-50 text-indigo-700 shadow-sm">
                            <UserRoundCog class="size-7" />
                        </div>

                        <div>
                            <p class="text-sm font-black uppercase tracking-wide text-indigo-700">
                                Staff
                            </p>
                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ stats.staff }}
                            </p>
                            <p class="text-sm font-medium text-slate-500">
                                Office approvers
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="grid h-14 w-14 place-items-center rounded-2xl bg-green-50 text-green-700 shadow-sm">
                            <FileText class="size-7" />
                        </div>

                        <div>
                            <p class="text-sm font-black uppercase tracking-wide text-green-700">
                                Courses
                            </p>
                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ stats.courses }}
                            </p>
                            <p class="text-sm font-medium text-slate-500">
                                Course modules
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="grid h-14 w-14 place-items-center rounded-2xl bg-purple-50 text-purple-700 shadow-sm">
                            <Building2 class="size-7" />
                        </div>

                        <div>
                            <p class="text-sm font-black uppercase tracking-wide text-purple-700">
                                Offices
                            </p>
                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ stats.offices }}
                            </p>
                            <p class="text-sm font-medium text-slate-500">
                                Clearance offices
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Clearance Stats -->
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="grid h-14 w-14 place-items-center rounded-2xl bg-blue-50 text-blue-700 shadow-sm">
                            <ClipboardCheck class="size-7" />
                        </div>

                        <div>
                            <p class="text-sm font-black uppercase tracking-wide text-blue-700">
                                Clearance Requests
                            </p>
                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ stats.clearanceRequests }}
                            </p>
                            <p class="text-sm font-medium text-slate-500">
                                Submitted requests
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="grid h-14 w-14 place-items-center rounded-2xl bg-orange-50 text-orange-600 shadow-sm">
                            <BarChart3 class="size-7" />
                        </div>

                        <div>
                            <p class="text-sm font-black uppercase tracking-wide text-orange-600">
                                Pending Approvals
                            </p>
                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ stats.pendingApprovals }}
                            </p>
                            <p class="text-sm font-medium text-slate-500">
                                Waiting actions
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="grid h-14 w-14 place-items-center rounded-2xl bg-green-50 text-green-700 shadow-sm">
                            <CheckCircle2 class="size-7" />
                        </div>

                        <div>
                            <p class="text-sm font-black uppercase tracking-wide text-green-700">
                                Approved Approvals
                            </p>
                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ stats.approvedApprovals }}
                            </p>
                            <p class="text-sm font-medium text-slate-500">
                                Completed actions
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="grid h-14 w-14 place-items-center rounded-2xl bg-red-50 text-red-600 shadow-sm">
                            <XCircle class="size-7" />
                        </div>

                        <div>
                            <p class="text-sm font-black uppercase tracking-wide text-red-600">
                                Rejected Approvals
                            </p>
                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ stats.rejectedApprovals }}
                            </p>
                            <p class="text-sm font-medium text-slate-500">
                                Needs attention
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Recent Requests -->
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-sm shadow-slate-200/70">
                <div class="flex flex-col gap-4 border-b border-slate-200 bg-white px-6 py-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                            Latest Submissions
                        </p>

                        <h2 class="mt-1 text-xl font-black text-blue-950">
                            Recent Clearance Requests
                        </h2>

                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Latest student clearance requests submitted in the system.
                        </p>
                    </div>

                    <Link
                        href="/admin/clearance-requests"
                        class="inline-flex w-fit items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-blue-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:shadow-md"
                    >
                        View Monitoring
                        <ClipboardCheck class="size-4" />
                    </Link>
                </div>

                <div v-if="recentRequests.length === 0" class="p-10 text-center">
                    <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-blue-50 text-blue-700">
                        <ClipboardCheck class="size-8" />
                    </div>

                    <p class="mt-4 font-black text-slate-700">
                        No clearance requests yet.
                    </p>

                    <p class="mt-1 text-sm font-medium text-slate-500">
                        Student requests will appear here after submission.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-6 py-4 text-xs font-black uppercase tracking-wide">
                                    Student
                                </th>
                                <th class="px-6 py-4 text-xs font-black uppercase tracking-wide">
                                    Student ID
                                </th>
                                <th class="px-6 py-4 text-xs font-black uppercase tracking-wide">
                                    Course
                                </th>
                                <th class="px-6 py-4 text-xs font-black uppercase tracking-wide">
                                    Semester
                                </th>
                                <th class="px-6 py-4 text-xs font-black uppercase tracking-wide">
                                    School Year
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="request in recentRequests"
                                :key="request.id"
                                class="transition hover:bg-blue-50/50"
                            >
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-black text-blue-950">
                                            {{ request.user.name }}
                                        </p>
                                        <p class="mt-1 text-xs font-medium text-slate-500">
                                            Clearance request #{{ request.id }}
                                        </p>
                                    </div>
                                </td>

                                <td class="px-6 py-4 font-semibold text-slate-700">
                                    {{ request.user.student_id }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">
                                        {{ request.user.course?.code ?? 'N/A' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 font-semibold text-slate-700">
                                    {{ request.semester }}
                                </td>

                                <td class="px-6 py-4 font-semibold text-slate-700">
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