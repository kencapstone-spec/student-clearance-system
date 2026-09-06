<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    CheckCircle,
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
                        <p
                            class="text-sm font-black tracking-tight text-white"
                        >
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

                    <div class="mt-6 grid max-w-xl grid-cols-3 gap-2.5 xl:gap-3">
                        <div
                            class="rounded-2xl border border-white/15 bg-white/10 p-3 xl:p-3.5 backdrop-blur-xl"
                        >
                            <p class="text-xl font-black text-white xl:text-2xl">4</p>
                            <p
                                class="mt-0.5 text-[11px] font-semibold tracking-wide text-blue-100 uppercase"
                            >
                                User Roles
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/15 bg-white/10 p-3 xl:p-3.5 backdrop-blur-xl"
                        >
                            <p class="text-xl font-black text-white xl:text-2xl">QR</p>
                            <p
                                class="mt-0.5 text-[11px] font-semibold tracking-wide text-blue-100 uppercase"
                            >
                                Verification
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/15 bg-white/10 p-3 xl:p-3.5 backdrop-blur-xl"
                        >
                            <p class="text-xl font-black text-white xl:text-2xl">CSV</p>
                            <p
                                class="mt-0.5 text-[11px] font-semibold tracking-wide text-blue-100 uppercase"
                            >
                                Reports
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-6 rounded-2xl border border-white/15 bg-white/10 p-3.5 xl:p-4 shadow-xl shadow-blue-950/20 backdrop-blur-xl"
                    >
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <div class="flex gap-2.5 rounded-xl bg-white/10 p-3">
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

                            <div class="flex gap-2.5 rounded-xl bg-white/10 p-3">
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

                            <div class="flex gap-2.5 rounded-xl bg-white/10 p-3">
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

                            <div class="flex gap-2.5 rounded-xl bg-white/10 p-3">
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
            class="relative flex min-h-svh w-full items-center justify-center overflow-y-auto px-4 py-6 sm:px-6 sm:py-8 lg:h-svh lg:overflow-y-auto lg:px-8 xl:px-12"
        >
            <div class="pointer-events-none absolute inset-0">
                <div
                    class="absolute top-[-8rem] right-[-8rem] h-80 w-80 rounded-full bg-blue-200/60 blur-3xl"
                ></div>
                <div
                    class="absolute bottom-[-10rem] left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-cyan-100/80 blur-3xl"
                ></div>
            </div>

            <div class="relative z-10 my-auto w-full max-w-md sm:max-w-lg lg:max-w-xl">
                <div class="mb-4 text-center lg:hidden">
                    <Link
                        :href="home()"
                        class="inline-flex items-center gap-2.5 rounded-2xl border border-slate-200/80 bg-white/90 px-3.5 py-2 shadow-xs backdrop-blur"
                    >
                        <div
                            class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-blue-700 text-white shadow-sm"
                        >
                            <AppLogoIcon
                                class="size-5 fill-current text-white"
                            />
                        </div>

                        <div class="text-left">
                            <p
                                class="text-[9px] font-black tracking-wider text-blue-700 uppercase"
                            >
                                Talibon Polytechnic College
                            </p>
                            <p
                                class="text-xs font-black tracking-tight text-slate-900"
                            >
                                Student Clearance System
                            </p>
                        </div>
                    </Link>
                </div>

                <div
                    class="rounded-2xl border border-slate-200/90 bg-white/95 p-5 shadow-xl shadow-slate-200/60 backdrop-blur-xl sm:rounded-3xl sm:p-7 xl:p-8"
                >
                    <div class="mb-4 sm:mb-5">
                        <div class="mb-2.5 flex items-center justify-center">
                            <div
                                class="flex size-9 sm:size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700"
                            >
                                <FileCheck2 class="size-5" />
                            </div>
                        </div>

                        <h1
                            v-if="title"
                            class="text-center text-xl sm:text-2xl font-black tracking-tight text-slate-950"
                        >
                            {{ title }}
                        </h1>

                        <p
                            v-if="description"
                            class="mt-1 text-center text-xs sm:text-sm leading-5 text-slate-500"
                        >
                            {{ description }}
                        </p>
                    </div>

                    <slot />
                </div>

                <div
                    class="mt-3.5 flex items-center justify-center gap-1.5 text-xs font-semibold text-slate-400 sm:mt-4"
                >
                    <CheckCircle class="size-3.5 text-green-600" />
                    Secure role-based clearance access
                </div>
            </div>
        </section>
    </div>
</template>
