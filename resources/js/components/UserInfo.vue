<script setup lang="ts">
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';

type Props = {
    user: User;
    showEmail?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

const showAvatar = computed(
    () => props.user.avatar && props.user.avatar !== '',
);
</script>

<template>
    <Avatar class="h-10 w-10 overflow-hidden rounded-2xl border border-blue-100 bg-blue-50 shadow-sm">
        <AvatarImage v-if="showAvatar" :src="user.avatar!" :alt="user.name" />

        <AvatarFallback class="rounded-2xl bg-blue-50 text-sm font-black text-blue-700">
            {{ getInitials(user.name) }}
        </AvatarFallback>
    </Avatar>

    <div class="grid min-w-0 flex-1 text-left leading-tight">
        <span class="truncate text-sm font-black text-slate-900">
            {{ user.name }}
        </span>

        <span
            v-if="showEmail"
            class="mt-0.5 truncate text-xs font-medium text-slate-500"
        >
            {{ user.email }}
        </span>
    </div>
</template>