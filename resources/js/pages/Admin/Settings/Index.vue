<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertTriangle, ArrowLeft, CalendarDays } from 'lucide-vue-next';
import { ref } from 'vue';

type SystemSettings = {
    active_semester: string;
    active_school_year: string;
};

const props = defineProps<{
    settings: SystemSettings;
}>();

const showTermModal = ref(false);
const confirmText = ref('');

const termForm = useForm({
    active_semester: props.settings.active_semester,
    active_school_year: props.settings.active_school_year,
});

const openTermModal = () => {
    termForm.active_semester = props.settings.active_semester;
    termForm.active_school_year = props.settings.active_school_year;
    confirmText.value = '';
    showTermModal.value = true;
};

const closeTermModal = () => {
    showTermModal.value = false;
    termForm.reset();
};

const submitTermChange = () => {
    if (confirmText.value !== 'RESET CLEARANCES') {
        return;
    }

    termForm.patch('/admin/settings', {
        onSuccess: () => {
            closeTermModal();
        },
    });
};
</script>

<template>
    <Head title="System Settings" />

    <div
        class="min-h-screen bg-linear-to-br from-slate-50 via-white to-blue-50/40 p-3 pb-28 text-slate-900 sm:p-4 sm:pb-28 md:p-6 md:pb-6"
    >
        <div class="mx-auto flex max-w-5xl flex-col gap-4 md:gap-6">
            <!-- Header section -->
            <div
                class="flex flex-col items-start gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-3">
                    <Link
                        href="/admin/dashboard"
                        class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-700"
                    >
                        <ArrowLeft class="size-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-black text-blue-950">
                            System Settings
                        </h1>
                        <p class="text-sm font-medium text-slate-500">
                            Manage global system configurations.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Settings Content -->
            <div class="grid gap-6">
                <!-- Academic Term Settings -->
                <section
                    class="overflow-hidden rounded-3xl border border-blue-200 bg-white/95 shadow-sm shadow-blue-200/70 md:rounded-4xl"
                >
                    <div
                        class="flex flex-col gap-4 border-b border-blue-100 bg-linear-to-r from-blue-50 to-white px-6 py-6 sm:px-8 md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <div class="mb-2 flex items-center gap-2">
                                <CalendarDays class="size-5 text-blue-600" />
                                <p
                                    class="text-xs font-black tracking-[0.18em] text-blue-600 uppercase"
                                >
                                    Academic Term
                                </p>
                            </div>

                            <h2 class="mt-1 text-2xl font-black text-blue-950">
                                Active Academic Term
                            </h2>

                            <p
                                class="mt-1 max-w-2xl text-sm font-medium text-slate-500"
                            >
                                The current semester and school year for all new
                                clearance requests. Changing the term will reset
                                the clearance cycle.
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="openTermModal"
                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-2xl bg-amber-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-amber-500/20 transition hover:-translate-y-0.5 hover:bg-amber-600 hover:shadow-xl sm:w-auto"
                        >
                            Change Term
                            <AlertTriangle class="size-4" />
                        </button>
                    </div>

                    <div
                        class="grid gap-0 divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0"
                    >
                        <div class="p-6 sm:p-8">
                            <p
                                class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                            >
                                Current Semester
                            </p>
                            <p class="mt-2 text-3xl font-black text-blue-950">
                                {{ settings.active_semester }}
                            </p>
                        </div>
                        <div class="p-6 sm:p-8">
                            <p
                                class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                            >
                                Current School Year
                            </p>
                            <p class="mt-2 text-3xl font-black text-blue-950">
                                {{ settings.active_school_year }}
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

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
                        <div
                            class="grid size-10 shrink-0 place-items-center rounded-full bg-amber-100 text-amber-600"
                        >
                            <AlertTriangle class="size-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-amber-950">
                                Change Academic Term
                            </h3>
                            <p class="text-sm font-medium text-amber-700">
                                DANGER: This action will reset clearances.
                            </p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submitTermChange" class="p-6">
                    <div
                        class="mb-6 rounded-2xl border border-red-100 bg-red-50 p-4"
                    >
                        <p class="text-sm font-bold text-red-800">
                            Warning: Updating the academic term will force all
                            students to submit new clearance requests for the
                            new term. Old clearance requests will remain in the
                            database for historical purposes but will no longer
                            be considered active.
                        </p>
                    </div>

                    <div class="grid gap-5">
                        <div>
                            <label
                                class="mb-2 block text-sm font-bold text-slate-700"
                            >
                                Active Semester
                            </label>
                            <select
                                v-model="termForm.active_semester"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                                required
                            >
                                <option value="1st Semester">
                                    1st Semester
                                </option>
                                <option value="2nd Semester">
                                    2nd Semester
                                </option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-bold text-slate-700"
                            >
                                School Year
                            </label>
                            <input
                                v-model="termForm.active_school_year"
                                type="text"
                                placeholder="e.g. 2026-2027"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                                required
                            />
                        </div>

                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                        >
                            <label
                                class="mb-2 block text-sm font-bold text-slate-700"
                            >
                                Type "RESET CLEARANCES" to confirm
                            </label>
                            <input
                                v-model="confirmText"
                                type="text"
                                placeholder="RESET CLEARANCES"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 placeholder:font-medium placeholder:text-slate-300 focus:border-red-500 focus:ring-4 focus:ring-red-500/20"
                                required
                            />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            @click="closeTermModal"
                            class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-100"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="
                                confirmText !== 'RESET CLEARANCES' ||
                                termForm.processing
                            "
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-red-600 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-red-700 disabled:opacity-50"
                        >
                            <AlertTriangle class="size-4" />
                            Change Term
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>
</template>
