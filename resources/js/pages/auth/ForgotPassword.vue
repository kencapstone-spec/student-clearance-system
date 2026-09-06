<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Mail } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Forgot Password',
        description: 'Enter your email to receive a password reset link',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Forgot Password" />

    <div
        v-if="status"
        class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-xs font-bold text-emerald-700"
    >
        {{ status }}
    </div>

    <div class="space-y-5">
        <Form v-bind="email.form()" v-slot="{ errors, processing }">
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
                        name="email"
                        autocomplete="off"
                        autofocus
                        placeholder="email@example.com"
                        class="!h-11 !rounded-xl !border-slate-200/90 !bg-slate-50/70 !pr-3.5 !pl-10 !text-sm !font-semibold !text-slate-900 !shadow-xs transition-all duration-150 placeholder:!font-normal placeholder:!text-slate-400 focus:!bg-white focus-visible:!border-blue-600 focus-visible:!ring-4 focus-visible:!ring-blue-600/10"
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <div class="my-4 flex items-center justify-start">
                <Button
                    class="group h-11.5 w-full cursor-pointer rounded-xl bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 text-sm font-black text-white shadow-xl shadow-blue-700/25 transition-all duration-200 hover:-translate-y-0.5 hover:from-blue-700 hover:via-blue-800 hover:to-indigo-800 hover:shadow-2xl hover:shadow-blue-700/35 active:translate-y-0 disabled:translate-y-0 disabled:opacity-70"
                    :disabled="processing"
                    data-test="email-password-reset-link-button"
                >
                    <Spinner v-if="processing" />
                    <span>Email password reset link</span>
                    <ArrowRight
                        v-if="!processing"
                        class="size-4 transition-transform duration-150 group-hover:translate-x-1"
                    />
                </Button>
            </div>
        </Form>

        <div
            class="text-center text-xs font-semibold text-slate-500 sm:text-sm"
        >
            <span>Remember your password?</span>
            <Link
                :href="login()"
                class="ml-1 inline-flex items-center gap-1 font-black text-blue-700 underline-offset-4 transition hover:text-blue-900 hover:underline"
            >
                <span>Log in</span>
                <ArrowRight class="size-3" />
            </Link>
        </div>
    </div>
</template>
