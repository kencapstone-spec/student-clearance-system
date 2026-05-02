<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
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

const props = defineProps<{
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
        errorMessage.value = 'Select at least one regular office for this course module.';

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
            errorMessage.value = 'Unable to update course module offices. Please check the selected offices.';
        },
    });
};
</script>

<template>
    <Head title="Course Modules" />

    <div class="min-h-screen bg-slate-50 p-4 text-slate-900">
        <div class="mx-auto flex max-w-7xl flex-col gap-6">
            <section class="rounded-2xl border bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-700">
                            Admin / OSAS Director Panel
                        </p>

                        <h1 class="mt-2 text-3xl font-bold text-blue-950">
                            Course Modules
                        </h1>

                        <p class="mt-2 text-slate-600">
                            View and configure each course module's assigned clearance offices.
                        </p>

                        <div class="mt-4 rounded-xl bg-blue-50 p-4 text-sm text-blue-900">
                            Changes made here apply to future or newly created clearance requests only.
                            Existing clearance records, receipts, reports, QR verification, remarks, and approval history must remain unchanged.
                        </div>

                        <div
                            v-if="successMessage"
                            class="mt-4 rounded-xl bg-green-50 p-4 text-sm font-medium text-green-800"
                        >
                            {{ successMessage }}
                        </div>
                    </div>

                    <Link
                        href="/admin/dashboard"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Back to Dashboard
                    </Link>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2">
                <div
                    v-for="course in courses"
                    :key="course.id"
                    class="rounded-2xl border bg-white p-6 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-blue-700">
                                {{ course.code }} Module
                            </p>

                            <h2 class="mt-1 text-xl font-bold text-blue-950">
                                {{ course.name }}
                            </h2>
                        </div>

                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                            {{ course.offices.length }} Offices
                        </span>
                    </div>

                    <div class="mt-4">
                        <p class="mb-2 text-sm font-semibold text-slate-700">
                            Assigned Clearance Offices
                        </p>

                        <div
                            v-if="course.offices.length === 0"
                            class="rounded-xl border border-dashed p-4 text-sm text-slate-500"
                        >
                            No offices assigned to this course yet.
                        </div>

                        <ul v-else class="space-y-2">
                            <li
                                v-for="office in course.offices"
                                :key="office.id"
                                class="flex items-center justify-between rounded-xl border bg-slate-50 px-4 py-3 text-sm"
                            >
                                <div>
                                    <p class="font-semibold text-blue-950">
                                        {{ office.name }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ office.group }}
                                    </p>
                                </div>

                                <span
                                    v-if="office.is_final_approver"
                                    class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700"
                                >
                                    Final
                                </span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button
                            type="button"
                            class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                            @click="openEditModal(course)"
                        >
                            Edit Offices
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <div
            v-if="showEditModal && selectedCourse"
            class="fixed inset-0 z-40 flex items-center justify-center bg-slate-950/60 p-4"
        >
            <div class="w-full max-w-3xl rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-blue-700">
                            Edit Course Module
                        </p>

                        <h2 class="mt-1 text-2xl font-bold text-blue-950">
                            {{ selectedCourse.code }} - {{ selectedCourse.name }}
                        </h2>

                        <p class="mt-2 text-sm text-slate-600">
                            Select the regular offices required for this course. The President final approval is handled separately and is not editable here.
                        </p>
                    </div>

<button
    type="button"
    class="rounded-lg px-3 py-1 text-xl font-bold text-slate-500 hover:bg-slate-100"
    @click="closeEditModal()"
>
    ×
</button>
                </div>

                <div class="mt-4 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                    Removing an office from a course module will not delete the office and will not change old clearance records.
                    It only affects future or newly created clearance requests for this course.
                </div>

                <div
                    v-if="errorMessage || form.errors.office_ids"
                    class="mt-4 rounded-xl bg-red-50 p-4 text-sm font-medium text-red-700"
                >
                    {{ errorMessage || form.errors.office_ids }}
                </div>

                <div class="mt-5 flex items-center justify-between rounded-xl border bg-slate-50 px-4 py-3 text-sm">
                    <span class="font-semibold text-slate-700">
                        Selected Offices
                    </span>

                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                        {{ selectedOfficeCount }} selected
                    </span>
                </div>

                <div class="mt-4 max-h-96 space-y-2 overflow-y-auto pr-1">
                    <label
                        v-for="office in props.offices"
                        :key="office.id"
                        class="flex cursor-pointer items-start gap-3 rounded-xl border bg-white px-4 py-3 text-sm hover:bg-slate-50"
                    >
                        <input
                            v-model="form.office_ids"
                            type="checkbox"
                            :value="office.id"
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        />

                        <span>
                            <span class="block font-semibold text-blue-950">
                                {{ office.name }}
                            </span>

                            <span class="block text-xs text-slate-500">
                                {{ office.group }}
                            </span>
                        </span>
                    </label>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
  <button
    type="button"
    class="rounded-xl border px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
    :disabled="form.processing"
    @click="closeEditModal()"
>
    Cancel
</button>

                    <button
                        type="button"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-blue-300"
                        :disabled="form.processing"
                        @click="submitAssignments"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Assignments' }}
                    </button>
                </div>
            </div>
        </div>

        <div
            v-if="showRemoveConfirmModal && selectedCourse"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
        >
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <h2 class="text-xl font-bold text-red-700">
                    Confirm Office Removal
                </h2>

                <p class="mt-3 text-sm text-slate-700">
                    You are removing the following office assignment(s) from
                    <span class="font-semibold">{{ selectedCourse.code }}</span>:
                </p>

                <ul class="mt-3 space-y-2">
                    <li
                        v-for="office in removedOffices"
                        :key="office.id"
                        class="rounded-xl border bg-red-50 px-4 py-2 text-sm font-semibold text-red-800"
                    >
                        {{ office.name }}
                    </li>
                </ul>

                <div class="mt-4 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                    This will only update the course module assignment. It will not delete existing clearance approvals,
                    student history, receipts, reports, or QR verification records.
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        class="rounded-xl border px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                        :disabled="form.processing"
                        @click="showRemoveConfirmModal = false"
                    >
                        Go Back
                    </button>

                    <button
                        type="button"
                        class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-red-300"
                        :disabled="form.processing"
                        @click="saveAssignments"
                    >
                        {{ form.processing ? 'Saving...' : 'Continue and Save' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>