<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    Building2,
    CheckCircle2,
    FileCog,
    LayoutDashboard,
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
};

type Course = {
    id: number;
    code: string;
    name: string;
    offices: Office[];
};

defineProps<{
    courses: Course[];
    offices: Office[];
}>();

const selectedCourse = ref<Course | null>(null);
const showEditModal = ref(false);
const showRemoveConfirmModal = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const form = useForm<{
    office_ids: number[];
}>({
    office_ids: [],
});

const selectedOfficeCount = computed(() => form.office_ids.length);

const removedOffices = computed(() => {
    if (!selectedCourse.value) {
        return [];
    }

    return selectedCourse.value.offices.filter((office) => {
        return !office.is_final_approver && !form.office_ids.includes(office.id);
    });
});

const openEditModal = (course: Course) => {
    selectedCourse.value = course;
    form.office_ids = course.offices
        .filter((office) => !office.is_final_approver)
        .map((office) => office.id);

    form.clearErrors();
    errorMessage.value = '';
    successMessage.value = '';
    showRemoveConfirmModal.value = false;
    showEditModal.value = true;
};

const closeEditModal = (force = false) => {
    if (form.processing && !force) {
        return;
    }

    selectedCourse.value = null;
    form.reset();
    form.clearErrors();
    errorMessage.value = '';
    showEditModal.value = false;
    showRemoveConfirmModal.value = false;
};

const submitAssignments = () => {
    if (!selectedCourse.value) {
        return;
    }

    errorMessage.value = '';
    successMessage.value = '';
    form.clearErrors();

    if (form.office_ids.length === 0) {
        errorMessage.value =
            'Select at least one regular office for this course module.';

        return;
    }

    if (removedOffices.value.length > 0) {
        showRemoveConfirmModal.value = true;

        return;
    }

    saveAssignments();
};

const saveAssignments = () => {
    if (!selectedCourse.value) {
        return;
    }

    const courseCode = selectedCourse.value.code;

    showRemoveConfirmModal.value = false;

    form.patch(`/admin/course-modules/${selectedCourse.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = `${courseCode} office assignments updated successfully.`;
            closeEditModal(true);
        },
        onError: () => {
            errorMessage.value =
                'Unable to update course module offices. Please check the selected offices.';
        },
    });
};
</script>

<template>
    <Head title="Course Modules" />

    <div
        class="min-h-screen bg-linear-to-br from-slate-50 via-white to-blue-50/40 p-4 text-slate-900 md:p-6"
    >
        <div class="mx-auto flex max-w-7xl flex-col gap-6">
            <!-- Hero -->
            <section
                class="overflow-hidden rounded-4xl border border-slate-200 bg-white/95 shadow-xl shadow-slate-200/70"
            >
                <div class="grid gap-8 p-6 lg:grid-cols-[1fr_300px] lg:p-8">
                    <div>
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-blue-700"
                        >
                            <ShieldCheck class="size-4" />
                            Admin / OSAS Director Panel
                        </div>

                        <div
                            class="mt-5 flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between"
                        >
                            <div>
                                <h1
                                    class="text-4xl font-black tracking-tight text-blue-950"
                                >
                                    Course Modules
                                </h1>

                                <p
                                    class="mt-3 max-w-3xl text-base leading-7 text-slate-600"
                                >
                                    View and configure the clearance offices
                                    assigned to each course module. These
                                    assignments control which offices students
                                    must clear for future requests.
                                </p>
                            </div>

                            <Link
                                href="/admin/dashboard"
                                class="inline-flex w-fit items-center gap-2 whitespace-nowrap rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-md"
                            >
                                <LayoutDashboard class="size-4" />
                                Back to Dashboard
                            </Link>
                        </div>

                        <div
                            class="mt-5 rounded-2xl border border-amber-200 bg-amber-50/90 p-4 text-sm font-medium leading-6 text-amber-900"
                        >
                            Changes made here apply only to future or newly
                            created clearance requests. Existing clearance
                            records, receipts, reports, QR verification,
                            remarks, and approval history must remain unchanged.
                        </div>

                        <div
                            v-if="successMessage"
                            class="mt-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-black text-green-700"
                        >
                            {{ successMessage }}
                        </div>
                    </div>

                    <div class="hidden items-center justify-center lg:flex">
                        <div
                            class="relative grid h-52 w-52 place-items-center rounded-4xl border border-blue-100 bg-linear-to-br from-white to-blue-50 shadow-2xl shadow-slate-300/70"
                        >
                            <div
                                class="grid h-20 w-20 place-items-center rounded-3xl bg-blue-700 text-white shadow-xl shadow-blue-700/25"
                            >
                                <FileCog class="size-10" />
                            </div>

                            <p
                                class="text-center text-sm font-black uppercase tracking-[0.18em] text-blue-700"
                            >
                                Module Center
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Course Cards -->
            <section class="grid gap-5 xl:grid-cols-2">
                <div
                    v-for="course in courses"
                    :key="course.id"
                    class="overflow-hidden rounded-4xl border border-slate-200 bg-white/95 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="border-b border-slate-200 p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-xs font-black uppercase tracking-[0.18em] text-blue-700"
                                >
                                    {{ course.code }} Module
                                </p>

                                <h2
                                    class="mt-2 text-2xl font-black leading-tight text-blue-950"
                                >
                                    {{ course.name }}
                                </h2>
                            </div>

                            <span
                                class="shrink-0 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-black text-blue-700"
                            >
                                {{ course.offices.length }} Offices
                            </span>
                        </div>

                        <div class="mt-5 flex justify-end">
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-2xl bg-blue-700 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-blue-700/20 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-lg"
                                @click="openEditModal(course)"
                            >
                                <Pencil class="size-4" />
                                Edit Offices
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-black text-blue-950"
                                >
                                    Assigned Clearance Offices
                                </p>

                                <p class="mt-1 text-sm font-medium text-slate-500">
                                    Offices currently required for this course.
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="course.offices.length === 0"
                            class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center"
                        >
                            <div
                                class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-blue-50 text-blue-700"
                            >
                                <Building2 class="size-7" />
                            </div>

                            <p class="mt-3 font-black text-slate-700">
                                No offices assigned yet.
                            </p>

                            <p class="mt-1 text-sm font-medium text-slate-500">
                                Edit this module to assign required offices.
                            </p>
                        </div>

                        <ul v-else class="space-y-3">
                            <li
                                v-for="office in course.offices"
                                :key="office.id"
                                class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm transition hover:border-blue-200 hover:bg-blue-50/70"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-white text-blue-700 shadow-sm"
                                    >
                                        <Building2 class="size-5" />
                                    </div>

                                    <div>
                                        <p class="font-black text-blue-950">
                                            {{ office.name }}
                                        </p>

                                        <p class="text-xs font-medium text-slate-500">
                                            {{ office.group }}
                                        </p>
                                    </div>
                                </div>

                                <span
                                    v-if="office.is_final_approver"
                                    class="rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs font-black text-green-700"
                                >
                                    Final
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>
        </div>

        <!-- Edit Modal -->
        <div
            v-if="showEditModal && selectedCourse"
            class="fixed inset-0 z-40 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
            @click.self="closeEditModal()"
        >
            <div
                class="max-h-[90vh] w-full max-w-3xl overflow-hidden rounded-4xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20"
            >
                <div
                    class="flex items-start justify-between gap-4 border-b border-slate-200 p-6"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-blue-50 text-blue-700"
                        >
                            <FileCog class="size-6" />
                        </div>

                        <div>
                            <p
                                class="text-xs font-black uppercase tracking-[0.18em] text-blue-700"
                            >
                                Edit Course Module
                            </p>

                            <h2
                                class="mt-1 text-2xl font-black leading-tight text-blue-950"
                            >
                                {{ selectedCourse.code }} -
                                {{ selectedCourse.name }}
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Select the regular offices required for this
                                course. The President final approval is handled
                                separately and is not editable here.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="grid size-10 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                        @click="closeEditModal()"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <div class="max-h-[75vh] overflow-y-auto p-6">
                    <div
                        class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-medium leading-6 text-amber-900"
                    >
                        Removing an office from a course module will not delete
                        the office and will not change old clearance records. It
                        only affects future or newly created clearance requests
                        for this course.
                    </div>

                    <div
                        v-if="errorMessage || form.errors.office_ids"
                        class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-black text-red-700"
                    >
                        {{ errorMessage || form.errors.office_ids }}
                    </div>

                    <div
                        class="mt-5 flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm"
                    >
                        <span class="font-black text-slate-700">
                            Selected Offices
                        </span>

                        <span
                            class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-black text-blue-700"
                        >
                            {{ selectedOfficeCount }} selected
                        </span>
                    </div>

                    <div class="mt-4 max-h-96 space-y-2 overflow-y-auto pr-1">
                        <label
                            v-for="office in offices"
                            :key="office.id"
                            class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm transition hover:border-blue-200 hover:bg-blue-50/60"
                        >
                            <input
                                v-model="form.office_ids"
                                type="checkbox"
                                :value="office.id"
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />

                            <span class="flex-1">
                                <span class="block font-black text-blue-950">
                                    {{ office.name }}
                                </span>

                                <span
                                    class="mt-0.5 block text-xs font-medium text-slate-500"
                                >
                                    {{ office.group }}
                                </span>
                            </span>

                            <span
                                v-if="office.is_final_approver"
                                class="rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs font-black text-green-700"
                            >
                                Final
                            </span>
                        </label>
                    </div>

                    <div
                        class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="form.processing"
                            @click="closeEditModal()"
                        >
                            <RotateCcw class="size-4" />
                            Cancel
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-700 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-blue-700/20 transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:bg-blue-300"
                            :disabled="form.processing"
                            @click="submitAssignments"
                        >
                            <Save class="size-4" />
                            {{
                                form.processing
                                    ? 'Saving...'
                                    : 'Save Assignments'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remove Confirmation Modal -->
        <div
            v-if="showRemoveConfirmModal && selectedCourse"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
        >
            <div
                class="w-full max-w-lg rounded-4xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-900/20"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-red-50 text-red-600"
                    >
                        <AlertTriangle class="size-6" />
                    </div>

                    <div>
                        <h2 class="text-xl font-black text-red-700">
                            Confirm Office Removal
                        </h2>

                        <p class="mt-3 text-sm leading-6 text-slate-700">
                            You are removing the following office assignment(s)
                            from
                            <span class="font-black">
                                {{ selectedCourse.code }}
                            </span>
                            .
                        </p>
                    </div>
                </div>

                <ul class="mt-4 space-y-2">
                    <li
                        v-for="office in removedOffices"
                        :key="office.id"
                        class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-black text-red-800"
                    >
                        {{ office.name }}
                    </li>
                </ul>

                <div
                    class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-medium leading-6 text-amber-900"
                >
                    This will only update the course module assignment. It will
                    not delete existing clearance approvals, student history,
                    receipts, reports, or QR verification records.
                </div>

                <div
                    class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end"
                >
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="form.processing"
                        @click="showRemoveConfirmModal = false"
                    >
                        <ArrowLeft class="size-4" />
                        Go Back
                    </button>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-600 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-red-600/20 transition hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-red-300"
                        :disabled="form.processing"
                        @click="saveAssignments"
                    >
                        <CheckCircle2 class="size-4" />
                        {{
                            form.processing ? 'Saving...' : 'Continue and Save'
                        }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>