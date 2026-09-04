<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useInitials } from '@/composables/useInitials';
import type { BreadcrumbItem, User } from '@/types';

type SharedNotification = {
    id: number;
    title: string;
    message: string;
    link: string | null;
    read_at: string | null;
    created_at: string | null;
    created_at_human: string | null;
};

type NotificationsProp = {
    items: SharedNotification[];
    unread_count: number;
};

const props = withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const { getInitials } = useInitials();
const authUser = computed(() => (page.props.auth as { user?: User })?.user);

const userRole = computed(() => {
    return authUser.value?.role ?? 'student';
});

const portalLabel = computed(() => {
    if (userRole.value === 'admin') {
        return 'Admin Portal';
    }

    if (userRole.value === 'staff') {
        return 'Staff Portal';
    }

    if (userRole.value === 'president') {
        return 'President Portal';
    }

    return 'Student Portal';
});

const formatPageTitle = (value: string) => {
    return value
        .split(/[-_]/)
        .filter(Boolean)
        .map((word) => {
            return word.charAt(0).toUpperCase() + word.slice(1);
        })
        .join(' ');
};

const pageTitle = computed(() => {
    const breadcrumbTitle = props.breadcrumbs.at(-1)?.title;

    if (breadcrumbTitle) {
        return breadcrumbTitle;
    }

    const currentPath = page.url?.split('?')[0] ?? '';
    const currentSegment = currentPath.split('/').filter(Boolean).at(-1);

    if (currentSegment) {
        return formatPageTitle(currentSegment);
    }

    return portalLabel.value;
});

const notifications = computed<NotificationsProp>(() => {
    const sharedNotifications = page.props.notifications as
        | NotificationsProp
        | undefined;

    return {
        items: sharedNotifications?.items ?? [],
        unread_count: sharedNotifications?.unread_count ?? 0,
    };
});

const markAllNotificationsAsRead = () => {
    router.patch(
        '/notifications/mark-all-as-read',
        {},
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <header
        class="sticky top-0 z-20 flex h-16 shrink-0 items-center border-b border-slate-200/80 bg-white/90 px-4 text-slate-900 shadow-sm shadow-slate-200/70 backdrop-blur-xl transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-14 md:px-6"
    >
        <div class="flex min-w-0 flex-1 items-center gap-4">
            <SidebarTrigger
                class="-ml-1 rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 hover:shadow-md"
            />

            <div class="hidden h-8 w-px bg-slate-200 sm:block"></div>

            <div class="min-w-0">
                <div class="flex min-w-0 items-center gap-2">
                    <h1
                        class="truncate text-lg font-black tracking-tight text-slate-950 md:text-xl"
                    >
                        {{ pageTitle }}
                    </h1>

                    <span
                        class="hidden rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[0.65rem] font-black tracking-wide text-slate-600 uppercase sm:inline-flex"
                    >
                        {{ portalLabel }}
                    </span>
                </div>

                <div
                    v-if="props.breadcrumbs && props.breadcrumbs.length > 1"
                    class="mt-1 hidden text-xs text-slate-500 lg:block"
                >
                    <Breadcrumbs :breadcrumbs="props.breadcrumbs" />
                </div>
            </div>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <DropdownMenu>
                <DropdownMenuTrigger :as-child="true">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="relative h-11 w-11 cursor-pointer rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 hover:shadow-md"
                    >
                        <Bell class="size-5" />

                        <span
                            v-if="notifications.unread_count > 0"
                            class="absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-black text-white shadow-sm"
                        >
                            {{
                                notifications.unread_count > 99
                                    ? '99+'
                                    : notifications.unread_count
                            }}
                        </span>

                        <span class="sr-only">Open notifications</span>
                    </Button>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                    align="end"
                    class="w-[22rem] overflow-hidden rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl shadow-slate-300/70"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3"
                    >
                        <div>
                            <p class="text-sm font-black text-slate-900">
                                Notifications
                            </p>

                            <p class="text-xs font-medium text-slate-500">
                                {{ notifications.unread_count }} unread
                            </p>
                        </div>

                        <button
                            v-if="notifications.unread_count > 0"
                            type="button"
                            class="rounded-full px-3 py-1 text-xs font-bold text-slate-700 transition hover:bg-slate-100 hover:text-slate-950"
                            @click="markAllNotificationsAsRead"
                        >
                            Mark all as read
                        </button>
                    </div>

                    <div
                        v-if="notifications.items.length === 0"
                        class="px-4 py-8 text-center text-sm font-medium text-slate-500"
                    >
                        No notifications yet.
                    </div>

                    <div v-else class="max-h-96 overflow-y-auto">
                        <Link
                            v-for="notification in notifications.items"
                            :key="notification.id"
                            :href="`/notifications/${notification.id}/open`"
                            class="block border-b border-slate-100 px-4 py-3 text-left transition last:border-b-0 hover:bg-slate-50"
                            :class="
                                notification.read_at
                                    ? 'bg-white opacity-75'
                                    : 'bg-slate-50'
                            "
                        >
                            <div class="flex items-start gap-3">
                                <span
                                    v-if="!notification.read_at"
                                    class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-slate-900 shadow-sm"
                                ></span>

                                <span
                                    v-else
                                    class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-slate-200"
                                ></span>

                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-slate-900">
                                        {{ notification.title }}
                                    </p>

                                    <p
                                        class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500"
                                    >
                                        {{ notification.message }}
                                    </p>

                                    <p
                                        v-if="notification.created_at_human"
                                        class="mt-1 text-xs font-medium text-slate-400"
                                    >
                                        {{ notification.created_at_human }}
                                    </p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>

            <!-- Mobile User Profile Avatar Dropdown -->
            <DropdownMenu v-if="authUser">
                <DropdownMenuTrigger :as-child="true">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="relative h-11 w-11 cursor-pointer rounded-2xl border border-slate-200 bg-blue-950 text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-blue-900 md:hidden"
                    >
                        <span class="text-xs font-black tracking-wider">
                            {{ getInitials(authUser.name) }}
                        </span>
                        <span class="sr-only">Open user menu</span>
                    </Button>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                    align="end"
                    class="w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white p-1 shadow-2xl shadow-slate-300/70"
                >
                    <UserMenuContent :user="authUser" />
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
