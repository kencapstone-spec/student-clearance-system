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
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="ml-auto flex items-center">
            <DropdownMenu>
                <DropdownMenuTrigger :as-child="true">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="relative h-9 w-9 cursor-pointer"
                    >
                        <Bell class="size-5 opacity-80" />

                        <span
                            v-if="notifications.unread_count > 0"
                            class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-bold text-white"
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

                <DropdownMenuContent align="end" class="w-80 p-0">
                    <div
                        class="flex items-center justify-between border-b px-4 py-3"
                    >
                        <div>
                            <p class="text-sm font-semibold">
                                Notifications
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ notifications.unread_count }} unread
                            </p>
                        </div>

                        <button
                            v-if="notifications.unread_count > 0"
                            type="button"
                            class="text-xs font-medium text-blue-600 hover:underline"
                            @click="markAllNotificationsAsRead"
                        >
                            Mark all as read
                        </button>
                    </div>

                    <div
                        v-if="notifications.items.length === 0"
                        class="px-4 py-6 text-center text-sm text-muted-foreground"
                    >
                        No notifications yet.
                    </div>

                    <div v-else class="max-h-96 overflow-y-auto">
                        <Link
                            v-for="notification in notifications.items"
                            :key="notification.id"
                            :href="`/notifications/${notification.id}/open`"
                            class="block border-b px-4 py-3 text-left last:border-b-0 hover:bg-accent"
                            :class="
                                notification.read_at
                                    ? 'opacity-70'
                                    : 'bg-accent/40'
                            "
                        >
                            <div class="flex items-start gap-3">
                                <span
                                    v-if="!notification.read_at"
                                    class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-blue-600"
                                ></span>
                                <span
                                    v-else
                                    class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-transparent"
                                ></span>

                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium">
                                        {{ notification.title }}
                                    </p>
                                    <p
                                        class="mt-1 line-clamp-2 text-xs text-muted-foreground"
                                    >
                                        {{ notification.message }}
                                    </p>
                                    <p
                                        v-if="notification.created_at_human"
                                        class="mt-1 text-xs text-muted-foreground"
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