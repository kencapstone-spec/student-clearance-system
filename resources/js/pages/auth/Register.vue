<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
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
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="student_id">Student ID</Label>
                <Input
                    id="student_id"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="username"
                    name="student_id"
                    placeholder="Enter your Student ID"
                />
                <InputError :message="errors.student_id" />
            </div>

            <div class="grid gap-2">
                <Label for="name">Full Name</Label>
                <Input
                    id="name"
                    type="text"
                    required
                    :tabindex="2"
                    autocomplete="name"
                    name="name"
                    placeholder="Enter your full name"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="course_id">Course</Label>
                <select
                    id="course_id"
                    name="course_id"
                    required
                    :tabindex="3"
                    class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
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
                <Label for="password">Password</Label>
                <PasswordInput
                    id="password"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    name="password"
                    placeholder="Password"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirm password</Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="5"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Confirm password"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                tabindex="6"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                Create Student Account
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Already have an account?
            <TextLink
                :href="login()"
                class="underline underline-offset-4"
                :tabindex="7"
                >Log in</TextLink
            >
        </div>
    </Form>
</template>