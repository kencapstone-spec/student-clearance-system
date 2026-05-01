<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Bell,
    LayoutGrid,
    Menu,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { getInitials } from '@/composables/useInitials';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, NavItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

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

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);

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

const { isCurrentUrl, whenCurrentUrl } = useCurrentUrl();

const activeItemStyles =
    'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];
</script>

<template>
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="mr-2 h-9 w-9"
                            >
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>

                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only">
                                Navigation menu
                            </SheetTitle>

                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon
                                    class="size-6 fill-current text-black dark:text-white"
                                />
                            </SheetHeader>

                            <div class="flex h-full flex-1 flex-col justify-between space-y-4 py-6">
                                <nav class="-mx-3 space-y-1">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                        :class="
                                            whenCurrentUrl(
                                                item.href,
                                                activeItemStyles,
                                            )
                                        "
                                    >
                                        <component
                                            v-if="item.icon"
                                            :is="item.icon"
                                            class="h-5 w-5"
                                        />
                                        {{ item.title }}
                                    </Link>
                                </nav>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="dashboard()" class="flex items-center gap-x-2">
                    <AppLogo />
                </Link>

                <!-- Desktop Menu -->
                <div class="hidden h-full lg:flex lg:flex-1">
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList class="flex h-full items-stretch space-x-2">
                            <NavigationMenuItem
                                v-for="(item, index) in mainNavItems"
                                :key="index"
                                class="relative flex h-full items-center"
                            >
                                <Link
                                    :class="[
                                        navigationMenuTriggerStyle(),
                                        whenCurrentUrl(
                                            item.href,
                                            activeItemStyles,
                                        ),
                                        'h-9 cursor-pointer px-3',
                                    ]"
                                    :href="item.href"
                                >
                                    <component
                                        v-if="item.icon"
                                        :is="item.icon"
                                        class="mr-2 h-4 w-4"
                                    />
                                    {{ item.title }}
                                </Link>

                                <div
                                    v-if="isCurrentUrl(item.href)"
                                    class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"
                                ></div>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <div class="ml-auto flex items-center space-x-2">
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
                                    class="absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-bold text-white"
                                >
                                    {{
                                        notifications.unread_count > 99
                                            ? '99+'
                                            : notifications.unread_count
                                    }}
                                </span>

                                <span class="sr-only">
                                    Open notifications
                                </span>
                            </Button>
                        </DropdownMenuTrigger>

                        <DropdownMenuContent align="end" class="w-80 p-0">
                            <div class="flex items-center justify-between border-b px-4 py-3">
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

                                            <p class="mt-1 line-clamp-2 text-xs text-muted-foreground">
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

                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <Avatar class="size-8 overflow-hidden rounded-full">
                                    <AvatarImage
                                        v-if="auth.user.avatar"
                                        :src="auth.user.avatar"
                                        :alt="auth.user.name"
                                    />

                                    <AvatarFallback
                                        class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>

                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <div
            v-if="props.breadcrumbs.length > 1"
            class="flex w-full border-b border-sidebar-border/70"
        >
            <div
                class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl"
            >
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>