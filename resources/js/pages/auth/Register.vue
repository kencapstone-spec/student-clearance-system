<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    ChevronDown,
    GraduationCap,
    IdCard,
    Layers,
    Lock,
    Mail,
    ShieldCheck,
    User,
} from 'lucide-vue-next';
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
        description: 'Create your official student clearance account',
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
        <div class="grid gap-3 sm:gap-3.5">
            <!-- Row 1: Student ID & Email Address -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <Label
                            for="student_id"
                            class="text-[11px] font-black tracking-wider text-slate-700 uppercase"
                        >
                            Student ID
                        </Label>
                        <span
                            class="inline-flex items-center rounded-md border border-blue-200/80 bg-blue-50 px-2 py-0.5 text-[10px] font-extrabold text-blue-700"
                        >
                            9 Digits
                        </span>
                    </div>

                    <div class="group relative flex items-center">
                        <IdCard
                            class="pointer-events-none absolute left-3.5 size-4 text-slate-400 transition-colors group-focus-within:text-blue-600"
                        />
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
                            class="!h-10.5 !rounded-xl !border-slate-200/90 !bg-slate-50/70 !pr-3.5 !pl-10 !text-sm !font-semibold !text-slate-900 !shadow-xs transition-all duration-150 placeholder:!font-normal placeholder:!text-slate-400 focus:!bg-white focus-visible:!border-blue-600 focus-visible:!ring-4 focus-visible:!ring-blue-600/10"
                        />
                    </div>

                    <InputError :message="errors.student_id" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="email"
                        class="text-[11px] font-black tracking-wider text-slate-700 uppercase"
                    >
                        Email Address
                    </Label>

                    <div class="group relative flex items-center">
                        <Mail
                            class="pointer-events-none absolute left-3.5 size-4 text-slate-400 transition-colors group-focus-within:text-blue-600"
                        />
                        <Input
                            id="email"
                            type="email"
                            required
                            :tabindex="2"
                            autocomplete="email"
                            name="email"
                            placeholder="student@example.com"
                            class="!h-10.5 !rounded-xl !border-slate-200/90 !bg-slate-50/70 !pr-3.5 !pl-10 !text-sm !font-semibold !text-slate-900 !shadow-xs transition-all duration-150 placeholder:!font-normal placeholder:!text-slate-400 focus:!bg-white focus-visible:!border-blue-600 focus-visible:!ring-4 focus-visible:!ring-blue-600/10"
                        />
                    </div>

                    <InputError :message="errors.email" />
                </div>
            </div>

            <!-- Row 2: Last Name & First Name -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <Label
                        for="last_name"
                        class="text-[11px] font-black tracking-wider text-slate-700 uppercase"
                    >
                        Last Name
                    </Label>

                    <div class="group relative flex items-center">
                        <User
                            class="pointer-events-none absolute left-3.5 size-4 text-slate-400 transition-colors group-focus-within:text-blue-600"
                        />
                        <Input
                            id="last_name"
                            type="text"
                            required
                            :tabindex="3"
                            autocomplete="family-name"
                            name="last_name"
                            placeholder="Last name"
                            class="!h-10.5 !rounded-xl !border-slate-200/90 !bg-slate-50/70 !pr-3.5 !pl-10 !text-sm !font-semibold !text-slate-900 !shadow-xs transition-all duration-150 placeholder:!font-normal placeholder:!text-slate-400 focus:!bg-white focus-visible:!border-blue-600 focus-visible:!ring-4 focus-visible:!ring-blue-600/10"
                        />
                    </div>

                    <InputError :message="errors.last_name" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="first_name"
                        class="text-[11px] font-black tracking-wider text-slate-700 uppercase"
                    >
                        First Name
                    </Label>

                    <div class="group relative flex items-center">
                        <User
                            class="pointer-events-none absolute left-3.5 size-4 text-slate-400 transition-colors group-focus-within:text-blue-600"
                        />
                        <Input
                            id="first_name"
                            type="text"
                            required
                            :tabindex="4"
                            autocomplete="given-name"
                            name="first_name"
                            placeholder="First name"
                            class="!h-10.5 !rounded-xl !border-slate-200/90 !bg-slate-50/70 !pr-3.5 !pl-10 !text-sm !font-semibold !text-slate-900 !shadow-xs transition-all duration-150 placeholder:!font-normal placeholder:!text-slate-400 focus:!bg-white focus-visible:!border-blue-600 focus-visible:!ring-4 focus-visible:!ring-blue-600/10"
                        />
                    </div>

                    <InputError :message="errors.first_name" />
                </div>
            </div>

            <!-- Row 3: Year Level & Course -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <Label
                        for="year_level"
                        class="text-[11px] font-black tracking-wider text-slate-700 uppercase"
                    >
                        Year Level
                    </Label>

                    <div class="group relative flex items-center">
                        <Layers
                            class="pointer-events-none absolute left-3.5 size-4 text-slate-400 transition-colors group-focus-within:text-blue-600"
                        />
                        <select
                            id="year_level"
                            name="year_level"
                            required
                            :tabindex="5"
                            class="h-10.5 w-full cursor-pointer appearance-none rounded-xl border border-slate-200/90 bg-slate-50/70 pr-9 pl-10 text-sm font-semibold text-slate-900 shadow-xs transition-all duration-150 outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="">Select year level</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                        <ChevronDown
                            class="pointer-events-none absolute right-3 size-4 text-slate-400"
                        />
                    </div>

                    <InputError :message="errors.year_level" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="course_id"
                        class="text-[11px] font-black tracking-wider text-slate-700 uppercase"
                    >
                        Course
                    </Label>

                    <div class="group relative flex items-center">
                        <GraduationCap
                            class="pointer-events-none absolute left-3.5 size-4 text-slate-400 transition-colors group-focus-within:text-blue-600"
                        />
                        <select
                            id="course_id"
                            name="course_id"
                            required
                            :tabindex="6"
                            class="h-10.5 w-full cursor-pointer appearance-none truncate rounded-xl border border-slate-200/90 bg-slate-50/70 pr-9 pl-10 text-sm font-semibold text-slate-900 shadow-xs transition-all duration-150 outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-600/10 disabled:cursor-not-allowed disabled:opacity-50"
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
                        <ChevronDown
                            class="pointer-events-none absolute right-3 size-4 text-slate-400"
                        />
                    </div>

                    <InputError :message="errors.course_id" />
                </div>
            </div>

            <!-- Row 4: Password & Confirm Password -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <Label
                        for="password"
                        class="text-[11px] font-black tracking-wider text-slate-700 uppercase"
                    >
                        Password
                    </Label>

                    <PasswordInput
                        id="password"
                        required
                        :tabindex="7"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Create password"
                        :icon="Lock"
                        class="!h-10.5 !rounded-xl !border-slate-200/90 !bg-slate-50/70 !text-sm !font-semibold !text-slate-900 !shadow-xs transition-all duration-150 placeholder:!font-normal placeholder:!text-slate-400 focus:!bg-white focus-visible:!border-blue-600 focus-visible:!ring-4 focus-visible:!ring-blue-600/10"
                    />

                    <InputError :message="errors.password" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="password_confirmation"
                        class="text-[11px] font-black tracking-wider text-slate-700 uppercase"
                    >
                        Confirm Password
                    </Label>

                    <PasswordInput
                        id="password_confirmation"
                        required
                        :tabindex="8"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Repeat password"
                        :icon="ShieldCheck"
                        class="!h-10.5 !rounded-xl !border-slate-200/90 !bg-slate-50/70 !text-sm !font-semibold !text-slate-900 !shadow-xs transition-all duration-150 placeholder:!font-normal placeholder:!text-slate-400 focus:!bg-white focus-visible:!border-blue-600 focus-visible:!ring-4 focus-visible:!ring-blue-600/10"
                    />

                    <InputError :message="errors.password_confirmation" />
                </div>
            </div>

            <Button
                type="submit"
                class="group mt-2 h-11.5 w-full cursor-pointer rounded-xl bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 text-sm font-black text-white shadow-xl shadow-blue-700/25 transition-all duration-200 hover:-translate-y-0.5 hover:from-blue-700 hover:via-blue-800 hover:to-indigo-800 hover:shadow-2xl hover:shadow-blue-700/35 active:translate-y-0 disabled:translate-y-0 disabled:opacity-70"
                tabindex="9"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                <span>Create Student Account</span>
                <ArrowRight
                    v-if="!processing"
                    class="size-4 transition-transform duration-150 group-hover:translate-x-1"
                />
            </Button>
        </div>

        <div
            class="text-center text-xs font-semibold text-slate-500 sm:text-sm"
        >
            Already have an account?
            <Link
                :href="login()"
                class="inline-flex items-center gap-1 font-black text-blue-700 underline-offset-4 transition hover:text-blue-900 hover:underline"
                :tabindex="10"
            >
                <span>Log in</span>
                <ArrowRight class="size-3" />
            </Link>
        </div>
    </Form>
</template>
