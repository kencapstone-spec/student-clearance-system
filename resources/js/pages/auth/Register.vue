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
        class="flex flex-col gap-3.5 sm:gap-4"
    >
        <div class="grid gap-2.5 sm:gap-3">
            <!-- Row 1: Student ID & Email Address -->
            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 sm:gap-3">
                <div class="grid gap-1">
                    <div class="flex items-center justify-between">
                        <Label
                            for="student_id"
                            class="text-xs font-bold text-slate-700 sm:text-xs sm:font-black"
                        >
                            Student ID
                        </Label>
                        <span class="text-[10px] font-semibold text-slate-400">
                            9 digits only
                        </span>
                    </div>

                    <Input
                        id="student_id"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="username"
                        name="student_id"
                        placeholder="e.g. 202610001"
                        minlength="9"
                        maxlength="9"
                        pattern="[0-9]{9}"
                        inputmode="numeric"
                        title="Student ID must be exactly 9 digits and contain numbers only."
                        class="!h-9.5 !rounded-xl !border-slate-200 !bg-white !px-3.5 !text-xs !font-semibold !text-slate-900 !shadow-xs placeholder:!font-normal placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20 sm:!h-10 sm:!text-sm"
                    />

                    <InputError :message="errors.student_id" />
                </div>

                <div class="grid gap-1">
                    <Label
                        for="email"
                        class="text-xs font-bold text-slate-700 sm:text-xs sm:font-black"
                    >
                        Email Address
                    </Label>

                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="student@example.com"
                        class="!h-9.5 !rounded-xl !border-slate-200 !bg-white !px-3.5 !text-xs !font-semibold !text-slate-900 !shadow-xs placeholder:!font-normal placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20 sm:!h-10 sm:!text-sm"
                    />

                    <InputError :message="errors.email" />
                </div>
            </div>

            <!-- Row 2: Last Name & First Name -->
            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 sm:gap-3">
                <div class="grid gap-1">
                    <Label
                        for="last_name"
                        class="text-xs font-bold text-slate-700 sm:text-xs sm:font-black"
                    >
                        Last Name
                    </Label>

                    <Input
                        id="last_name"
                        type="text"
                        required
                        :tabindex="3"
                        autocomplete="family-name"
                        name="last_name"
                        placeholder="Last name"
                        class="!h-9.5 !rounded-xl !border-slate-200 !bg-white !px-3.5 !text-xs !font-semibold !text-slate-900 !shadow-xs placeholder:!font-normal placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20 sm:!h-10 sm:!text-sm"
                    />

                    <InputError :message="errors.last_name" />
                </div>

                <div class="grid gap-1">
                    <Label
                        for="first_name"
                        class="text-xs font-bold text-slate-700 sm:text-xs sm:font-black"
                    >
                        First Name
                    </Label>

                    <Input
                        id="first_name"
                        type="text"
                        required
                        :tabindex="4"
                        autocomplete="given-name"
                        name="first_name"
                        placeholder="First name"
                        class="!h-9.5 !rounded-xl !border-slate-200 !bg-white !px-3.5 !text-xs !font-semibold !text-slate-900 !shadow-xs placeholder:!font-normal placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20 sm:!h-10 sm:!text-sm"
                    />

                    <InputError :message="errors.first_name" />
                </div>
            </div>

            <!-- Row 3: Year Level & Course -->
            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 sm:gap-3">
                <div class="grid gap-1">
                    <Label
                        for="year_level"
                        class="text-xs font-bold text-slate-700 sm:text-xs sm:font-black"
                    >
                        Year Level
                    </Label>

                    <select
                        id="year_level"
                        name="year_level"
                        required
                        :tabindex="5"
                        class="h-9.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 text-xs font-semibold text-slate-900 shadow-xs transition outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-50 sm:h-10 sm:text-sm"
                    >
                        <option value="">Select year level</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>

                    <InputError :message="errors.year_level" />
                </div>

                <div class="grid gap-1">
                    <Label
                        for="course_id"
                        class="text-xs font-bold text-slate-700 sm:text-xs sm:font-black"
                    >
                        Course
                    </Label>

                    <select
                        id="course_id"
                        name="course_id"
                        required
                        :tabindex="6"
                        class="h-9.5 w-full truncate rounded-xl border border-slate-200 bg-white px-3.5 text-xs font-semibold text-slate-900 shadow-xs transition outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-50 sm:h-10 sm:text-sm"
                    >
                        <option value="">Select course</option>
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
            </div>

            <!-- Row 4: Password & Confirm Password -->
            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 sm:gap-3">
                <div class="grid gap-1">
                    <Label
                        for="password"
                        class="text-xs font-bold text-slate-700 sm:text-xs sm:font-black"
                    >
                        Password
                    </Label>

                    <PasswordInput
                        id="password"
                        required
                        :tabindex="7"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Password"
                        class="!h-9.5 !rounded-xl !border-slate-200 !bg-white !px-3.5 !text-xs !font-semibold !text-slate-900 !shadow-xs placeholder:!font-normal placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20 sm:!h-10 sm:!text-sm"
                    />

                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-1">
                    <Label
                        for="password_confirmation"
                        class="text-xs font-bold text-slate-700 sm:text-xs sm:font-black"
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
                        class="!h-9.5 !rounded-xl !border-slate-200 !bg-white !px-3.5 !text-xs !font-semibold !text-slate-900 !shadow-xs placeholder:!font-normal placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20 sm:!h-10 sm:!text-sm"
                    />

                    <InputError :message="errors.password_confirmation" />
                </div>
            </div>

            <Button
                type="submit"
                class="mt-1 h-10.5 w-full rounded-xl bg-blue-700 text-xs font-black text-white shadow-lg shadow-blue-700/25 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-xl disabled:translate-y-0 disabled:opacity-70 sm:h-11 sm:text-sm"
                tabindex="9"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                Create Student Account
            </Button>
        </div>

        <div class="text-center text-xs font-medium text-slate-500 sm:text-sm">
            Already have an account?
            <Link
                :href="login()"
                class="font-extrabold text-blue-700 underline-offset-4 transition hover:text-blue-900 hover:underline"
                :tabindex="10"
            >
                Log in
            </Link>
        </div>
    </Form>
</template>
