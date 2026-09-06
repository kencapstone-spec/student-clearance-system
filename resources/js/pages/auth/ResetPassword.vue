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
        <div class="grid gap-3.5 sm:gap-4">
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
                    autocomplete="email"
                    v-model="inputEmail"
                    class="!h-10 cursor-not-allowed !rounded-xl !border-slate-200 !bg-slate-100 !px-3.5 !text-xs !font-semibold !text-slate-500 !shadow-xs sm:!h-10.5 sm:!text-sm"
                    readonly
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-1.5">
                <Label
                    for="password"
                    class="text-xs font-bold text-slate-700 sm:text-xs sm:font-black"
                >
                    New Password
                </Label>
                <PasswordInput
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    autofocus
                    placeholder="Enter new password"
                    class="!h-10 !rounded-xl !border-slate-200 !bg-white !px-3.5 !text-xs !font-semibold !text-slate-900 !shadow-xs placeholder:!font-normal placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20 sm:!h-10.5 sm:!text-sm"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-1.5">
                <Label
                    for="password_confirmation"
                    class="text-xs font-bold text-slate-700 sm:text-xs sm:font-black"
                >
                    Confirm New Password
                </Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    autocomplete="new-password"
                    placeholder="Confirm new password"
                    class="!h-10 !rounded-xl !border-slate-200 !bg-white !px-3.5 !text-xs !font-semibold !text-slate-900 !shadow-xs placeholder:!font-normal placeholder:!text-slate-400 focus-visible:!border-blue-500 focus-visible:!ring-blue-500/20 sm:!h-10.5 sm:!text-sm"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-1 h-10.5 w-full rounded-xl bg-blue-700 text-xs font-black text-white shadow-lg shadow-blue-700/25 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-xl disabled:translate-y-0 disabled:opacity-70 sm:h-11 sm:text-sm"
                :disabled="processing"
                data-test="reset-password-button"
            >
                <Spinner v-if="processing" />
                Reset Password
            </Button>
        </div>
    </Form>
</template>
