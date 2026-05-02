<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle, ClipboardCheck, FileText, QrCode, ShieldCheck, Users } from 'lucide-vue-next';
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

    <div class="relative min-h-screen overflow-hidden bg-slate-50 text-slate-950">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute left-[-8rem] top-[-10rem] h-80 w-80 rounded-full bg-blue-200/50 blur-3xl"></div>
            <div class="absolute right-[-7rem] top-28 h-96 w-96 rounded-full bg-indigo-200/50 blur-3xl"></div>
            <div class="absolute bottom-[-10rem] left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-cyan-100/70 blur-3xl"></div>
        </div>

        <header class="relative z-10 border-b border-slate-200/80 bg-white/80 backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-2xl bg-blue-700 text-sm font-black text-white shadow-lg shadow-blue-700/20">
                        TPC
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">
                            Talibon Polytechnic College
                        </p>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-950">
                            Student Clearance System
                        </h1>
                    </div>
                </div>

                <nav class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-blue-700/20 transition hover:-translate-y-0.5 hover:bg-blue-800"
                    >
                        Dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:text-blue-700 hover:shadow-md"
                        >
                            Log in
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-blue-700/20 transition hover:-translate-y-0.5 hover:bg-blue-800"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="relative z-10 mx-auto grid max-w-7xl gap-12 px-6 py-16 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:py-20">
            <section>
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white px-4 py-2 text-sm font-bold text-blue-700 shadow-sm">
                    <span class="size-2 rounded-full bg-blue-700"></span>
                    Web-based Student Clearance System with Role-Based Approval
                </div>

                <h2 class="max-w-4xl text-4xl font-black leading-tight tracking-tight text-slate-950 md:text-6xl">
                    A cleaner, faster, and more reliable way to process student clearance.
                </h2>

                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                    Students can submit clearance requests online, offices can review requests based on their assigned role, and final clearance can be verified through printable receipts with QR-based public validation.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="rounded-2xl bg-blue-700 px-7 py-3.5 text-center text-sm font-black text-white shadow-xl shadow-blue-700/25 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-2xl"
                    >
                        Go to Dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-2xl bg-blue-700 px-7 py-3.5 text-center text-sm font-black text-white shadow-xl shadow-blue-700/25 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-2xl"
                        >
                            Log in to Continue
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="rounded-2xl border border-slate-300 bg-white px-7 py-3.5 text-center text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:text-blue-700 hover:shadow-lg"
                        >
                            Register as Student
                        </Link>
                    </template>
                </div>

                <div class="mt-10 grid max-w-2xl gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white/85 p-4 shadow-sm backdrop-blur">
                        <p class="text-sm font-black text-slate-950">Student</p>
                        <p class="mt-1 text-xs leading-5 text-slate-500">Submit and track requests.</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white/85 p-4 shadow-sm backdrop-blur">
                        <p class="text-sm font-black text-slate-950">Office Staff</p>
                        <p class="mt-1 text-xs leading-5 text-slate-500">Approve or reject assigned items.</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white/85 p-4 shadow-sm backdrop-blur">
                        <p class="text-sm font-black text-slate-950">Admin</p>
                        <p class="mt-1 text-xs leading-5 text-slate-500">Monitor users, reports, and modules.</p>
                    </div>
                </div>
            </section>

            <section class="relative">
                <div class="absolute -inset-4 rounded-[2.5rem] bg-gradient-to-br from-blue-200/70 via-white to-cyan-100/70 blur-2xl"></div>

                <div class="relative rounded-[2rem] border border-slate-200 bg-white/90 p-5 shadow-2xl shadow-slate-300/60 backdrop-blur-xl">
                    <div class="rounded-[1.5rem] border border-slate-100 bg-gradient-to-br from-slate-50 to-white p-6">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-bold text-blue-700">
                                    Core Workflow
                                </p>

                                <h3 class="mt-1 text-2xl font-black tracking-tight text-slate-950">
                                    Clearance Process
                                </h3>
                            </div>

                            <div class="rounded-2xl bg-blue-50 p-3">
                                <ClipboardCheck class="size-7 text-blue-700" />
                            </div>
                        </div>

                        <div class="mt-7 space-y-4">
                            <div class="group flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-50">
                                    <ClipboardCheck class="size-5 text-blue-700" />
                                </div>
                                <div>
                                    <p class="font-black text-slate-900">Student Request</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">
                                        Students submit clearance requests and select course-assigned offices.
                                    </p>
                                </div>
                            </div>

                            <div class="group flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-green-200 hover:shadow-lg">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-green-50">
                                    <Users class="size-5 text-green-700" />
                                </div>
                                <div>
                                    <p class="font-black text-slate-900">Office Approval</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">
                                        Staff approve or reject requests assigned to their office only.
                                    </p>
                                </div>
                            </div>

                            <div class="group flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-purple-200 hover:shadow-lg">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-purple-50">
                                    <ShieldCheck class="size-5 text-purple-700" />
                                </div>
                                <div>
                                    <p class="font-black text-slate-900">Final Approval</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">
                                        The President gives final approval after all required offices approve.
                                    </p>
                                </div>
                            </div>

                            <div class="group flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-orange-200 hover:shadow-lg">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-orange-50">
                                    <QrCode class="size-5 text-orange-700" />
                                </div>
                                <div>
                                    <p class="font-black text-slate-900">Receipt Verification</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">
                                        Cleared students can print a receipt with QR-based public verification.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <section class="relative z-10 mx-auto grid max-w-7xl gap-4 px-6 pb-16 md:grid-cols-3">
            <div class="rounded-[1.75rem] border border-slate-200 bg-white/90 p-6 shadow-sm backdrop-blur transition hover:-translate-y-1 hover:shadow-xl">
                <div class="flex size-12 items-center justify-center rounded-2xl bg-green-50">
                    <CheckCircle class="size-7 text-green-700" />
                </div>
                <h3 class="mt-5 text-lg font-black text-slate-950">Role-Based Access</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Separate access for students, staff, admin, and president.
                </p>
            </div>

            <div class="rounded-[1.75rem] border border-slate-200 bg-white/90 p-6 shadow-sm backdrop-blur transition hover:-translate-y-1 hover:shadow-xl">
                <div class="flex size-12 items-center justify-center rounded-2xl bg-blue-50">
                    <FileText class="size-7 text-blue-700" />
                </div>
                <h3 class="mt-5 text-lg font-black text-slate-950">Reports and Monitoring</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Admin can monitor clearance progress, print reports, and export CSV data.
                </p>
            </div>

            <div class="rounded-[1.75rem] border border-slate-200 bg-white/90 p-6 shadow-sm backdrop-blur transition hover:-translate-y-1 hover:shadow-xl">
                <div class="flex size-12 items-center justify-center rounded-2xl bg-purple-50">
                    <ClipboardCheck class="size-7 text-purple-700" />
                </div>
                <h3 class="mt-5 text-lg font-black text-slate-950">Course Modules</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Clearance offices can be configured based on each course module.
                </p>
            </div>
        </section>
    </div>
</template>