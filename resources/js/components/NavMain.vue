<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup class="px-3 py-2">
        <SidebarGroupLabel
            class="px-2 text-xs font-black tracking-[0.18em] text-slate-400 uppercase"
        >
            Platform
        </SidebarGroupLabel>

        <SidebarMenu class="mt-3 space-y-1.5">
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                    class="h-11 rounded-2xl border border-transparent px-3 text-sm font-bold text-slate-600 transition hover:border-slate-200 hover:bg-slate-50 hover:text-slate-950 hover:shadow-sm data-[active=true]:border-slate-200 data-[active=true]:bg-slate-100 data-[active=true]:text-slate-950 data-[active=true]:shadow-sm"
                >
                    <Link :href="item.href">
                        <component
                            :is="item.icon"
                            class="size-4 shrink-0"
                        />

                        <span class="truncate">
                            {{ item.title }}
                        </span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>