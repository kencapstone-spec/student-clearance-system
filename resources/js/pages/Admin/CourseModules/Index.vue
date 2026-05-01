<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

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
}>();
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
                            View each course module and its assigned clearance offices.
                        </p>

                        <div class="mt-4 rounded-xl bg-blue-50 p-4 text-sm text-blue-900">
                            Each course can have its own clearance office configuration.
                            For now, all courses use the same default offices until the final course-specific requirements are available.
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
                </div>
            </section>
        </div>
    </div>
</template>