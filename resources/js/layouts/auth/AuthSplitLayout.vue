<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CheckCircle, ClipboardCheck, FileCheck2, QrCode, ShieldCheck, Users } from 'lucide-vue-next';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-svh bg-slate-50 text-slate-950 lg:grid lg:grid-cols-[1.12fr_0.88fr]">
        <section class="relative hidden min-h-svh overflow-hidden bg-blue-950 lg:flex">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.55),_transparent_34%),radial-gradient(circle_at_80%_20%,_rgba(125,211,252,0.35),_transparent_30%),linear-gradient(135deg,_#0f172a_0%,_#1d4ed8_48%,_#172554_100%)]"></div>

            <div class="absolute inset-0 opacity-[0.08]">
                <div class="h-full w-full bg-[linear-gradient(to_right,_#ffffff_1px,_transparent_1px),linear-gradient(to_bottom,_#ffffff_1px,_transparent_1px)] bg-[size:52px_52px]"></div>
            </div>

            <div class="absolute -left-24 top-20 h-72 w-72 rounded-full bg-cyan-300/20 blur-3xl"></div>
            <div class="absolute -right-20 bottom-24 h-96 w-96 rounded-full bg-blue-300/20 blur-3xl"></div>

            <div class="relative z-10 flex w-full flex-col justify-between p-10 xl:p-14">
                <Link
                    :href="home()"
                    class="inline-flex w-fit items-center gap-4 rounded-3xl border border-white/15 bg-white/10 px-5 py-4 text-white shadow-2xl shadow-blue-950/20 backdrop-blur-xl transition hover:bg-white/15"
                >
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-white text-blue-700 shadow-xl">
                        <AppLogoIcon class="size-8 fill-current text-blue-700" />
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-100">
                            Talibon Polytechnic College
                        </p>
                        <p class="text-lg font-black tracking-tight text-white">
                            Student Clearance System
                        </p>
                    </div>
                </Link>

                <div class="max-w-2xl">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-blue-50 backdrop-blur">
                        <span class="size-2 rounded-full bg-cyan-300"></span>
                        Role-Based Clearance Platform
                    </div>

                    <h2 class="text-5xl font-black leading-[1.05] tracking-tight text-white xl:text-6xl">
                        Manage student clearance with confidence and control.
                    </h2>

                    <p class="mt-6 max-w-xl text-base leading-8 text-blue-50/90">
                        A web-based clearance system designed for students, office staff, administrators, and final approval by the College President.
                    </p>

                    <div class="mt-10 grid max-w-xl grid-cols-3 gap-3">
                        <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur-xl">
                            <p class="text-2xl font-black text-white">4</p>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-blue-100">
                                User Roles
                            </p>
                        </div>

                        <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur-xl">
                            <p class="text-2xl font-black text-white">QR</p>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-blue-100">
                                Verification
                            </p>
                        </div>

                        <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur-xl">
                            <p class="text-2xl font-black text-white">CSV</p>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-blue-100">
                                Reports
                            </p>
                        </div>
                    </div>

                    <div class="mt-10 rounded-[2rem] border border-white/15 bg-white/10 p-5 shadow-2xl shadow-blue-950/20 backdrop-blur-xl">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="flex gap-3 rounded-2xl bg-white/10 p-4">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-blue-700">
                                    <ClipboardCheck class="size-5" />
                                </div>
                                <div>
                                    <p class="text-sm font-black text-white">Student Request</p>
                                    <p class="mt-1 text-xs leading-5 text-blue-50/80">
                                        Submit and track clearance progress online.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-3 rounded-2xl bg-white/10 p-4">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-green-700">
                                    <Users class="size-5" />
                                </div>
                                <div>
                                    <p class="text-sm font-black text-white">Office Review</p>
                                    <p class="mt-1 text-xs leading-5 text-blue-50/80">
                                        Staff approve or reject assigned requests.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-3 rounded-2xl bg-white/10 p-4">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-purple-700">
                                    <ShieldCheck class="size-5" />
                                </div>
                                <div>
                                    <p class="text-sm font-black text-white">Final Approval</p>
                                    <p class="mt-1 text-xs leading-5 text-blue-50/80">
                                        President finalizes completed clearances.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-3 rounded-2xl bg-white/10 p-4">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-orange-700">
                                    <QrCode class="size-5" />
                                </div>
                                <div>
                                    <p class="text-sm font-black text-white">Public Verification</p>
                                    <p class="mt-1 text-xs leading-5 text-blue-50/80">
                                        Receipts can be verified through QR code.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm text-blue-100/80">
                    <span>Talibon Polytechnic College</span>
                    <span>Student Clearance System</span>
                </div>
            </div>
        </section>

        <section class="relative flex min-h-svh items-center justify-center overflow-hidden px-6 py-10 lg:px-10">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute right-[-8rem] top-[-8rem] h-80 w-80 rounded-full bg-blue-200/60 blur-3xl"></div>
                <div class="absolute bottom-[-10rem] left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-cyan-100/80 blur-3xl"></div>
            </div>

            <div class="relative z-10 w-full max-w-md">
                <div class="mb-7 text-center lg:hidden">
                    <Link :href="home()" class="inline-flex flex-col items-center gap-3">
                        <div class="flex size-14 items-center justify-center rounded-2xl bg-blue-700 text-white shadow-xl shadow-blue-700/25">
                            <AppLogoIcon class="size-8 fill-current text-white" />
                        </div>

                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-700">
                                Talibon Polytechnic College
                            </p>
                            <p class="text-lg font-black tracking-tight text-slate-950">
                                Student Clearance System
                            </p>
                        </div>
                    </Link>
                </div>

                <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-8 shadow-2xl shadow-slate-300/70 backdrop-blur-xl sm:p-10">
                    <div class="mb-8">
                        <div class="mb-5 flex items-center justify-center">
                            <div class="flex size-13 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                                <FileCheck2 class="size-7" />
                            </div>
                        </div>

                        <h1 v-if="title" class="text-center text-3xl font-black tracking-tight text-slate-950">
                            {{ title }}
                        </h1>

                        <p v-if="description" class="mt-3 text-center text-sm leading-6 text-slate-500">
                            {{ description }}
                        </p>
                    </div>

                    <slot />
                </div>

                <div class="mt-6 flex items-center justify-center gap-2 text-xs font-semibold text-slate-500">
                    <CheckCircle class="size-4 text-green-600" />
                    Secure role-based clearance access
                </div>
            </div>
        </section>
    </div>
</template>