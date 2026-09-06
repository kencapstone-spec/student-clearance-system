<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowRight, IdCard, Lock } from 'lucide-vue-next';
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
        title: 'Welcome Back',
        description: 'Sign in to access your clearance portal',
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
        class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-xs font-bold text-emerald-700"
    >
        {{ status }}
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-4"
    >
        <div class="grid gap-3.5">
            <div class="space-y-1.5">
                <Label
                    for="student_id"
                    class="text-[11px] font-black tracking-wider text-slate-700 uppercase"
                >
                    Account ID
                </Label>

                <div class="group relative flex items-center">
                    <IdCard
                        class="pointer-events-none absolute left-3.5 size-4 text-slate-400 transition-colors group-focus-within:text-blue-600"
                    />
                    <Input
                        id="student_id"
                        type="text"
                        name="student_id"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="username"
                        placeholder="Enter your Account ID"
                        class="!h-11 !rounded-xl !border-slate-200/90 !bg-slate-50/70 !pr-3.5 !pl-10 !text-sm !font-semibold !text-slate-900 !shadow-xs transition-all duration-150 placeholder:!font-normal placeholder:!text-slate-400 focus:!bg-white focus-visible:!border-blue-600 focus-visible:!ring-4 focus-visible:!ring-blue-600/10"
                    />
                </div>

                <InputError :message="errors.student_id" />
            </div>

            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <Label
                        for="password"
                        class="text-[11px] font-black tracking-wider text-slate-700 uppercase"
                    >
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
                    :icon="Lock"
                    class="!h-11 !rounded-xl !border-slate-200/90 !bg-slate-50/70 !text-sm !font-semibold !text-slate-900 !shadow-xs transition-all duration-150 placeholder:!font-normal placeholder:!text-slate-400 focus:!bg-white focus-visible:!border-blue-600 focus-visible:!ring-4 focus-visible:!ring-blue-600/10"
                />

                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between pt-0.5">
                <Label
                    for="remember"
                    class="flex items-center space-x-2.5 text-xs font-semibold text-slate-600 sm:text-sm"
                >
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Remember me</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="group mt-1 h-11.5 w-full cursor-pointer rounded-xl bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 text-sm font-black text-white shadow-xl shadow-blue-700/25 transition-all duration-200 hover:-translate-y-0.5 hover:from-blue-700 hover:via-blue-800 hover:to-indigo-800 hover:shadow-2xl hover:shadow-blue-700/35 active:translate-y-0 disabled:translate-y-0 disabled:opacity-70"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                <span>Sign In</span>
                <ArrowRight
                    v-if="!processing"
                    class="size-4 transition-transform duration-150 group-hover:translate-x-1"
                />
            </Button>
        </div>

        <div
            class="text-center text-xs font-semibold text-slate-500 sm:text-sm"
            v-if="canRegister"
        >
            Don't have an account?
            <Link
                :href="register()"
                class="inline-flex items-center gap-1 font-black text-blue-700 underline-offset-4 transition hover:text-blue-900 hover:underline"
                :tabindex="5"
            >
                <span>Register here</span>
                <ArrowRight class="size-3" />
            </Link>
        </div>
    </Form>
</template>
