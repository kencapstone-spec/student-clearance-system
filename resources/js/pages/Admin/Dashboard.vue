<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    AlertTriangle,
    BarChart3,
    Building2,
    CheckCircle2,
    ClipboardCheck,
    FileText,
    GraduationCap,
    LayoutDashboard,
    Settings,
    ShieldCheck,
    UserRoundCog,
    Users,
    X,
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

type SystemSettings = {
    active_semester: string;
    active_school_year: string;
};

defineProps<{
    stats: Stats;
    recentRequests: ClearanceRequest[];
    settings: SystemSettings;
}>();

const showAdminMobileMoreMenu = ref(false);

const showTermModal = ref(false);
const confirmText = ref('');

const termForm = useForm({
    active_semester: '',
    active_school_year: '',
});

const openTermModal = (settings: SystemSettings) => {
    termForm.active_semester = settings.active_semester;
    termForm.active_school_year = settings.active_school_year;
    confirmText.value = '';
    showTermModal.value = true;
};

const closeTermModal = () => {
    showTermModal.value = false;
    termForm.reset();
};

const submitTermChange = () => {
    if (confirmText.value !== 'RESET CLEARANCES') return;

    termForm.patch('/admin/settings', {
        onSuccess: () => {
            closeTermModal();
        },
    });
};

const toggleAdminMobileMoreMenu = () => {
    showAdminMobileMoreMenu.value = !showAdminMobileMoreMenu.value;
};

const closeAdminMobileMoreMenu = () => {
    showAdminMobileMoreMenu.value = false;
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <div
        class="min-h-screen bg-linear-to-br from-slate-50 via-white to-blue-50/40 p-3 pb-28 text-slate-900 sm:p-4 sm:pb-28 md:p-6 md:pb-6"
    >
        <div class="mx-auto flex max-w-7xl flex-col gap-4 md:gap-6">
            <!-- Hero -->
            <section
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-xl shadow-slate-200/70 md:rounded-4xl"
            >
                <div
                    class="grid gap-6 p-4 sm:p-6 lg:grid-cols-[1fr_320px] lg:p-8"
                >
                    <div>
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-2 text-[0.65rem] font-black tracking-[0.14em] text-blue-700 uppercase sm:px-4 sm:text-xs sm:tracking-[0.18em]"
                        >
                            <ShieldCheck class="size-4" />
                            Admin / OSAS Director Panel
                        </div>

                        <h1
                            class="mt-5 text-3xl font-black tracking-tight text-blue-950 sm:text-4xl"
                        >
                            Admin Dashboard
                        </h1>

                        <p
                            class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base sm:leading-7"
                        >
                            Monitor student clearance activity, approval
                            progress, course modules, offices, reports, and
                            system users from one control center.
                        </p>

                        <div
                            class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/80 p-4 text-sm leading-6 font-medium text-blue-900"
                        >
                            You are logged in as the system administrator. Use
                            this panel to manage users, monitor clearance
                            progress, review reports, and maintain
                            course-office module assignments.
                        </div>

                        <div
                            class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:flex-wrap"
                        >
                            <Link
                                href="/admin/users"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl bg-blue-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-700/20 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-xl"
                            >
                                <Users class="size-4" />
                                Manage Users
                            </Link>

                            <Link
                                href="/admin/clearance-requests"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl bg-green-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-green-700/20 transition hover:-translate-y-0.5 hover:bg-green-800 hover:shadow-xl"
                            >
                                <ClipboardCheck class="size-4" />
                                Monitor Clearances
                            </Link>

                            <Link
                                href="/admin/reports"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl bg-purple-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-purple-700/20 transition hover:-translate-y-0.5 hover:bg-purple-800 hover:shadow-xl"
                            >
                                <FileText class="size-4" />
                                View Reports
                            </Link>

                            <Link
                                href="/admin/course-modules"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl bg-indigo-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-indigo-700/20 transition hover:-translate-y-0.5 hover:bg-indigo-800 hover:shadow-xl"
                            >
                                <Settings class="size-4" />
                                Course Modules
                            </Link>
                        </div>
                    </div>

                    <div class="hidden items-center justify-center lg:flex">
                        <div
                            class="relative flex h-64 w-64 items-center justify-center"
                        >
                            <div
                                class="absolute inset-0 rounded-full bg-blue-100 blur-2xl"
                            ></div>

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
                                    System Overview
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- User and Structure Stats -->
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
                            <GraduationCap class="size-6 md:size-7" />
                        </div>

                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-blue-700 uppercase sm:text-sm"
                            >
                                Students
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ stats.students }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Registered accounts
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:rounded-3xl md:p-6"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-indigo-50 text-indigo-700 shadow-sm md:h-14 md:w-14"
                        >
                            <UserRoundCog class="size-6 md:size-7" />
                        </div>

                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-indigo-700 uppercase sm:text-sm"
                            >
                                Staff
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ stats.staff }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Office approvers
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
                            <FileText class="size-6 md:size-7" />
                        </div>

                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-green-700 uppercase sm:text-sm"
                            >
                                Courses
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ stats.courses }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Course modules
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:rounded-3xl md:p-6"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-purple-50 text-purple-700 shadow-sm md:h-14 md:w-14"
                        >
                            <Building2 class="size-6 md:size-7" />
                        </div>

                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-purple-700 uppercase sm:text-sm"
                            >
                                Offices
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ stats.offices }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Clearance offices
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Clearance Stats -->
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
                                Clearance Requests
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ stats.clearanceRequests }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Submitted requests
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
                            <BarChart3 class="size-6 md:size-7" />
                        </div>

                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-orange-600 uppercase sm:text-sm"
                            >
                                Pending Approvals
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ stats.pendingApprovals }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Waiting actions
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
                                Approved Approvals
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ stats.approvedApprovals }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Completed actions
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
                            <XCircle class="size-6 md:size-7" />
                        </div>

                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-red-600 uppercase sm:text-sm"
                            >
                                Rejected Approvals
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ stats.rejectedApprovals }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Needs attention
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Academic Term Settings -->
            <section
                class="overflow-hidden rounded-3xl border border-blue-200 bg-white/95 shadow-sm shadow-blue-200/70"
            >
                <div
                    class="flex flex-col gap-4 border-b border-blue-100 bg-linear-to-r from-blue-50 to-white px-4 py-5 sm:px-6 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-black tracking-[0.18em] text-blue-600 uppercase"
                        >
                            System Configuration
                        </p>

                        <h2 class="mt-1 text-xl font-black text-blue-950">
                            Active Academic Term
                        </h2>

                        <p class="mt-1 text-sm font-medium text-slate-500">
                            The current semester and school year for all new
                            clearance requests.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="openTermModal(settings)"
                        class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-2xl bg-amber-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-amber-500/20 transition hover:-translate-y-0.5 hover:bg-amber-600 hover:shadow-xl sm:w-auto"
                    >
                        Change Term
                        <AlertTriangle class="size-4" />
                    </button>
                </div>

                <div class="grid gap-0 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                    <div class="p-6">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Current Semester</p>
                        <p class="mt-2 text-3xl font-black text-blue-950">{{ settings.active_semester }}</p>
                    </div>
                    <div class="p-6">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Current School Year</p>
                        <p class="mt-2 text-3xl font-black text-blue-950">{{ settings.active_school_year }}</p>
                    </div>
                </div>
            </section>

            <!-- Recent Requests -->
            <section
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-sm shadow-slate-200/70"
            >
                <div
                    class="flex flex-col gap-4 border-b border-slate-200 bg-white px-4 py-5 sm:px-6 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-black tracking-[0.18em] text-slate-400 uppercase"
                        >
                            Latest Submissions
                        </p>

                        <h2 class="mt-1 text-xl font-black text-blue-950">
                            Recent Clearance Requests
                        </h2>

                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Latest student clearance requests submitted in the
                            system.
                        </p>
                    </div>

                    <Link
                        href="/admin/clearance-requests"
                        class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-blue-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:shadow-md sm:w-auto"
                    >
                        View Monitoring
                        <ClipboardCheck class="size-4" />
                    </Link>
                </div>

                <div v-if="recentRequests.length === 0" class="p-8 text-center sm:p-10">
                    <div
                        class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-blue-50 text-blue-700"
                    >
                        <ClipboardCheck class="size-8" />
                    </div>

                    <p class="mt-4 font-black text-slate-700">
                        No clearance requests yet.
                    </p>

                    <p class="mt-1 text-sm font-medium text-slate-500">
                        Student requests will appear here after submission.
                    </p>
                </div>

                <div v-else>
                    <!-- Mobile Recent Request Cards -->
                    <div class="grid gap-3 p-4 lg:hidden">
                        <article
                            v-for="request in recentRequests"
                            :key="request.id"
                            class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3
                                        class="truncate text-base font-black text-blue-950"
                                    >
                                        {{ request.user.name }}
                                    </h3>

                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-600"
                                    >
                                        {{ request.user.student_id }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs font-medium text-slate-500"
                                    >
                                        Clearance request #{{ request.id }}
                                    </p>
                                </div>

                                <span
                                    class="shrink-0 rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700"
                                >
                                    {{ request.user.course?.code ?? 'N/A' }}
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
                        </article>
                    </div>

                    <!-- Desktop Recent Request Table -->
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

                                            <p
                                                class="mt-1 text-xs font-medium text-slate-500"
                                            >
                                                Clearance request
                                                #{{ request.id }}
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
                                                request.user.course?.code ??
                                                'N/A'
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
                    Quick access to reports and course modules.
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
                class="flex min-h-12 items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700"
            >
                <span>Reports</span>
                <span>→</span>
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
                class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl bg-white/15 px-2 py-2 text-[0.65rem] font-black text-white ring-1 ring-blue-200/40 transition hover:bg-white/10"
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
                <ClipboardCheck class="size-4" />
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

    <!-- Term Change Confirmation Modal -->
    <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div
            v-if="showTermModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        >
            <!-- Backdrop -->
            <div
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="closeTermModal"
            ></div>

            <!-- Modal Panel -->
            <div
                class="relative w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl"
            >
                <div class="border-b border-slate-100 bg-amber-50 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="grid size-10 shrink-0 place-items-center rounded-full bg-amber-100 text-amber-600">
                            <AlertTriangle class="size-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-amber-950">Change Academic Term</h3>
                            <p class="text-sm font-medium text-amber-700">DANGER: This action will reset clearances.</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submitTermChange" class="p-6">
                    <div class="rounded-2xl border border-red-100 bg-red-50 p-4 mb-6">
                        <p class="text-sm font-bold text-red-800">
                            Warning: Updating the academic term will force all students to submit new clearance requests for the new term. Old clearance requests will remain in the database for historical purposes but will no longer be considered active.
                        </p>
                    </div>

                    <div class="grid gap-5">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">New Semester</label>
                            <select
                                v-model="termForm.active_semester"
                                class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-amber-500 focus:ring-amber-500"
                                required
                            >
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">New School Year</label>
                            <input
                                v-model="termForm.active_school_year"
                                type="text"
                                placeholder="e.g., 2026-2027"
                                class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 focus:border-amber-500 focus:ring-amber-500"
                                required
                            />
                        </div>

                        <div class="mt-4 border-t border-slate-100 pt-5">
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                Type <span class="rounded bg-slate-200 px-1.5 py-0.5 font-mono text-red-600">RESET CLEARANCES</span> to confirm:
                            </label>
                            <input
                                v-model="confirmText"
                                type="text"
                                placeholder="RESET CLEARANCES"
                                class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 font-mono text-sm font-bold text-red-600 focus:border-red-500 focus:ring-red-500"
                                required
                            />
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-xl px-5 py-3 text-sm font-bold text-slate-600 hover:bg-slate-100"
                            @click="closeTermModal"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="inline-flex min-h-12 items-center justify-center rounded-xl bg-red-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-red-600/20 transition hover:-translate-y-0.5 hover:bg-red-700 hover:shadow-xl disabled:opacity-50 disabled:hover:translate-y-0 disabled:hover:bg-red-600 disabled:hover:shadow-none"
                            :disabled="confirmText !== 'RESET CLEARANCES' || termForm.processing"
                        >
                            {{ termForm.processing ? 'Saving...' : 'Save & Reset Clearances' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>
</template>