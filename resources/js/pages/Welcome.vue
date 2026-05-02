<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BarChart3,
    CheckCircle2,
    ClipboardCheck,
    Download,
    FileCheck2,
    FileText,
    GraduationCap,
    LayoutDashboard,
    QrCode,
    ShieldCheck,
    Sparkles,
    UserCheck,
    Users,
} from 'lucide-vue-next';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Student Clearance System" />

    <div class="min-h-screen overflow-hidden bg-slate-50 text-slate-950">
        <!-- Hero Background -->
        <section
            class="relative overflow-hidden bg-slate-950 text-white"
        >
            <div class="pointer-events-none absolute inset-0">
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(37,99,235,0.35),transparent_34%),radial-gradient(circle_at_bottom_left,rgba(14,165,233,0.22),transparent_34%)]"
                ></div>

                <div
                    class="absolute inset-0 opacity-[0.06] [background-image:linear-gradient(rgba(255,255,255,0.9)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.9)_1px,transparent_1px)] [background-size:64px_64px]"
                ></div>

                <div
                    class="absolute right-[-12rem] top-16 h-[28rem] w-[28rem] rounded-full bg-blue-500/25 blur-3xl"
                ></div>

                <div
                    class="absolute bottom-[-14rem] left-[-10rem] h-[30rem] w-[30rem] rounded-full bg-cyan-400/15 blur-3xl"
                ></div>
            </div>

            <!-- Header -->
            <header
                class="relative z-20 border-b border-white/10 bg-slate-950/75 backdrop-blur-xl"
            >
                <div
                    class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="grid size-11 place-items-center rounded-2xl bg-blue-600 text-sm font-black text-white shadow-lg shadow-blue-600/30"
                        >
                            TPC
                        </div>

                        <div>
                            <p
                                class="text-[11px] font-black uppercase tracking-[0.22em] text-blue-300"
                            >
                                Talibon Polytechnic College
                            </p>

                            <h1
                                class="text-base font-black tracking-tight text-white"
                            >
                                Student Clearance System
                            </h1>
                        </div>
                    </div>

                    <nav class="flex items-center gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-blue-600/25 transition hover:-translate-y-0.5 hover:bg-blue-500"
                        >
                            <LayoutDashboard class="size-4" />
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="login()"
                                class="rounded-2xl border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-black text-white/80 transition hover:-translate-y-0.5 hover:border-blue-300/40 hover:bg-white/10 hover:text-white"
                            >
                                Log in
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-blue-600/25 transition hover:-translate-y-0.5 hover:bg-blue-500"
                            >
                                Register
                            </Link>
                        </template>
                    </nav>
                </div>
            </header>

            <!-- Hero -->
            <div
                class="relative z-10 mx-auto grid min-h-[calc(100vh-80px)] max-w-7xl gap-12 px-6 py-16 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:py-20"
            >
                <div>
                    <div
                        class="mb-6 inline-flex items-center gap-2 rounded-full border border-blue-300/25 bg-blue-400/10 px-4 py-2 text-sm font-black text-blue-100 shadow-sm"
                    >
                        <Sparkles class="size-4 text-blue-300" />
                        Web-based clearance with role-based approval
                    </div>

                    <h2
                        class="max-w-4xl text-5xl font-black leading-[1.05] tracking-tight text-white md:text-7xl"
                    >
                        A faster, cleaner, and more reliable way to process
                        student clearance.
                    </h2>

                    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                        Submit clearance requests online, route approvals to the
                        correct offices, monitor progress, and generate
                        QR-verified clearance receipts from one controlled
                        system.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-7 py-4 text-sm font-black text-white shadow-xl shadow-blue-600/30 transition hover:-translate-y-1 hover:bg-blue-500 hover:shadow-2xl"
                        >
                            Go to Dashboard
                            <ArrowRight class="size-4" />
                        </Link>

                        <template v-else>
                            <Link
                                :href="login()"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-7 py-4 text-sm font-black text-white shadow-xl shadow-blue-600/30 transition hover:-translate-y-1 hover:bg-blue-500 hover:shadow-2xl"
                            >
                                Log in to Continue
                                <ArrowRight class="size-4" />
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/5 px-7 py-4 text-sm font-black text-white/85 transition hover:-translate-y-1 hover:border-blue-300/40 hover:bg-white/10 hover:text-white"
                            >
                                Register as Student
                            </Link>
                        </template>
                    </div>

                    <div class="mt-10 flex flex-wrap items-center gap-3">
                        <span
                            class="text-xs font-black uppercase tracking-[0.2em] text-white/35"
                        >
                            Roles
                        </span>

                        <div
                            class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-bold text-white/70"
                        >
                            <span class="text-white">Student</span> — Submit &
                            track
                        </div>

                        <div
                            class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-bold text-white/70"
                        >
                            <span class="text-white">Office Staff</span> —
                            Review requests
                        </div>

                        <div
                            class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-bold text-white/70"
                        >
                            <span class="text-white">Admin</span> — Monitor &
                            report
                        </div>

                        <div
                            class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-bold text-white/70"
                        >
                            <span class="text-white">President</span> — Final
                            approval
                        </div>
                    </div>
                </div>

                <!-- Workflow Card -->
                <div class="relative">
                    <div
                        class="absolute -inset-6 rounded-[3rem] bg-blue-500/20 blur-3xl"
                    ></div>

                    <div
                        class="relative rounded-[2rem] border border-white/10 bg-white/[0.06] p-6 shadow-2xl shadow-black/30 backdrop-blur-xl"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-xs font-black uppercase tracking-[0.22em] text-blue-300"
                                >
                                    Core Workflow
                                </p>

                                <h3 class="mt-2 text-3xl font-black text-white">
                                    Clearance Process
                                </h3>
                            </div>

                            <div
                                class="grid size-14 place-items-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/30"
                            >
                                <ClipboardCheck class="size-7" />
                            </div>
                        </div>

                        <div class="mt-7 space-y-3">
                            <div
                                class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/[0.05] p-4 transition hover:-translate-y-0.5 hover:border-blue-300/30 hover:bg-white/[0.08]"
                            >
                                <div
                                    class="grid size-11 shrink-0 place-items-center rounded-2xl bg-blue-500/15 text-blue-300"
                                >
                                    <FileCheck2 class="size-5" />
                                </div>

                                <div class="flex-1">
                                    <p class="font-black text-white">
                                        Student Request
                                    </p>

                                    <p class="mt-1 text-sm leading-6 text-slate-400">
                                        Students submit clearance requests and
                                        follow course-assigned offices.
                                    </p>
                                </div>

                                <span class="text-xs font-black text-white/20">
                                    01
                                </span>
                            </div>

                            <div
                                class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/[0.05] p-4 transition hover:-translate-y-0.5 hover:border-green-300/30 hover:bg-white/[0.08]"
                            >
                                <div
                                    class="grid size-11 shrink-0 place-items-center rounded-2xl bg-green-500/15 text-green-300"
                                >
                                    <Users class="size-5" />
                                </div>

                                <div class="flex-1">
                                    <p class="font-black text-white">
                                        Office Approval
                                    </p>

                                    <p class="mt-1 text-sm leading-6 text-slate-400">
                                        Office staff review only the requests
                                        assigned to their office.
                                    </p>
                                </div>

                                <span class="text-xs font-black text-white/20">
                                    02
                                </span>
                            </div>

                            <div
                                class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/[0.05] p-4 transition hover:-translate-y-0.5 hover:border-purple-300/30 hover:bg-white/[0.08]"
                            >
                                <div
                                    class="grid size-11 shrink-0 place-items-center rounded-2xl bg-purple-500/15 text-purple-300"
                                >
                                    <ShieldCheck class="size-5" />
                                </div>

                                <div class="flex-1">
                                    <p class="font-black text-white">
                                        Final Approval
                                    </p>

                                    <p class="mt-1 text-sm leading-6 text-slate-400">
                                        The President gives final clearance
                                        approval after all offices clear.
                                    </p>
                                </div>

                                <span class="text-xs font-black text-white/20">
                                    03
                                </span>
                            </div>

                            <div
                                class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/[0.05] p-4 transition hover:-translate-y-0.5 hover:border-orange-300/30 hover:bg-white/[0.08]"
                            >
                                <div
                                    class="grid size-11 shrink-0 place-items-center rounded-2xl bg-orange-500/15 text-orange-300"
                                >
                                    <QrCode class="size-5" />
                                </div>

                                <div class="flex-1">
                                    <p class="font-black text-white">
                                        Receipt Verification
                                    </p>

                                    <p class="mt-1 text-sm leading-6 text-slate-400">
                                        Cleared students print QR-verified
                                        clearance receipts.
                                    </p>
                                </div>

                                <span class="text-xs font-black text-white/20">
                                    04
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Strip -->
            <div class="relative z-10 border-y border-white/10 bg-white/[0.04]">
                <div
                    class="mx-auto grid max-w-7xl gap-4 px-6 py-8 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <div
                        class="rounded-3xl border border-white/10 bg-white/[0.04] p-5"
                    >
                        <p class="text-3xl font-black text-white">4 roles</p>
                        <p class="mt-2 text-sm font-medium text-slate-400">
                            Student, staff, admin, and president access
                        </p>
                    </div>

                    <div
                        class="rounded-3xl border border-white/10 bg-white/[0.04] p-5"
                    >
                        <p class="text-3xl font-black text-white">QR</p>
                        <p class="mt-2 text-sm font-medium text-slate-400">
                            Public receipt verification
                        </p>
                    </div>

                    <div
                        class="rounded-3xl border border-white/10 bg-white/[0.04] p-5"
                    >
                        <p class="text-3xl font-black text-white">CSV</p>
                        <p class="mt-2 text-sm font-medium text-slate-400">
                            Exportable report records
                        </p>
                    </div>

                    <div
                        class="rounded-3xl border border-white/10 bg-white/[0.04] p-5"
                    >
                        <p class="text-3xl font-black text-white">Modules</p>
                        <p class="mt-2 text-sm font-medium text-slate-400">
                            Course-based office requirements
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <main class="relative z-10 bg-slate-50">
            <section class="mx-auto max-w-7xl px-6 py-20">
                <div class="max-w-3xl">
                    <p
                        class="text-xs font-black uppercase tracking-[0.22em] text-blue-700"
                    >
                        What's Included
                    </p>

                    <h2
                        class="mt-4 text-4xl font-black tracking-tight text-blue-950 md:text-5xl"
                    >
                        Everything the clearance process needs — without
                        unnecessary clutter.
                    </h2>

                    <p class="mt-4 text-lg leading-8 text-slate-600">
                        The system is designed around the actual clearance
                        workflow: request submission, office approval, final
                        approval, reporting, receipt printing, and QR
                        verification.
                    </p>
                </div>

                <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <div
                        class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl"
                    >
                        <div
                            class="grid size-14 place-items-center rounded-2xl bg-blue-50 text-blue-700"
                        >
                            <ShieldCheck class="size-7" />
                        </div>

                        <h3 class="mt-5 text-lg font-black text-blue-950">
                            Role-Based Access
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Students, office staff, admins, and the president
                            each access only the tools needed for their role.
                        </p>
                    </div>

                    <div
                        class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl"
                    >
                        <div
                            class="grid size-14 place-items-center rounded-2xl bg-green-50 text-green-700"
                        >
                            <BarChart3 class="size-7" />
                        </div>

                        <h3 class="mt-5 text-lg font-black text-blue-950">
                            Real-Time Tracking
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Students can view office progress, pending requests,
                            approved offices, and final clearance status.
                        </p>
                    </div>

                    <div
                        class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl"
                    >
                        <div
                            class="grid size-14 place-items-center rounded-2xl bg-orange-50 text-orange-700"
                        >
                            <FileText class="size-7" />
                        </div>

                        <h3 class="mt-5 text-lg font-black text-blue-950">
                            Reports and Monitoring
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Admin users can monitor clearance progress, print
                            reports, and export CSV data for records.
                        </p>
                    </div>

                    <div
                        class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl"
                    >
                        <div
                            class="grid size-14 place-items-center rounded-2xl bg-purple-50 text-purple-700"
                        >
                            <QrCode class="size-7" />
                        </div>

                        <h3 class="mt-5 text-lg font-black text-blue-950">
                            QR Verification
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Fully cleared students receive a printable receipt
                            with public QR-based validation.
                        </p>
                    </div>

                    <div
                        class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl"
                    >
                        <div
                            class="grid size-14 place-items-center rounded-2xl bg-cyan-50 text-cyan-700"
                        >
                            <ClipboardCheck class="size-7" />
                        </div>

                        <h3 class="mt-5 text-lg font-black text-blue-950">
                            Course Modules
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Admin can configure which clearance offices are
                            required for each course module.
                        </p>
                    </div>

                    <div
                        class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl"
                    >
                        <div
                            class="grid size-14 place-items-center rounded-2xl bg-rose-50 text-rose-700"
                        >
                            <CheckCircle2 class="size-7" />
                        </div>

                        <h3 class="mt-5 text-lg font-black text-blue-950">
                            Final Approval
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            The president gives the final sign-off only after
                            required offices complete their review.
                        </p>
                    </div>
                </div>
            </section>

            <!-- How It Works -->
            <section class="border-y border-slate-200 bg-white">
                <div class="mx-auto max-w-7xl px-6 py-20">
                    <div class="max-w-3xl">
                        <p
                            class="text-xs font-black uppercase tracking-[0.22em] text-blue-700"
                        >
                            How It Works
                        </p>

                        <h2
                            class="mt-4 text-4xl font-black tracking-tight text-blue-950 md:text-5xl"
                        >
                            From request to receipt in four clear steps.
                        </h2>
                    </div>

                    <div
                        class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-4"
                    >
                        <div
                            class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6 shadow-sm"
                        >
                            <div
                                class="grid size-14 place-items-center rounded-full bg-blue-700 text-white"
                            >
                                <span class="font-black">1</span>
                            </div>

                            <GraduationCap class="mt-6 size-7 text-blue-700" />

                            <h3 class="mt-4 text-lg font-black text-blue-950">
                                Submit Request
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Student registers, logs in, and submits a
                                clearance request.
                            </p>
                        </div>

                        <div
                            class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6 shadow-sm"
                        >
                            <div
                                class="grid size-14 place-items-center rounded-full bg-blue-700 text-white"
                            >
                                <span class="font-black">2</span>
                            </div>

                            <UserCheck class="mt-6 size-7 text-green-700" />

                            <h3 class="mt-4 text-lg font-black text-blue-950">
                                Office Review
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Assigned offices approve or reject based on
                                their records.
                            </p>
                        </div>

                        <div
                            class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6 shadow-sm"
                        >
                            <div
                                class="grid size-14 place-items-center rounded-full bg-blue-700 text-white"
                            >
                                <span class="font-black">3</span>
                            </div>

                            <ShieldCheck class="mt-6 size-7 text-purple-700" />

                            <h3 class="mt-4 text-lg font-black text-blue-950">
                                Final Sign-off
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                The president gives final approval after all
                                office approvals are complete.
                            </p>
                        </div>

                        <div
                            class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6 shadow-sm"
                        >
                            <div
                                class="grid size-14 place-items-center rounded-full bg-blue-700 text-white"
                            >
                                <span class="font-black">4</span>
                            </div>

                            <Download class="mt-6 size-7 text-orange-700" />

                            <h3 class="mt-4 text-lg font-black text-blue-950">
                                Print Receipt
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Student prints a QR-verified receipt after final
                                clearance.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Roles -->
            <section class="bg-slate-950 text-white">
                <div class="mx-auto max-w-7xl px-6 py-20">
                    <div class="max-w-3xl">
                        <p
                            class="text-xs font-black uppercase tracking-[0.22em] text-blue-300"
                        >
                            Access Levels
                        </p>

                        <h2
                            class="mt-4 text-4xl font-black tracking-tight md:text-5xl"
                        >
                            Built for everyone involved in the clearance process.
                        </h2>

                        <p class="mt-4 text-lg leading-8 text-slate-400">
                            Each user role has a focused dashboard so the system
                            stays clear, controlled, and easy to defend.
                        </p>
                    </div>

                    <div
                        class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-4"
                    >
                        <div
                            class="rounded-[2rem] border border-white/10 bg-white/[0.05] p-6 transition hover:-translate-y-1 hover:border-blue-300/30 hover:bg-white/[0.08]"
                        >
                            <GraduationCap class="size-10 text-blue-300" />

                            <h3 class="mt-5 text-xl font-black">Student</h3>

                            <p class="mt-2 text-sm leading-6 text-slate-400">
                                Submit requests, track office progress, and
                                print the clearance receipt.
                            </p>
                        </div>

                        <div
                            class="rounded-[2rem] border border-white/10 bg-white/[0.05] p-6 transition hover:-translate-y-1 hover:border-green-300/30 hover:bg-white/[0.08]"
                        >
                            <Users class="size-10 text-green-300" />

                            <h3 class="mt-5 text-xl font-black">
                                Office Staff
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-slate-400">
                                Review assigned requests and approve or reject
                                with remarks.
                            </p>
                        </div>

                        <div
                            class="rounded-[2rem] border border-white/10 bg-white/[0.05] p-6 transition hover:-translate-y-1 hover:border-orange-300/30 hover:bg-white/[0.08]"
                        >
                            <BarChart3 class="size-10 text-orange-300" />

                            <h3 class="mt-5 text-xl font-black">Admin</h3>

                            <p class="mt-2 text-sm leading-6 text-slate-400">
                                Monitor clearances, manage users, configure
                                modules, and generate reports.
                            </p>
                        </div>

                        <div
                            class="rounded-[2rem] border border-white/10 bg-white/[0.05] p-6 transition hover:-translate-y-1 hover:border-purple-300/30 hover:bg-white/[0.08]"
                        >
                            <ShieldCheck class="size-10 text-purple-300" />

                            <h3 class="mt-5 text-xl font-black">President</h3>

                            <p class="mt-2 text-sm leading-6 text-slate-400">
                                Give final approval after all required offices
                                have cleared the student.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="border-t border-slate-200 bg-white">
                <div
                    class="mx-auto flex max-w-7xl flex-col gap-4 px-6 py-8 text-sm text-slate-500 md:flex-row md:items-center md:justify-between"
                >
                    <p>
                        © 2026 Talibon Polytechnic College — Student Clearance
                        System
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <span>Role-based approval</span>
                        <span>QR verification</span>
                        <span>Reports and monitoring</span>
                    </div>
                </div>
            </footer>
        </main>
    </div>
</template>