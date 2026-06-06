<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Reset password',
        description: 'Please enter your new password below',
    },
});

const props = defineProps<{
    token: string;
    email: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <Head title="Reset password" />

    <Form
        v-bind="update.form()"
        :transform="(data) => ({ ...data, token, email })"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email" class="text-sm font-black text-slate-800">
                    Email Address
                </Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    autocomplete="email"
                    v-model="inputEmail"
                    class="!h-12 cursor-not-allowed !rounded-2xl !border-slate-200 !bg-slate-100 !px-4 !text-sm !font-semibold !text-slate-500 !shadow-sm"
                    readonly
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password" class="text-sm font-black text-slate-800">
                    New Password
                </Label>
                <PasswordInput
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    autofocus
                    placeholder="Enter new password"
                    class="!h-12 !rounded-2xl !border-slate-200 !bg-white !px-4 !text-sm !font-semibold !text-slate-900 !shadow-sm placeholder:!font-medium placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label
                    for="password_confirmation"
                    class="text-sm font-black text-slate-800"
                >
                    Confirm New Password
                </Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    autocomplete="new-password"
                    placeholder="Confirm new password"
                    class="!h-12 !rounded-2xl !border-slate-200 !bg-white !px-4 !text-sm !font-semibold !text-slate-900 !shadow-sm placeholder:!font-medium placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-2 h-12 w-full rounded-2xl bg-blue-700 text-sm font-black text-white shadow-xl shadow-blue-700/25 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-2xl disabled:translate-y-0 disabled:opacity-70"
                :disabled="processing"
                data-test="reset-password-button"
            >
                <Spinner v-if="processing" />
                Reset Password
            </Button>
        </div>
    </Form>
</template>
