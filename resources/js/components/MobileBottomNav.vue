<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Bell,
    CheckCircle2,
    ChevronRight,
    ClipboardCheck,
    FilePlus,
    FileText,
    GraduationCap,
    LayoutGrid,
    Layers,
    LogOut,
    MoreHorizontal,
    Printer,
    Settings,
    ShieldAlert,
    ShieldCheck,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { useInitials } from '@/composables/useInitials';
import { logout } from '@/routes';

type UserCourse = {
    id?: number;
    code?: string;
    name?: string;
};

type AuthUser = {
    id: number;
    name: string;
    first_name?: string;
    last_name?: string;
    year_level?: string;
    email: string;
    role?: string;
    student_id?: string;
    avatar?: string;
    course?: UserCourse | null;
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

type StudentClearanceProp = {
    id: number;
    is_cleared: boolean;
};

const page = usePage();
const { getInitials } = useInitials();

const authUser = computed(() => (page.props.auth as { user?: AuthUser })?.user);
const userRole = computed(() => authUser.value?.role ?? 'student');

const notifications = computed<NotificationsProp>(() => {
    const shared = page.props.notifications as NotificationsProp | undefined;
    return {
        items: shared?.items ?? [],
        unread_count: shared?.unread_count ?? 0,
    };
});

const studentClearance = computed<StudentClearanceProp | null>(() => {
    return (page.props.studentClearance as StudentClearanceProp | null | undefined) ?? null;
});

// Also check dashboard specific clearanceRequest prop
const dashboardClearance = computed(() => {
    return (page.props.clearanceRequest as { id?: number; status?: string } | null | undefined) ?? null;
});

const canPrintReceipt = computed(() => {
    if (userRole.value !== 'student') {
        return false;
    }

    if (studentClearance.value?.is_cleared) {
        return true;
    }

    if (dashboardClearance.value?.status === 'cleared') {
        return true;
    }

    return false;
});

const receiptClearanceId = computed(() => {
    return studentClearance.value?.id ?? dashboardClearance.value?.id ?? null;
});

const currentUrl = computed(() => page.url?.split('?')[0] ?? '');

const isUrlActive = (target: string) => {
    if (target === '/dashboard' || target === '/admin/dashboard') {
        return currentUrl.value === target;
    }
    return currentUrl.value === target || currentUrl.value.startsWith(target + '/');
};

const showMoreSheet = ref(false);
const showAlertsSheet = ref(false);
const showLogoutDialog = ref(false);

const openMoreSheet = () => {
    showMoreSheet.value = true;
};

const closeMoreSheet = () => {
    showMoreSheet.value = false;
};

const openAlertsSheet = () => {
    showAlertsSheet.value = true;
};

const closeAlertsSheet = () => {
    showAlertsSheet.value = false;
};

const openLogoutDialog = () => {
    showMoreSheet.value = false;
    showLogoutDialog.value = true;
};

const closeLogoutDialog = () => {
    showLogoutDialog.value = false;
};

const handleLogout = () => {
    router.flushAll();
};

const markAllAsRead = () => {
    router.patch(
        '/notifications/mark-all-as-read',
        {},
        {
            preserveScroll: true,
        },
    );
};

// Student interactive actions
const handleStudentStatusClick = () => {
    if (currentUrl.value === '/dashboard') {
        window.dispatchEvent(new CustomEvent('open-clearance-status'));
    } else {
        router.visit('/dashboard?view=status');
    }
};

const handleStudentRequestClick = () => {
    if (currentUrl.value === '/dashboard') {
        window.dispatchEvent(new CustomEvent('open-submit-request'));
    } else {
        router.visit('/dashboard?view=request');
    }
};

const handleStudentHomeClick = () => {
    if (currentUrl.value === '/dashboard') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        router.visit('/dashboard');
    }
};

const handleReceiptClick = () => {
    if (receiptClearanceId.value) {
        showMoreSheet.value = false;
        router.visit(`/clearance-receipts/${receiptClearanceId.value}`);
    }
};
</script>

<template>
    <!-- Floating Pop-up Circle Button for Print Clearance (Student Only when Fully Cleared) -->
    <div
        v-if="canPrintReceipt"
        class="fixed bottom-22 right-4 z-40 animate-in fade-in zoom-in slide-in-from-bottom-4 duration-300 md:hidden"
    >
        <button
            type="button"
            class="group relative flex h-13 w-13 items-center justify-center rounded-full bg-gradient-to-tr from-emerald-600 via-emerald-500 to-teal-400 text-white shadow-xl shadow-emerald-950/30 ring-4 ring-white transition-all hover:scale-105 active:scale-95"
            @click="handleReceiptClick"
            title="Print Clearance Receipt"
        >
            <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
                <span
                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-300 opacity-75"
                ></span>
                <span
                    class="relative inline-flex h-3.5 w-3.5 rounded-full bg-emerald-400 ring-2 ring-white"
                ></span>
            </span>
            <Printer class="size-6 drop-shadow-sm transition group-hover:scale-110" />
        </button>
    </div>

    <!-- Main Mobile Bottom Navigation Bar -->
    <nav
        class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200/80 bg-white/95 px-3 pt-1 pb-[max(env(safe-area-inset-bottom),0.75rem)] shadow-[0_-4px_24px_rgba(0,0,0,0.06)] backdrop-blur-xl md:hidden"
    >
        <!-- ADMIN NAVIGATION (5 items) -->
        <div
            v-if="userRole === 'admin'"
            class="grid grid-cols-5 items-end justify-items-center gap-1"
        >
            <!-- Users -->
            <Link
                href="/admin/users"
                class="flex w-full flex-col items-center justify-center py-1 transition active:scale-95"
                :class="isUrlActive('/admin/users') ? 'text-blue-950' : 'text-slate-500'"
            >
                <Users class="size-5" />
                <span
                    class="mt-0.5 h-1 w-1 rounded-full transition-all"
                    :class="isUrlActive('/admin/users') ? 'bg-blue-700' : 'bg-transparent'"
                ></span>
                <span class="text-[0.65rem] font-bold tracking-tight">Users</span>
            </Link>

            <!-- Clearances -->
            <Link
                href="/admin/clearance-requests"
                class="flex w-full flex-col items-center justify-center py-1 transition active:scale-95"
                :class="isUrlActive('/admin/clearance-requests') ? 'text-blue-950' : 'text-slate-500'"
            >
                <ClipboardCheck class="size-5" />
                <span
                    class="mt-0.5 h-1 w-1 rounded-full transition-all"
                    :class="isUrlActive('/admin/clearance-requests') ? 'bg-blue-700' : 'bg-transparent'"
                ></span>
                <span class="text-[0.65rem] font-bold tracking-tight">Clearance</span>
            </Link>

            <!-- Elevated Center Focal Button: Dashboard -->
            <div class="-mt-5 flex flex-col items-center justify-center">
                <Link
                    href="/admin/dashboard"
                    class="flex h-12 w-12 items-center justify-center rounded-2xl ring-4 ring-white shadow-lg transition-transform active:scale-95"
                    :class="
                        isUrlActive('/admin/dashboard')
                            ? 'bg-gradient-to-tr from-blue-950 via-blue-800 to-indigo-600 text-white shadow-blue-900/30'
                            : 'border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100'
                    "
                >
                    <LayoutGrid class="size-6" />
                </Link>
                <span
                    class="mt-1 text-[0.65rem] font-black tracking-tight"
                    :class="isUrlActive('/admin/dashboard') ? 'text-blue-950 font-black' : 'text-slate-500'"
                >
                    Home
                </span>
            </div>

            <!-- Reports -->
            <Link
                href="/admin/reports"
                class="flex w-full flex-col items-center justify-center py-1 transition active:scale-95"
                :class="isUrlActive('/admin/reports') ? 'text-blue-950' : 'text-slate-500'"
            >
                <FileText class="size-5" />
                <span
                    class="mt-0.5 h-1 w-1 rounded-full transition-all"
                    :class="isUrlActive('/admin/reports') ? 'bg-blue-700' : 'bg-transparent'"
                ></span>
                <span class="text-[0.65rem] font-bold tracking-tight">Reports</span>
            </Link>

            <!-- More -->
            <button
                type="button"
                class="flex w-full flex-col items-center justify-center py-1 text-slate-500 transition active:scale-95 hover:text-slate-900"
                @click="openMoreSheet"
            >
                <MoreHorizontal class="size-5" />
                <span class="mt-0.5 h-1 w-1 rounded-full bg-transparent"></span>
                <span class="text-[0.65rem] font-bold tracking-tight">More</span>
            </button>
        </div>

        <!-- STUDENT NAVIGATION (5 items) -->
        <div
            v-else-if="userRole === 'student'"
            class="grid grid-cols-5 items-end justify-items-center gap-1"
        >
            <!-- Status Modal Trigger -->
            <button
                type="button"
                class="flex w-full flex-col items-center justify-center py-1 text-slate-500 transition active:scale-95 hover:text-blue-950"
                @click="handleStudentStatusClick"
            >
                <ClipboardCheck class="size-5" />
                <span class="mt-0.5 h-1 w-1 rounded-full bg-transparent"></span>
                <span class="text-[0.65rem] font-bold tracking-tight">Status</span>
            </button>

            <!-- Request Modal Trigger -->
            <button
                type="button"
                class="flex w-full flex-col items-center justify-center py-1 text-slate-500 transition active:scale-95 hover:text-blue-950"
                @click="handleStudentRequestClick"
            >
                <FilePlus class="size-5" />
                <span class="mt-0.5 h-1 w-1 rounded-full bg-transparent"></span>
                <span class="text-[0.65rem] font-bold tracking-tight">Request</span>
            </button>

            <!-- Elevated Center Focal Button: Dashboard -->
            <div class="-mt-5 flex flex-col items-center justify-center">
                <button
                    type="button"
                    class="flex h-12 w-12 items-center justify-center rounded-2xl ring-4 ring-white shadow-lg transition-transform active:scale-95"
                    :class="
                        isUrlActive('/dashboard')
                            ? 'bg-gradient-to-tr from-blue-950 via-blue-800 to-indigo-600 text-white shadow-blue-900/30'
                            : 'border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100'
                    "
                    @click="handleStudentHomeClick"
                >
                    <LayoutGrid class="size-6" />
                </button>
                <span
                    class="mt-1 text-[0.65rem] font-black tracking-tight"
                    :class="isUrlActive('/dashboard') ? 'text-blue-950 font-black' : 'text-slate-500'"
                >
                    Home
                </span>
            </div>

            <!-- Alerts Drawer Trigger -->
            <button
                type="button"
                class="relative flex w-full flex-col items-center justify-center py-1 text-slate-500 transition active:scale-95 hover:text-blue-950"
                @click="openAlertsSheet"
            >
                <div class="relative">
                    <Bell class="size-5" />
                    <span
                        v-if="notifications.unread_count > 0"
                        class="absolute -top-1 -right-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[0.6rem] font-black text-white shadow-xs"
                    >
                        {{ notifications.unread_count > 99 ? '99+' : notifications.unread_count }}
                    </span>
                </div>
                <span class="mt-0.5 h-1 w-1 rounded-full bg-transparent"></span>
                <span class="text-[0.65rem] font-bold tracking-tight">Alerts</span>
            </button>

            <!-- More -->
            <button
                type="button"
                class="flex w-full flex-col items-center justify-center py-1 text-slate-500 transition active:scale-95 hover:text-slate-900"
                @click="openMoreSheet"
            >
                <MoreHorizontal class="size-5" />
                <span class="mt-0.5 h-1 w-1 rounded-full bg-transparent"></span>
                <span class="text-[0.65rem] font-bold tracking-tight">More</span>
            </button>
        </div>

        <!-- STAFF NAVIGATION (3 items - Symmetrical) -->
        <div
            v-else-if="userRole === 'staff'"
            class="grid grid-cols-3 items-end justify-items-center gap-2"
        >
            <!-- Alerts -->
            <button
                type="button"
                class="flex w-full flex-col items-center justify-center py-1 text-slate-500 transition active:scale-95 hover:text-blue-950"
                @click="openAlertsSheet"
            >
                <div class="relative">
                    <Bell class="size-5" />
                    <span
                        v-if="notifications.unread_count > 0"
                        class="absolute -top-1 -right-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[0.6rem] font-black text-white shadow-xs"
                    >
                        {{ notifications.unread_count > 99 ? '99+' : notifications.unread_count }}
                    </span>
                </div>
                <span class="mt-0.5 h-1 w-1 rounded-full bg-transparent"></span>
                <span class="text-[0.65rem] font-bold tracking-tight">Alerts</span>
            </button>

            <!-- Elevated Center Focal Button: Pending Requests -->
            <div class="-mt-5 flex flex-col items-center justify-center">
                <Link
                    href="/staff/pending-requests"
                    class="flex h-12 w-12 items-center justify-center rounded-2xl ring-4 ring-white shadow-lg transition-transform active:scale-95"
                    :class="
                        isUrlActive('/staff/pending-requests')
                            ? 'bg-gradient-to-tr from-blue-950 via-blue-800 to-indigo-600 text-white shadow-blue-900/30'
                            : 'border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100'
                    "
                >
                    <ClipboardCheck class="size-6" />
                </Link>
                <span
                    class="mt-1 text-[0.65rem] font-black tracking-tight"
                    :class="isUrlActive('/staff/pending-requests') ? 'text-blue-950 font-black' : 'text-slate-500'"
                >
                    Requests
                </span>
            </div>

            <!-- More -->
            <button
                type="button"
                class="flex w-full flex-col items-center justify-center py-1 text-slate-500 transition active:scale-95 hover:text-slate-900"
                @click="openMoreSheet"
            >
                <MoreHorizontal class="size-5" />
                <span class="mt-0.5 h-1 w-1 rounded-full bg-transparent"></span>
                <span class="text-[0.65rem] font-bold tracking-tight">More</span>
            </button>
        </div>

        <!-- PRESIDENT NAVIGATION (3 items - Symmetrical) -->
        <div
            v-else-if="userRole === 'president'"
            class="grid grid-cols-3 items-end justify-items-center gap-2"
        >
            <!-- Alerts -->
            <button
                type="button"
                class="flex w-full flex-col items-center justify-center py-1 text-slate-500 transition active:scale-95 hover:text-blue-950"
                @click="openAlertsSheet"
            >
                <div class="relative">
                    <Bell class="size-5" />
                    <span
                        v-if="notifications.unread_count > 0"
                        class="absolute -top-1 -right-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[0.6rem] font-black text-white shadow-xs"
                    >
                        {{ notifications.unread_count > 99 ? '99+' : notifications.unread_count }}
                    </span>
                </div>
                <span class="mt-0.5 h-1 w-1 rounded-full bg-transparent"></span>
                <span class="text-[0.65rem] font-bold tracking-tight">Alerts</span>
            </button>

            <!-- Elevated Center Focal Button: Final Approvals -->
            <div class="-mt-5 flex flex-col items-center justify-center">
                <Link
                    href="/president/final-approvals"
                    class="flex h-12 w-12 items-center justify-center rounded-2xl ring-4 ring-white shadow-lg transition-transform active:scale-95"
                    :class="
                        isUrlActive('/president/final-approvals')
                            ? 'bg-gradient-to-tr from-blue-950 via-blue-800 to-indigo-600 text-white shadow-blue-900/30'
                            : 'border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100'
                    "
                >
                    <ShieldCheck class="size-6" />
                </Link>
                <span
                    class="mt-1 text-[0.65rem] font-black tracking-tight"
                    :class="isUrlActive('/president/final-approvals') ? 'text-blue-950 font-black' : 'text-slate-500'"
                >
                    Approvals
                </span>
            </div>

            <!-- More -->
            <button
                type="button"
                class="flex w-full flex-col items-center justify-center py-1 text-slate-500 transition active:scale-95 hover:text-slate-900"
                @click="openMoreSheet"
            >
                <MoreHorizontal class="size-5" />
                <span class="mt-0.5 h-1 w-1 rounded-full bg-transparent"></span>
                <span class="text-[0.65rem] font-bold tracking-tight">More</span>
            </button>
        </div>
    </nav>

    <!-- SLIDE-UP "MORE" SHEET -->
    <Sheet v-model:open="showMoreSheet">
        <SheetContent
            side="bottom"
            class="max-h-[85vh] overflow-y-auto rounded-t-3xl border-slate-200 bg-white p-6 shadow-2xl"
        >
            <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-slate-300"></div>

            <SheetHeader class="text-left">
                <SheetTitle class="text-lg font-black text-slate-900">
                    Menu & Quick Actions
                </SheetTitle>
            </SheetHeader>

            <!-- User Info Card -->
            <div
                v-if="authUser"
                class="mt-4 flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-slate-50/80 p-3.5"
            >
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-950 text-sm font-black text-white shadow-sm"
                >
                    {{ getInitials(authUser.name) }}
                </div>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-black text-slate-950">
                        {{ authUser.name }}
                    </p>
                    <p class="truncate text-xs font-medium text-slate-500">
                        {{ authUser.email }}
                    </p>
                    <div class="mt-1 flex flex-wrap gap-1.5">
                        <span
                            class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-[0.65rem] font-black uppercase text-blue-900"
                        >
                            {{ userRole }}
                        </span>
                        <span
                            v-if="authUser.student_id"
                            class="inline-flex rounded-full bg-slate-200/80 px-2 py-0.5 text-[0.65rem] font-bold text-slate-700"
                        >
                            ID: {{ authUser.student_id }}
                        </span>
                        <span
                            v-if="authUser.course?.code"
                            class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[0.65rem] font-bold text-emerald-800"
                        >
                            {{ authUser.course.code }}
                        </span>
                        <span
                            v-if="authUser.year_level"
                            class="inline-flex rounded-full bg-indigo-100 px-2 py-0.5 text-[0.65rem] font-bold text-indigo-800"
                        >
                            {{ authUser.year_level }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Role-Specific Action Links -->
            <div class="mt-4 space-y-2">
                <!-- Admin Secondary Links -->
                <template v-if="userRole === 'admin'">
                    <Link
                        href="/admin/course-modules"
                        class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-xs transition hover:bg-slate-50 active:scale-[0.99]"
                        @click="closeMoreSheet"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700"
                            >
                                <Layers class="size-5" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">
                                    Course Modules
                                </p>
                                <p class="text-xs text-slate-500">
                                    Manage academic courses and assigned offices
                                </p>
                            </div>
                        </div>
                        <ChevronRight class="size-5 text-slate-400" />
                    </Link>

                    <Link
                        href="/admin/office-prerequisites"
                        class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-xs transition hover:bg-slate-50 active:scale-[0.99]"
                        @click="closeMoreSheet"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700"
                            >
                                <GraduationCap class="size-5" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">
                                    Office Prerequisites
                                </p>
                                <p class="text-xs text-slate-500">
                                    Configure dependency workflows for offices
                                </p>
                            </div>
                        </div>
                        <ChevronRight class="size-5 text-slate-400" />
                    </Link>

                    <Link
                        href="/admin/settings"
                        class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-xs transition hover:bg-slate-50 active:scale-[0.99]"
                        @click="closeMoreSheet"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700"
                            >
                                <Settings class="size-5" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">
                                    System Settings
                                </p>
                                <p class="text-xs text-slate-500">
                                    Active semester and clearance rules
                                </p>
                            </div>
                        </div>
                        <ChevronRight class="size-5 text-slate-400" />
                    </Link>
                </template>

                <!-- Student Secondary Actions -->
                <template v-else-if="userRole === 'student'">
                    <button
                        v-if="canPrintReceipt"
                        type="button"
                        class="flex w-full items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50/80 p-3.5 text-left shadow-xs transition hover:bg-emerald-100 active:scale-[0.99]"
                        @click="handleReceiptClick"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-emerald-600 text-white"
                            >
                                <Printer class="size-5" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-emerald-950">
                                    Print Clearance Receipt
                                </p>
                                <p class="text-xs text-emerald-700">
                                    Official verified receipt ready to print
                                </p>
                            </div>
                        </div>
                        <ChevronRight class="size-5 text-emerald-600" />
                    </button>

                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-2xl border border-slate-200/80 bg-white p-3.5 text-left shadow-xs transition hover:bg-slate-50 active:scale-[0.99]"
                        @click="
                            () => {
                                closeMoreSheet();
                                handleStudentStatusClick();
                            }
                        "
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700"
                            >
                                <CheckCircle2 class="size-5" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">
                                    View Clearance Breakdown
                                </p>
                                <p class="text-xs text-slate-500">
                                    Check approval status per assigned office
                                </p>
                            </div>
                        </div>
                        <ChevronRight class="size-5 text-slate-400" />
                    </button>
                </template>

                <!-- Staff Secondary Actions -->
                <template v-else-if="userRole === 'staff'">
                    <Link
                        href="/staff/pending-requests"
                        class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-xs transition hover:bg-slate-50 active:scale-[0.99]"
                        @click="closeMoreSheet"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700"
                            >
                                <ClipboardCheck class="size-5" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">
                                    Office Pending Queue
                                </p>
                                <p class="text-xs text-slate-500">
                                    Review students awaiting office signatures
                                </p>
                            </div>
                        </div>
                        <ChevronRight class="size-5 text-slate-400" />
                    </Link>
                </template>

                <!-- President Secondary Actions -->
                <template v-else-if="userRole === 'president'">
                    <Link
                        href="/president/final-approvals"
                        class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-xs transition hover:bg-slate-50 active:scale-[0.99]"
                        @click="closeMoreSheet"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700"
                            >
                                <ShieldCheck class="size-5" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">
                                    Final Clearance Approvals
                                </p>
                                <p class="text-xs text-slate-500">
                                    Grant official completion signatures
                                </p>
                            </div>
                        </div>
                        <ChevronRight class="size-5 text-slate-400" />
                    </Link>
                </template>

                <!-- Account & Logout Action -->
                <button
                    type="button"
                    class="flex w-full items-center justify-between rounded-2xl border border-red-200 bg-red-50/60 p-3.5 text-left transition hover:bg-red-100/70 active:scale-[0.99]"
                    @click="openLogoutDialog"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-red-100 text-red-600"
                        >
                            <LogOut class="size-5" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-red-700">
                                Sign Out
                            </p>
                            <p class="text-xs text-red-600/80">
                                Log out of your account
                            </p>
                        </div>
                    </div>
                    <ChevronRight class="size-5 text-red-400" />
                </button>
            </div>
        </SheetContent>
    </Sheet>

    <!-- SLIDE-UP "ALERTS" SHEET -->
    <Sheet v-model:open="showAlertsSheet">
        <SheetContent
            side="bottom"
            class="max-h-[85vh] overflow-y-auto rounded-t-3xl border-slate-200 bg-white p-6 shadow-2xl"
        >
            <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-slate-300"></div>

            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <SheetTitle class="text-lg font-black text-slate-900">
                        Notifications
                    </SheetTitle>
                    <p class="text-xs font-medium text-slate-500">
                        {{ notifications.unread_count }} unread message(s)
                    </p>
                </div>

                <button
                    v-if="notifications.unread_count > 0"
                    type="button"
                    class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 transition hover:bg-slate-200"
                    @click="markAllAsRead"
                >
                    Mark all as read
                </button>
            </div>

            <div class="mt-4">
                <div
                    v-if="notifications.items.length === 0"
                    class="py-10 text-center text-sm font-medium text-slate-500"
                >
                    No notifications yet.
                </div>

                <div v-else class="space-y-2">
                    <Link
                        v-for="notification in notifications.items"
                        :key="notification.id"
                        :href="`/notifications/${notification.id}/open`"
                        class="block rounded-2xl border border-slate-100 p-3.5 text-left transition hover:bg-slate-50"
                        :class="notification.read_at ? 'bg-white opacity-80' : 'bg-slate-50/90 font-medium'"
                        @click="closeAlertsSheet"
                    >
                        <div class="flex items-start gap-3">
                            <span
                                class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full"
                                :class="notification.read_at ? 'bg-slate-300' : 'bg-blue-600 ring-2 ring-blue-100'"
                            ></span>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-900">
                                    {{ notification.title }}
                                </p>
                                <p class="mt-0.5 text-xs text-slate-600 line-clamp-2">
                                    {{ notification.message }}
                                </p>
                                <p
                                    v-if="notification.created_at_human"
                                    class="mt-1 text-[0.65rem] font-semibold text-slate-400"
                                >
                                    {{ notification.created_at_human }}
                                </p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </SheetContent>
    </Sheet>

    <!-- LOGOUT CONFIRMATION DIALOG -->
    <Dialog v-model:open="showLogoutDialog">
        <DialogContent class="rounded-3xl border border-slate-200 p-0 shadow-2xl sm:max-w-md">
            <div class="p-6">
                <DialogHeader>
                    <div class="flex items-start gap-4">
                        <div
                            class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-red-50 text-red-600"
                        >
                            <ShieldAlert class="size-6" />
                        </div>

                        <div>
                            <DialogTitle class="text-xl font-black text-slate-950">
                                Confirm logout
                            </DialogTitle>

                            <DialogDescription class="mt-2 text-sm leading-6 text-slate-600">
                                Are you sure you want to log out of your account? You will need to sign in again to continue using the clearance system.
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
