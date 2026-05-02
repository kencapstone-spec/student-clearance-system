<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Welcome Back!',
        description: 'Login to your student clearance account',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Login" />

    <div
        v-if="status"
        class="mb-5 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-center text-sm font-bold text-green-700"
    >
        {{ status }}
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-5">
            <div class="grid gap-2">
                <Label for="student_id" class="text-sm font-black text-slate-800">
                    Student ID
                </Label>

                <Input
                    id="student_id"
                    type="text"
                    name="student_id"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="username"
                    placeholder="Enter your Student ID"
                    class="!h-12 !rounded-2xl !border-slate-200 !bg-white !px-4 !text-sm !font-semibold !text-slate-900 !shadow-sm placeholder:!font-medium placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20"
                />

                <InputError :message="errors.student_id" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password" class="text-sm font-black text-slate-800">
                        Password
                    </Label>

                    <Link
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-xs font-bold text-blue-700 underline-offset-4 transition hover:text-blue-900 hover:underline"
                        :tabindex="5"
                    >
                        Forgot password?
                    </Link>
                </div>

                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    class="!h-12 !rounded-2xl !border-slate-200 !bg-white !px-4 !text-sm !font-semibold !text-slate-900 !shadow-sm placeholder:!font-medium placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20"
                />

                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3 text-sm font-bold text-slate-700">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Remember me</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-2 h-12 w-full rounded-2xl bg-blue-700 text-sm font-black text-white shadow-xl shadow-blue-700/25 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-2xl disabled:translate-y-0 disabled:opacity-70"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Login
            </Button>
        </div>

        <div
            class="text-center text-sm font-medium text-slate-500"
            v-if="canRegister"
        >
            Don't have an account?
            <Link
                :href="register()"
                class="font-black text-blue-700 underline-offset-4 transition hover:text-blue-900 hover:underline"
                :tabindex="5"
            >
                Register here
            </Link>
        </div>
    </Form>
</template>