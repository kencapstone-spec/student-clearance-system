<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ClipboardCheck,
    FileText,
    LayoutGrid,
    Settings,
    ShieldCheck,
    Users,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

type AuthUser = {
    role?: string;
};

const page = usePage();
const authUser = computed(() => page.props.auth.user as AuthUser);
const userRole = computed(() => authUser.value.role);

const mainNavItems = computed<NavItem[]>(() => {
    if (userRole.value === 'admin') {
        return [
            {
                title: 'Dashboard',
                href: '/admin/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'Manage Users',
                href: '/admin/users',
                icon: Users,
            },
            {
                title: 'Monitor Clearances',
                href: '/admin/clearance-requests',
                icon: ClipboardCheck,
            },
            {
                title: 'Reports',
                href: '/admin/reports',
                icon: FileText,
            },
            {
                title: 'Course Modules',
                href: '/admin/course-modules',
                icon: Settings,
            },
        ];
    }

    if (userRole.value === 'staff') {
        return [
            {
                title: 'Pending Requests',
                href: '/staff/pending-requests',
                icon: ClipboardCheck,
            },
        ];
    }

    if (userRole.value === 'president') {
        return [
            {
                title: 'Final Approvals',
                href: '/president/final-approvals',
                icon: ShieldCheck,
            },
        ];
    }

    return [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader class="border-b border-slate-100 px-3 py-4">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="px-1 py-3">
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter class="border-t border-slate-100 px-3 py-4">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>