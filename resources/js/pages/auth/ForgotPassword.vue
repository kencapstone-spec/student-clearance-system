<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Forgot password',
        description: 'Enter your email to receive a password reset link',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Forgot password" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <div class="space-y-6">
        <Form v-bind="email.form()" v-slot="{ errors, processing }">
            <div class="grid gap-1.5">
                <Label
                    for="email"
                    class="text-xs font-bold text-slate-700 sm:text-xs sm:font-black"
                >
                    Email Address
                </Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    autocomplete="off"
                    autofocus
                    placeholder="email@example.com"
                    class="!h-10 !rounded-xl !border-slate-200 !bg-white !px-3.5 !text-xs !font-semibold !text-slate-900 !shadow-xs placeholder:!font-normal placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20 sm:!h-10.5 sm:!text-sm"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="my-5 flex items-center justify-start">
                <Button
                    class="h-10.5 w-full rounded-xl bg-blue-700 text-xs font-black text-white shadow-lg shadow-blue-700/25 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-xl disabled:translate-y-0 disabled:opacity-70 sm:h-11 sm:text-sm"
                    :disabled="processing"
                    data-test="email-password-reset-link-button"
                >
                    <Spinner v-if="processing" />
                    Email password reset link
                </Button>
            </div>
        </Form>

        <div class="space-x-1 text-center text-sm font-medium text-slate-500">
            <span>Or, return to</span>
            <Link
                :href="login()"
                class="font-black text-blue-700 underline-offset-4 transition hover:text-blue-900 hover:underline"
            >
                Log in
            </Link>
        </div>
    </div>
</template>
