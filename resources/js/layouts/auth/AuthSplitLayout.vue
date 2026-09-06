<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ClipboardCheck,
    FileCheck2,
    QrCode,
    ShieldCheck,
    Users,
} from 'lucide-vue-next';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="min-h-svh bg-slate-50 text-slate-950 lg:grid lg:h-svh lg:grid-cols-[1.08fr_0.92fr] lg:overflow-hidden"
    >
        <section
            class="relative hidden h-full overflow-hidden bg-blue-950 lg:flex"
        >
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.55),_transparent_34%),radial-gradient(circle_at_80%_20%,_rgba(125,211,252,0.35),_transparent_30%),linear-gradient(135deg,_#0f172a_0%,_#1d4ed8_48%,_#172554_100%)]"
            ></div>

            <div class="absolute inset-0 opacity-[0.08]">
                <div
                    class="h-full w-full bg-[linear-gradient(to_right,_#ffffff_1px,_transparent_1px),linear-gradient(to_bottom,_#ffffff_1px,_transparent_1px)] bg-[size:52px_52px]"
                ></div>
            </div>

            <div
                class="absolute top-20 -left-24 h-72 w-72 rounded-full bg-cyan-300/20 blur-3xl"
            ></div>
            <div
                class="absolute -right-20 bottom-24 h-96 w-96 rounded-full bg-blue-300/20 blur-3xl"
            ></div>

            <div
                class="relative z-10 flex h-full w-full flex-col justify-between p-6 xl:p-10"
            >
                <Link
                    :href="home()"
                    class="inline-flex w-fit items-center gap-3 rounded-2xl border border-white/10 bg-white/10 px-3.5 py-2.5 text-white shadow-xl shadow-blue-950/20 backdrop-blur-xl transition hover:bg-white/15"
                >
                    <div
                        class="flex size-9 items-center justify-center rounded-xl bg-white text-blue-700 shadow-lg"
                    >
                        <AppLogoIcon
                            class="size-5.5 fill-current text-blue-700"
                        />
                    </div>

                    <div>
                        <p
                            class="text-[0.65rem] font-black tracking-[0.18em] text-blue-100 uppercase"
                        >
                            Talibon Polytechnic College
                        </p>
                        <p class="text-sm font-black tracking-tight text-white">
                            Student Clearance System
                        </p>
                    </div>
                </Link>

                <div class="max-w-2xl py-4">
                    <div
                        class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1.5 text-[0.65rem] font-black tracking-[0.16em] text-blue-50 uppercase backdrop-blur"
                    >
                        <span class="size-2 rounded-full bg-cyan-300"></span>
                        Role-Based Clearance Platform
                    </div>

                    <h2
                        class="text-4xl leading-[1.08] font-black tracking-tight text-white xl:text-5xl"
                    >
                        Manage student clearance with confidence and control.
                    </h2>

                    <p
                        class="mt-4 max-w-xl text-sm leading-7 text-blue-50/90 xl:text-base"
                    >
                        A web-based clearance system designed for students,
                        office staff, administrators, and final approval by the
                        College President.
                    </p>

                    <div
                        class="mt-6 grid max-w-xl grid-cols-3 gap-2.5 xl:gap-3"
                    >
                        <div
                            class="rounded-2xl border border-white/15 bg-white/10 p-3 backdrop-blur-xl xl:p-3.5"
                        >
                            <p
                                class="text-xl font-black text-white xl:text-2xl"
                            >
                                4
                            </p>
                            <p
                                class="mt-0.5 text-[11px] font-semibold tracking-wide text-blue-100 uppercase"
                            >
                                User Roles
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/15 bg-white/10 p-3 backdrop-blur-xl xl:p-3.5"
                        >
                            <p
                                class="text-xl font-black text-white xl:text-2xl"
                            >
                                QR
                            </p>
                            <p
                                class="mt-0.5 text-[11px] font-semibold tracking-wide text-blue-100 uppercase"
                            >
                                Verification
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/15 bg-white/10 p-3 backdrop-blur-xl xl:p-3.5"
                        >
                            <p
                                class="text-xl font-black text-white xl:text-2xl"
                            >
                                CSV
                            </p>
                            <p
                                class="mt-0.5 text-[11px] font-semibold tracking-wide text-blue-100 uppercase"
                            >
                                Reports
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-6 rounded-2xl border border-white/15 bg-white/10 p-3.5 shadow-xl shadow-blue-950/20 backdrop-blur-xl xl:p-4"
                    >
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <div
                                class="flex gap-2.5 rounded-xl bg-white/10 p-3"
                            >
                                <div
                                    class="flex size-8.5 shrink-0 items-center justify-center rounded-lg bg-white text-blue-700"
                                >
                                    <ClipboardCheck class="size-4.5" />
                                </div>
                                <div>
                                    <p class="text-xs font-black text-white">
                                        Student Request
                                    </p>
                                    <p
                                        class="mt-0.5 text-[11px] leading-4 text-blue-50/80"
                                    >
                                        Submit and track clearance progress
                                        online.
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex gap-2.5 rounded-xl bg-white/10 p-3"
                            >
                                <div
                                    class="flex size-8.5 shrink-0 items-center justify-center rounded-lg bg-white text-green-700"
                                >
                                    <Users class="size-4.5" />
                                </div>
                                <div>
                                    <p class="text-xs font-black text-white">
                                        Office Review
                                    </p>
                                    <p
                                        class="mt-0.5 text-[11px] leading-4 text-blue-50/80"
                                    >
                                        Staff approve or reject assigned
                                        requests.
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex gap-2.5 rounded-xl bg-white/10 p-3"
                            >
                                <div
                                    class="flex size-8.5 shrink-0 items-center justify-center rounded-lg bg-white text-purple-700"
                                >
                                    <ShieldCheck class="size-4.5" />
                                </div>
                                <div>
                                    <p class="text-xs font-black text-white">
                                        Final Approval
                                    </p>
                                    <p
                                        class="mt-0.5 text-[11px] leading-4 text-blue-50/80"
                                    >
                                        President finalizes completed
                                        clearances.
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex gap-2.5 rounded-xl bg-white/10 p-3"
                            >
                                <div
                                    class="flex size-8.5 shrink-0 items-center justify-center rounded-lg bg-white text-orange-700"
                                >
                                    <QrCode class="size-4.5" />
                                </div>
                                <div>
                                    <p class="text-xs font-black text-white">
                                        Public Verification
                                    </p>
                                    <p
                                        class="mt-0.5 text-[11px] leading-4 text-blue-50/80"
                                    >
                                        Receipts can be verified through QR
                                        code.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between text-xs text-blue-100/80"
                >
                    <span>Talibon Polytechnic College</span>
                    <span>Student Clearance System</span>
                </div>
            </div>
        </section>

        <section
            class="relative flex min-h-svh w-full items-center justify-center overflow-x-hidden overflow-y-auto bg-gradient-to-br from-slate-50 via-blue-50/20 to-indigo-50/30 px-4 py-6 sm:px-6 sm:py-8 lg:h-svh lg:overflow-y-auto lg:px-8 xl:px-12"
        >
            <!-- Background atmosphere with contained ambient glows -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div
                    class="absolute inset-0 bg-[radial-gradient(#94a3b8_1px,transparent_1px)] [background-size:24px_24px] opacity-25"
                ></div>
                <div
                    class="absolute -top-32 -right-32 h-80 w-80 rounded-full bg-blue-400/15 blur-3xl"
                ></div>
                <div
                    class="absolute -bottom-32 -left-32 h-80 w-80 rounded-full bg-indigo-400/15 blur-3xl"
                ></div>
            </div>

            <div
                class="relative z-10 my-auto w-full max-w-md sm:max-w-lg lg:max-w-xl"
            >
                <!-- Compact modern mobile branding -->
                <div class="mb-4 text-center lg:hidden">
                    <Link
                        :href="home()"
                        class="inline-flex items-center gap-3 rounded-2xl border border-slate-200/90 bg-white/95 px-3.5 py-2 shadow-sm backdrop-blur"
                    >
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-700 text-white shadow-md shadow-blue-700/25"
                        >
                            <AppLogoIcon
                                class="size-5.5 fill-current text-white"
                            />
                        </div>

                        <div class="text-left">
                            <div class="flex items-center gap-1.5">
                                <p
                                    class="text-[9px] font-black tracking-wider text-blue-700 uppercase"
                                >
                                    Talibon Polytechnic College
                                </p>
                                <span
                                    class="size-1.5 rounded-full bg-emerald-500 ring-2 ring-emerald-200"
                                ></span>
                            </div>
                            <p
                                class="text-xs font-black tracking-tight text-slate-900"
                            >
                                Student Clearance System
                            </p>
                        </div>
                    </Link>
                </div>

                <!-- Modern Glassmorphism Card -->
                <div
                    class="relative rounded-3xl border border-white/90 bg-white/95 p-6 shadow-[0_20px_60px_-15px_rgba(15,23,42,0.12),0_0_0_1px_rgba(255,255,255,0.8)_inset] ring-1 ring-slate-900/5 backdrop-blur-2xl sm:p-7 xl:p-8"
                >
                    <!-- Top subtle accent bar -->
                    <div
                        class="absolute inset-x-0 top-0 h-1 rounded-t-3xl bg-gradient-to-r from-blue-600 via-indigo-600 to-cyan-500"
                    ></div>

                    <div class="mb-4 sm:mb-5">
                        <div class="mb-3 flex items-center justify-center">
                            <div
                                class="flex size-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-700 text-white shadow-lg ring-4 shadow-blue-600/30 ring-blue-50 sm:size-12"
                            >
                                <FileCheck2 class="size-5.5 sm:size-6" />
                            </div>
                        </div>

                        <h1
                            v-if="title"
                            class="text-center text-xl font-black tracking-tight text-slate-900 sm:text-2xl"
                        >
                            {{ title }}
                        </h1>

                        <p
                            v-if="description"
                            class="mt-1 text-center text-xs leading-5 font-medium text-slate-500 sm:text-sm"
                        >
                            {{ description }}
                        </p>
                    </div>

                    <slot />
                </div>

                <!-- Modern Trust Footer -->
                <div
                    class="mt-3.5 flex items-center justify-center gap-1.5 text-xs font-semibold text-slate-400 sm:mt-4"
                >
                    <ShieldCheck class="size-4 text-emerald-600" />
                    <span
                        >Official Clearance Portal • Secure Role-Based
                        Access</span
                    >
                </div>
            </div>
        </section>
    </div>
</template>
