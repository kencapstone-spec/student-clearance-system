<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Network,
    Pencil,
    RotateCcw,
    Save,
    ShieldCheck,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Office = {
    id: number;
    name: string;
    group: string;
    sort_order: number;
    is_final_approver: boolean;
    prerequisites?: { id: number; name: string }[];
};

const props = defineProps<{
    offices: Office[];
}>();

const selectedOffice = ref<Office | null>(null);
const showEditModal = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const form = useForm<{
    prerequisite_office_ids: number[];
}>({
    prerequisite_office_ids: [],
});

const selectedPrerequisiteCount = computed(() => form.prerequisite_office_ids.length);

const availablePrerequisites = computed(() => {
    if (!selectedOffice.value) return [];
    // An office cannot be a prerequisite of itself
    return props.offices.filter(o => o.id !== selectedOffice.value?.id);
});

const openEditModal = (office: Office) => {
    selectedOffice.value = office;
    form.prerequisite_office_ids = (office.prerequisites || []).map(p => p.id);

    form.clearErrors();
    errorMessage.value = '';
    successMessage.value = '';
    showEditModal.value = true;
};

const closeEditModal = (force = false) => {
    if (form.processing && !force) {
        return;
    }

    selectedOffice.value = null;
    form.reset();
    form.clearErrors();
    errorMessage.value = '';
    showEditModal.value = false;
};

const savePrerequisites = () => {
    if (!selectedOffice.value) return;

    const officeName = selectedOffice.value.name;

    form.patch(`/admin/office-prerequisites/${selectedOffice.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = `Prerequisites for ${officeName} updated successfully.`;
            closeEditModal(true);
        },
        onError: () => {
            errorMessage.value = 'Unable to update prerequisites. Please check your selections.';
        },
    });
};
</script>

<template>
    <Head title="Office Prerequisites" />

    <div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-blue-50/40 p-3 pb-28 text-slate-900 sm:p-4 sm:pb-28 md:p-6 md:pb-6">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 md:gap-6">
            <!-- Hero -->
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-xl shadow-slate-200/70 md:rounded-4xl">
                <div class="grid gap-6 p-4 sm:p-6 lg:grid-cols-[1fr_300px] lg:p-8">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-2 text-[0.65rem] font-black tracking-[0.14em] text-blue-700 uppercase sm:px-4 sm:text-xs sm:tracking-[0.18em]">
                            <ShieldCheck class="size-4" />
                            Admin / OSAS Director Panel
                        </div>

                        <div class="mt-5 flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                            <div>
                                <h1 class="text-3xl font-black tracking-tight text-blue-950 sm:text-4xl">
                                    Office Prerequisites
                                </h1>

                                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base sm:leading-7">
                                    Configure which offices must be cleared before a student can request clearance from another office. This enforces a strict built-in order for the clearance process.
                                </p>
                            </div>

                            <Link href="/admin/dashboard" class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black whitespace-nowrap text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-md sm:w-auto">
                                <LayoutDashboard class="size-4" />
                                Back to Dashboard
                            </Link>
                        </div>

                        <div v-if="successMessage" class="mt-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-black text-green-700">
                            {{ successMessage }}
                        </div>
                    </div>

                    <div class="hidden items-center justify-center lg:flex">
                        <div class="relative grid h-52 w-52 place-items-center rounded-4xl border border-blue-100 bg-linear-to-br from-white to-blue-50 shadow-2xl shadow-slate-300/70">
                            <div class="grid h-20 w-20 place-items-center rounded-3xl bg-blue-700 text-white shadow-xl shadow-blue-700/25">
                                <Network class="size-10" />
                            </div>

                            <p class="text-center text-sm font-black tracking-[0.18em] text-blue-700 uppercase">
                                Flow Control
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Office Cards -->
            <section class="grid gap-4 xl:grid-cols-2 xl:gap-5">
                <div v-for="office in offices" :key="office.id" class="overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:rounded-4xl">
                    <div class="border-b border-slate-200 p-4 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-black tracking-[0.18em] text-blue-700 uppercase">
                                    {{ office.group }}
                                </p>

                                <h2 class="mt-2 text-xl leading-tight font-black text-blue-950 sm:text-2xl">
                                    {{ office.name }}
                                </h2>
                            </div>

                            <span class="shrink-0 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">
                                {{ (office.prerequisites || []).length }} Prerequisites
                            </span>
                        </div>

                        <div class="mt-5 flex justify-end">
                            <button type="button" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-2xl bg-blue-700 px-4 py-3 text-sm font-black text-white shadow-md shadow-blue-700/20 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-lg sm:w-auto" @click="openEditModal(office)">
                                <Pencil class="size-4" />
                                Edit Prerequisites
                            </button>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-black text-blue-950">
                                    Required Before This Office
                                </p>
                                <p class="mt-1 text-sm font-medium text-slate-500">
                                    Students must clear these offices first.
                                </p>
                            </div>
                        </div>

                        <div v-if="!office.prerequisites || office.prerequisites.length === 0" class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                            <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-blue-50 text-blue-700">
                                <Network class="size-7" />
                            </div>
                            <p class="mt-3 font-black text-slate-700">No prerequisites set.</p>
                            <p class="mt-1 text-sm font-medium text-slate-500">Students can request this office immediately.</p>
                        </div>

                        <ul v-else class="space-y-3">
                            <li v-for="prereq in office.prerequisites" :key="prereq.id" class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 px-3 py-3 text-sm transition hover:border-blue-200 hover:bg-blue-50/70 sm:gap-4 sm:px-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-white text-blue-700 shadow-sm">
                                        <ShieldCheck class="size-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-black text-blue-950">{{ prereq.name }}</p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal && selectedOffice" class="fixed inset-0 z-40 flex items-end justify-center bg-slate-950/50 p-3 backdrop-blur-sm sm:items-center sm:p-4" @click.self="closeEditModal()">
        <div class="flex max-h-[92dvh] w-full max-w-3xl flex-col overflow-hidden rounded-t-4xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20 sm:max-h-[90vh] sm:rounded-4xl">
            <div class="shrink-0 border-b border-slate-200 p-4 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-start gap-3 sm:gap-4">
                        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-blue-50 text-blue-700 sm:h-12 sm:w-12">
                            <Network class="size-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black tracking-[0.18em] text-blue-700 uppercase">
                                Edit Prerequisites
                            </p>
                            <h2 class="mt-1 text-lg leading-tight font-black text-blue-950 sm:text-2xl">
                                {{ selectedOffice.name }}
                            </h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Select the offices that a student must clear before they can request clearance from {{ selectedOffice.name }}.
                            </p>
                        </div>
                    </div>
                    <button type="button" class="grid size-10 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900" @click="closeEditModal()">
                        <X class="size-5" />
                    </button>
                </div>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6">
                <div v-if="errorMessage || form.errors.prerequisite_office_ids" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-black text-red-700">
                    {{ errorMessage || form.errors.prerequisite_office_ids }}
                </div>

                <div class="mt-2 flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                    <span class="font-black text-slate-700">Selected Prerequisites</span>
                    <span class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">
                        {{ selectedPrerequisiteCount }} selected
                    </span>
                </div>

                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    <label v-for="office in availablePrerequisites" :key="office.id" class="flex min-h-16 cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm transition hover:border-blue-200 hover:bg-blue-50/60">
                        <input v-model="form.prerequisite_office_ids" type="checkbox" :value="office.id" class="mt-1 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                        <span class="min-w-0 flex-1">
                            <span class="block font-black text-blue-950">{{ office.name }}</span>
                            <span class="mt-0.5 block text-xs font-medium text-slate-500">{{ office.group }}</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="shrink-0 border-t border-slate-200 bg-white p-4 sm:p-6">
                <div class="grid grid-cols-2 gap-3 sm:flex sm:justify-end">
                    <button type="button" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50" :disabled="form.processing" @click="closeEditModal()">
                        <RotateCcw class="size-4" />
                        Cancel
                    </button>
                    <button type="button" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl bg-blue-700 px-4 py-3 text-sm font-black text-white shadow-md shadow-blue-700/20 transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:bg-blue-300" :disabled="form.processing" @click="savePrerequisites">
                        <Save class="size-4" />
                        {{ form.processing ? 'Saving...' : 'Save Prerequisites' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
