<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import MobileBottomNav from '@/components/MobileBottomNav.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const userRole = computed(
    () =>
        (page.props.auth as { user?: { role?: string } })?.user?.role ??
        'student',
);
const hasMobileBottomNav = computed(
    () => userRole.value === 'admin' || userRole.value === 'student',
);
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent
            variant="sidebar"
            class="pt-16 md:pt-0 md:pb-0"
            :class="hasMobileBottomNav ? 'pb-24' : 'pb-8'"
        >
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
        <MobileBottomNav />
        <Toaster />
    </AppShell>
</template>
