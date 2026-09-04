<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

type Course = {
    id: number;
    code: string;
    name: string;
};

defineProps<{
    courses: Course[];
}>();

defineOptions({
    layout: {
        title: 'Student Registration',
        description: 'Create your student clearance account',
    },
});
</script>

<template>
    <Head title="Student Registration" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-4">
            <div class="grid gap-2">
                <Label
                    for="student_id"
                    class="text-sm font-black text-slate-800"
                >
                    Student ID
                </Label>

                <Input
                    id="student_id"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="username"
                    name="student_id"
                    placeholder="Example: 202610001"
                    minlength="9"
                    maxlength="9"
                    pattern="[0-9]{9}"
                    inputmode="numeric"
                    title="Student ID must be exactly 9 digits and contain numbers only."
                    class="!h-12 !rounded-2xl !border-slate-200 !bg-white !px-4 !text-sm !font-semibold !text-slate-900 !shadow-sm placeholder:!font-medium placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20"
                />

                <p class="text-xs font-medium text-slate-500">
                    Student ID must be exactly 9 characters and can contain numbers only.
                </p>

                <InputError :message="errors.student_id" />
            </div>

            <div class="grid gap-2">
                <Label for="email" class="text-sm font-black text-slate-800">
                    Email Address
                </Label>

                <Input
                    id="email"
                    type="email"
                    required
                    :tabindex="2"
                    autocomplete="email"
                    name="email"
                    placeholder="Enter your email address"
                    class="!h-12 !rounded-2xl !border-slate-200 !bg-white !px-4 !text-sm !font-semibold !text-slate-900 !shadow-sm placeholder:!font-medium placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20"
                />

                <InputError :message="errors.email" />
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="last_name" class="text-sm font-black text-slate-800">
                        Last Name
                    </Label>

                    <Input
                        id="last_name"
                        type="text"
                        required
                        :tabindex="3"
                        autocomplete="family-name"
                        name="last_name"
                        placeholder="Enter your last name"
                        class="!h-12 !rounded-2xl !border-slate-200 !bg-white !px-4 !text-sm !font-semibold !text-slate-900 !shadow-sm placeholder:!font-medium placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20"
                    />

                    <InputError :message="errors.last_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="first_name" class="text-sm font-black text-slate-800">
                        First Name
                    </Label>

                    <Input
                        id="first_name"
                        type="text"
                        required
                        :tabindex="4"
                        autocomplete="given-name"
                        name="first_name"
                        placeholder="Enter your first name"
                        class="!h-12 !rounded-2xl !border-slate-200 !bg-white !px-4 !text-sm !font-semibold !text-slate-900 !shadow-sm placeholder:!font-medium placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20"
                    />

                    <InputError :message="errors.first_name" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label
                    for="year_level"
                    class="text-sm font-black text-slate-800"
                >
                    Year Level
                </Label>

                <select
                    id="year_level"
                    name="year_level"
                    required
                    :tabindex="5"
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 shadow-sm transition outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="">Select your year level</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>

                <InputError :message="errors.year_level" />
            </div>

            <div class="grid gap-2">
                <Label
                    for="course_id"
                    class="text-sm font-black text-slate-800"
                >
                    Course
                </Label>

                <select
                    id="course_id"
                    name="course_id"
                    required
                    :tabindex="6"
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 shadow-sm transition outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="">Select your course</option>
                    <option
                        v-for="course in courses"
                        :key="course.id"
                        :value="course.id"
                    >
                        {{ course.code }} - {{ course.name }}
                    </option>
                </select>

                <InputError :message="errors.course_id" />
            </div>

            <div class="grid gap-2">
                <Label for="password" class="text-sm font-black text-slate-800">
                    Password
                </Label>

                <PasswordInput
                    id="password"
                    required
                    :tabindex="7"
                    autocomplete="new-password"
                    name="password"
                    placeholder="Password"
                    class="!h-12 !rounded-2xl !border-slate-200 !bg-white !px-4 !text-sm !font-semibold !text-slate-900 !shadow-sm placeholder:!font-medium placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20"
                />

                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label
                    for="password_confirmation"
                    class="text-sm font-black text-slate-800"
                >
                    Confirm Password
                </Label>

                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="8"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Confirm password"
                    class="!h-12 !rounded-2xl !border-slate-200 !bg-white !px-4 !text-sm !font-semibold !text-slate-900 !shadow-sm placeholder:!font-medium placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20"
                />

                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-2 h-12 w-full rounded-2xl bg-blue-700 text-sm font-black text-white shadow-xl shadow-blue-700/25 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-2xl disabled:translate-y-0 disabled:opacity-70"
                tabindex="9"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                Create Student Account
            </Button>
        </div>

        <div class="text-center text-sm font-medium text-slate-500">
            Already have an account?
            <Link
                :href="login()"
                class="font-black text-blue-700 underline-offset-4 transition hover:text-blue-900 hover:underline"
                :tabindex="10"
            >
                Log in
            </Link>
        </div>
    </Form>
</template>
