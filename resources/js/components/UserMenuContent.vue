<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings } from 'lucide-vue-next';
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

    <DropdownMenuItem :as-child="true" class="rounded-xl p-0">
        <Link
            class="flex w-full cursor-pointer items-center rounded-xl px-3 py-2.5 text-sm font-bold text-red-600 transition hover:bg-red-50 hover:text-red-700"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>