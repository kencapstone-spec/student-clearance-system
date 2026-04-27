<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

type Office = {
    id: number;
    name: string;
    group: string;
};

defineProps<{
    offices: Office[];
}>();

const form = useForm({
    name: '',
    student_id: '',
    office_id: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/admin/users', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Staff Account" />

    <div class="min-h-screen bg-slate-50 p-4 text-slate-900">
        <div class="mx-auto flex max-w-4xl flex-col gap-6">
            <section class="rounded-2xl border bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-blue-700">
                    Admin / OSAS Director Panel
                </p>

                <div
                    class="mt-2 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <h1 class="text-3xl font-bold text-blue-950">
                            Create Staff Account
                        </h1>

                        <p class="mt-2 text-slate-600">
                            Add a new office staff account and assign it to one
                            clearance office.
                        </p>
                    </div>

                    <Link
                        href="/admin/users"
                        class="rounded-xl bg-slate-600 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
                    >
                        Back to Users
                    </Link>
                </div>

                <div
                    class="mt-4 rounded-xl bg-blue-50 p-4 text-sm text-blue-900"
                >
                    Staff accounts created here can log in using their
                    Staff/System ID and password. They will only see clearance
                    requests assigned to their selected office.
                </div>
            </section>

            <section class="rounded-2xl border bg-white p-6 shadow-sm">
                <form class="flex flex-col gap-5" @submit.prevent="submit">
                    <div>
                        <label
                            for="name"
                            class="text-sm font-semibold text-slate-700"
                        >
                            Full Name
                        </label>

                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            placeholder="Example: Library Staff"
                        />

                        <p
                            v-if="form.errors.name"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="student_id"
                            class="text-sm font-semibold text-slate-700"
                        >
                            Staff/System ID
                        </label>

                        <input
                            id="student_id"
                            v-model="form.student_id"
                            type="text"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            placeholder="Example: LIB002"
                        />

                        <p
                            v-if="form.errors.student_id"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.student_id }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="office_id"
                            class="text-sm font-semibold text-slate-700"
                        >
                            Assigned Office
                        </label>

                        <select
                            id="office_id"
                            v-model="form.office_id"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        >
                            <option value="">Select an office</option>

                            <option
                                v-for="office in offices"
                                :key="office.id"
                                :value="office.id"
                            >
                                {{ office.name }}
                            </option>
                        </select>

                        <p
                            v-if="form.errors.office_id"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.office_id }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="password"
                            class="text-sm font-semibold text-slate-700"
                        >
                            Password
                        </label>

                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            placeholder="Minimum 8 characters"
                        />

                        <p
                            v-if="form.errors.password"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="password_confirmation"
                            class="text-sm font-semibold text-slate-700"
                        >
                            Confirm Password
                        </label>

                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            placeholder="Re-enter password"
                        />
                    </div>

                    <div class="flex justify-end gap-3">
                        <Link
                            href="/admin/users"
                            class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                        >
                            Cancel
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Create Staff Account
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</template>