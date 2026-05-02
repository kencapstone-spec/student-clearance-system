<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings, ShieldAlert } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';

type Props = {
    user: User;
};

const showLogoutDialog = ref(false);

const openLogoutDialog = () => {
    showLogoutDialog.value = true;
};

const closeLogoutDialog = () => {
    showLogoutDialog.value = false;
};

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="rounded-2xl bg-blue-50/70 px-3 py-3 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>

    <DropdownMenuSeparator class="my-1 bg-slate-100" />

    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true" class="rounded-xl p-0">
            <Link
                class="flex w-full cursor-pointer items-center rounded-xl px-3 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 hover:text-blue-700"
                :href="edit()"
                prefetch
            >
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>

    <DropdownMenuSeparator class="my-1 bg-slate-100" />

    <DropdownMenuItem
        :as-child="true"
        class="rounded-xl p-0"
        @select.prevent="openLogoutDialog"
    >
        <button
            type="button"
            class="flex w-full cursor-pointer items-center rounded-xl px-3 py-2.5 text-left text-sm font-bold text-red-600 transition hover:bg-red-50 hover:text-red-700"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </button>
    </DropdownMenuItem>

    <Dialog v-model:open="showLogoutDialog">
        <DialogContent
            class="rounded-3xl border border-slate-200 p-0 shadow-2xl sm:max-w-md"
        >
            <div class="p-6">
                <DialogHeader>
                    <div class="flex items-start gap-4">
                        <div
                            class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-red-50 text-red-600"
                        >
                            <ShieldAlert class="size-6" />
                        </div>

                        <div>
                            <DialogTitle
                                class="text-xl font-black text-blue-950"
                            >
                                Confirm logout
                            </DialogTitle>

                            <DialogDescription
                                class="mt-2 text-sm leading-6 text-slate-600"
                            >
                                Are you sure you want to log out of your
                                account? You will need to sign in again to
                                continue using the clearance system.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <DialogFooter class="mt-6 flex gap-3 sm:justify-end">
                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="closeLogoutDialog"
                    >
                        Cancel
                    </button>

                    <Link
                        :href="logout()"
                        as="button"
                        class="rounded-2xl bg-red-600 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-red-600/20 transition hover:bg-red-700"
                        @click="handleLogout"
                    >
                        Yes, log out
                    </Link>
                </DialogFooter>
            </div>
        </DialogContent>
    </Dialog>
</template>
