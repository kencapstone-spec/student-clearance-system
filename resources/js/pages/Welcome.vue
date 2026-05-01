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

    <div class="min-h-screen bg-slate-950 text-white">
        <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6">
            <div>
                <p class="text-sm font-semibold text-blue-300">
                    Talibon Polytechnic College
                </p>
                <h1 class="text-xl font-bold">
                    Student Clearance System
                </h1>
            </div>

            <nav class="flex items-center gap-3">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                >
                    Dashboard
                </Link>

                <template v-else>
                    <Link
                        :href="login()"
                        class="rounded-xl border border-white/20 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10"
                    >
                        Log in
                    </Link>

                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </header>

        <main class="mx-auto grid max-w-7xl gap-10 px-6 py-16 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
            <section>
                <div class="mb-5 inline-flex rounded-full border border-blue-400/30 bg-blue-400/10 px-4 py-2 text-sm font-semibold text-blue-200">
                    Web-based Student Clearance System with Role-Based Approval
                </div>

                <h2 class="max-w-3xl text-4xl font-bold leading-tight md:text-6xl">
                    Faster, safer, and more organized student clearance processing.
                </h2>

                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                    This system helps students submit clearance requests online, allows assigned offices to approve or reject requests, enables final approval by the College President, and provides printable receipts with QR verification.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="rounded-xl bg-blue-600 px-6 py-3 text-center text-sm font-bold text-white hover:bg-blue-700"
                    >
                        Go to Dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-xl bg-blue-600 px-6 py-3 text-center text-sm font-bold text-white hover:bg-blue-700"
                        >
                            Log in to Continue
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="rounded-xl border border-white/20 px-6 py-3 text-center text-sm font-bold text-white hover:bg-white/10"
                        >
                            Register as Student
                        </Link>
                    </template>
                </div>
            </section>

            <section class="rounded-3xl border border-white/10 bg-white/10 p-6 shadow-2xl backdrop-blur">
                <div class="rounded-2xl bg-white p-6 text-slate-900">
                    <p class="text-sm font-semibold text-blue-700">
                        Core Workflow
                    </p>

                    <h3 class="mt-2 text-2xl font-bold text-blue-950">
                        Clearance Process
                    </h3>

                    <div class="mt-6 space-y-4">
                        <div class="flex gap-3 rounded-xl border bg-slate-50 p-4">
                            <ClipboardCheck class="mt-1 size-5 text-blue-600" />
                            <div>
                                <p class="font-semibold">Student Request</p>
                                <p class="text-sm text-slate-600">
                                    Students submit clearance requests and select course-assigned offices.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3 rounded-xl border bg-slate-50 p-4">
                            <Users class="mt-1 size-5 text-green-600" />
                            <div>
                                <p class="font-semibold">Office Approval</p>
                                <p class="text-sm text-slate-600">
                                    Staff approve or reject requests assigned to their office only.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3 rounded-xl border bg-slate-50 p-4">
                            <ShieldCheck class="mt-1 size-5 text-purple-600" />
                            <div>
                                <p class="font-semibold">Final Approval</p>
                                <p class="text-sm text-slate-600">
                                    The President gives final approval after all required offices approve.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3 rounded-xl border bg-slate-50 p-4">
                            <QrCode class="mt-1 size-5 text-orange-600" />
                            <div>
                                <p class="font-semibold">Receipt Verification</p>
                                <p class="text-sm text-slate-600">
                                    Cleared students can print a receipt with QR-based public verification.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <section class="mx-auto grid max-w-7xl gap-4 px-6 pb-16 md:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                <CheckCircle class="size-7 text-green-300" />
                <h3 class="mt-4 font-bold">Role-Based Access</h3>
                <p class="mt-2 text-sm text-slate-300">
                    Separate access for students, staff, admin, and president.
                </p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                <FileText class="size-7 text-blue-300" />
                <h3 class="mt-4 font-bold">Reports and Monitoring</h3>
                <p class="mt-2 text-sm text-slate-300">
                    Admin can monitor clearance progress, print reports, and export CSV data.
                </p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                <ClipboardCheck class="size-7 text-purple-300" />
                <h3 class="mt-4 font-bold">Course Modules</h3>
                <p class="mt-2 text-sm text-slate-300">
                    Clearance offices can be configured based on each course module.
                </p>
            </div>
        </section>
    </div>
</template>