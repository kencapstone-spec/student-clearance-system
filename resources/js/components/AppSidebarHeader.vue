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
import type { BreadcrumbItem } from '@/types';

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

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();

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
        class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-3 border-b border-slate-200 bg-white/90 px-6 text-slate-900 shadow-sm shadow-slate-200/70 backdrop-blur-xl transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex min-w-0 items-center gap-3">
            <SidebarTrigger
                class="-ml-1 rounded-xl text-slate-600 transition hover:bg-blue-50 hover:text-blue-700"
            />

            <div class="hidden h-6 w-px bg-slate-200 sm:block"></div>

            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <DropdownMenu>
                <DropdownMenuTrigger :as-child="true">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="relative h-10 w-10 cursor-pointer rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-md"
                    >
                        <Bell class="size-5" />

                        <span
                            v-if="notifications.unread_count > 0"
                            class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-black text-white shadow-sm"
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
                    class="w-86 overflow-hidden rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl shadow-slate-300/70"
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
                            class="rounded-full px-3 py-1 text-xs font-bold text-blue-700 transition hover:bg-blue-50 hover:text-blue-900"
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
                            class="block border-b border-slate-100 px-4 py-3 text-left transition last:border-b-0 hover:bg-blue-50/60"
                            :class="
                                notification.read_at
                                    ? 'bg-white opacity-75'
                                    : 'bg-blue-50/40'
                            "
                        >
                            <div class="flex items-start gap-3">
                                <span
                                    v-if="!notification.read_at"
                                    class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-blue-600 shadow-sm"
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
        </div>
    </header>
</template>