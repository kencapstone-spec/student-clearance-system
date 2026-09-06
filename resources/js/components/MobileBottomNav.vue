<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    ChevronRight,
    ClipboardCheck,
    FilePlus,
    FileText,
    GraduationCap,
    LayoutGrid,
    Layers,
    MoreHorizontal,
    Printer,
    Settings,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Sheet, SheetContent, SheetTitle } from '@/components/ui/sheet';
import { useInitials } from '@/composables/useInitials';

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

type StudentClearanceProp = {
    id: number;
    is_cleared: boolean;
};

const page = usePage();
const { getInitials } = useInitials();

const authUser = computed(() => (page.props.auth as { user?: AuthUser })?.user);
const userRole = computed(() => authUser.value?.role ?? 'student');

const studentClearance = computed<StudentClearanceProp | null>(() => {
    return (
        (page.props.studentClearance as
            | StudentClearanceProp
            | null
            | undefined) ?? null
    );
});

// Also check dashboard specific clearanceRequest prop
const dashboardClearance = computed(() => {
    return (
        (page.props.clearanceRequest as
            | { id?: number; status?: string }
            | null
            | undefined) ?? null
    );
});

const canPrintReceipt = computed(() => {
    if (userRole.value !== 'student') {
        return false;
    }

    if (currentUrl.value.startsWith('/clearance-receipts')) {
        return false;
    }

    // When on the student dashboard, the active term's clearanceRequest is authoritative
    if (currentUrl.value === '/dashboard') {
        return dashboardClearance.value?.status === 'cleared';
    }

    return Boolean(studentClearance.value?.is_cleared);
});

const receiptClearanceId = computed(() => {
    if (currentUrl.value === '/dashboard') {
        return dashboardClearance.value?.status === 'cleared'
            ? dashboardClearance.value.id
            : null;
    }

    return studentClearance.value?.is_cleared
        ? studentClearance.value.id
        : null;
});

const currentUrl = computed(() => page.url?.split('?')[0] ?? '');

const isUrlActive = (target: string) => {
    if (target === '/dashboard' || target === '/admin/dashboard') {
        return currentUrl.value === target;
    }

    return (
        currentUrl.value === target || currentUrl.value.startsWith(target + '/')
    );
};

const showMoreSheet = ref(false);

const openMoreSheet = () => {
    showMoreSheet.value = true;
};

const closeMoreSheet = () => {
    showMoreSheet.value = false;
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
        class="fixed right-4 bottom-22 z-40 animate-in duration-300 fade-in slide-in-from-bottom-4 zoom-in md:hidden print:hidden"
    >
        <button
            type="button"
            class="group relative flex h-13 w-13 items-center justify-center rounded-full bg-gradient-to-tr from-emerald-600 via-emerald-500 to-teal-400 text-white shadow-xl ring-4 shadow-emerald-950/30 ring-white transition-all hover:scale-105 active:scale-95"
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
            <Printer
                class="size-6 drop-shadow-sm transition group-hover:scale-110"
            />
        </button>
    </div>

    <!-- Main Mobile Bottom Navigation Bar -->
    <nav
        v-if="userRole === 'admin' || userRole === 'student'"
        class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200/80 bg-white/95 px-3 pt-1 pb-[max(env(safe-area-inset-bottom),0.75rem)] shadow-[0_-4px_24px_rgba(0,0,0,0.06)] backdrop-blur-xl md:hidden print:hidden"
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
                :class="
                    isUrlActive('/admin/users')
                        ? 'text-blue-950'
                        : 'text-slate-500'
                "
            >
                <Users class="size-5" />
                <span
                    class="mt-0.5 h-1 w-1 rounded-full transition-all"
                    :class="
                        isUrlActive('/admin/users')
                            ? 'bg-blue-700'
                            : 'bg-transparent'
                    "
                ></span>
                <span class="text-[0.65rem] font-bold tracking-tight"
                    >Users</span
                >
            </Link>

            <!-- Clearances -->
            <Link
                href="/admin/clearance-requests"
                class="flex w-full flex-col items-center justify-center py-1 transition active:scale-95"
                :class="
                    isUrlActive('/admin/clearance-requests')
                        ? 'text-blue-950'
                        : 'text-slate-500'
                "
            >
                <ClipboardCheck class="size-5" />
                <span
                    class="mt-0.5 h-1 w-1 rounded-full transition-all"
                    :class="
                        isUrlActive('/admin/clearance-requests')
                            ? 'bg-blue-700'
                            : 'bg-transparent'
                    "
                ></span>
                <span class="text-[0.65rem] font-bold tracking-tight"
                    >Clearance</span
                >
            </Link>

            <!-- Elevated Center Focal Button: Dashboard -->
            <div class="-mt-5 flex flex-col items-center justify-center">
                <Link
                    href="/admin/dashboard"
                    class="flex h-12 w-12 items-center justify-center rounded-2xl shadow-lg ring-4 ring-white transition-transform active:scale-95"
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
                    :class="
                        isUrlActive('/admin/dashboard')
                            ? 'font-black text-blue-950'
                            : 'text-slate-500'
                    "
                >
                    Home
                </span>
            </div>

            <!-- Reports -->
            <Link
                href="/admin/reports"
                class="flex w-full flex-col items-center justify-center py-1 transition active:scale-95"
                :class="
                    isUrlActive('/admin/reports')
                        ? 'text-blue-950'
                        : 'text-slate-500'
                "
            >
                <FileText class="size-5" />
                <span
                    class="mt-0.5 h-1 w-1 rounded-full transition-all"
                    :class="
                        isUrlActive('/admin/reports')
                            ? 'bg-blue-700'
                            : 'bg-transparent'
                    "
                ></span>
                <span class="text-[0.65rem] font-bold tracking-tight"
                    >Reports</span
                >
            </Link>

            <!-- More -->
            <button
                type="button"
                class="flex w-full flex-col items-center justify-center py-1 text-slate-500 transition hover:text-slate-900 active:scale-95"
                @click="openMoreSheet"
            >
                <MoreHorizontal class="size-5" />
                <span class="mt-0.5 h-1 w-1 rounded-full bg-transparent"></span>
                <span class="text-[0.65rem] font-bold tracking-tight"
                    >More</span
                >
            </button>
        </div>

        <!-- STUDENT NAVIGATION (3 items - Symmetrical: Request, Home, Status) -->
        <div
            v-else-if="userRole === 'student'"
            class="grid grid-cols-3 items-end justify-items-center gap-2"
        >
            <!-- Request Modal Trigger -->
            <button
                type="button"
                class="flex w-full flex-col items-center justify-center py-1 text-slate-500 transition hover:text-blue-950 active:scale-95"
                @click="handleStudentRequestClick"
            >
                <FilePlus class="size-5" />
                <span class="mt-0.5 h-1 w-1 rounded-full bg-transparent"></span>
                <span class="text-[0.65rem] font-bold tracking-tight">
                    Request
                </span>
            </button>

            <!-- Elevated Center Focal Button: Dashboard -->
            <div class="-mt-5 flex flex-col items-center justify-center">
                <button
                    type="button"
                    class="flex h-12 w-12 items-center justify-center rounded-2xl shadow-lg ring-4 ring-white transition-transform active:scale-95"
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
                    :class="
                        isUrlActive('/dashboard')
                            ? 'font-black text-blue-950'
                            : 'text-slate-500'
                    "
                >
                    Home
                </span>
            </div>

            <!-- Status Modal Trigger -->
            <button
                type="button"
                class="flex w-full flex-col items-center justify-center py-1 text-slate-500 transition hover:text-blue-950 active:scale-95"
                @click="handleStudentStatusClick"
            >
                <ClipboardCheck class="size-5" />
                <span class="mt-0.5 h-1 w-1 rounded-full bg-transparent"></span>
                <span class="text-[0.65rem] font-bold tracking-tight">
                    Status
                </span>
            </button>
        </div>
    </nav>

    <!-- SLIDE-UP "MORE" SHEET -->
    <Sheet v-model:open="showMoreSheet">
        <SheetContent
            side="bottom"
            class="max-h-[85vh] gap-0 overflow-y-auto rounded-t-[1.75rem] border-t border-slate-200 bg-white px-4 pt-2.5 pb-8 shadow-2xl focus:outline-hidden sm:px-6 sm:pb-8"
        >
            <!-- Drag handle -->
            <div
                class="mx-auto my-1.5 h-1 w-10 rounded-full bg-slate-300"
            ></div>

            <!-- Header -->
            <div class="pt-1 pr-8 pb-3">
                <SheetTitle
                    class="text-base font-black tracking-tight text-slate-950"
                >
                    Menu & Quick Actions
                </SheetTitle>
                <p class="mt-0.5 text-xs font-medium text-slate-500">
                    Account details and system navigation
                </p>
            </div>

            <!-- User Info Card -->
            <div
                v-if="authUser"
                class="mb-3.5 flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-slate-50 p-3 shadow-xs"
            >
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-950 via-blue-900 to-indigo-900 text-xs font-black text-white shadow-xs"
                >
                    {{ getInitials(authUser.name) }}
                </div>

                <div class="min-w-0 flex-1">
                    <p
                        class="truncate text-sm leading-tight font-black text-slate-950"
                    >
                        {{ authUser.name }}
                    </p>
                    <p
                        class="mt-0.5 truncate text-xs font-medium text-slate-500"
                    >
                        {{ authUser.email }}
                    </p>
                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                        <span
                            class="inline-flex items-center rounded-md bg-blue-100/80 px-2 py-0.5 text-[0.625rem] font-bold tracking-wide text-blue-900 uppercase"
                        >
                            {{ userRole }}
                        </span>
                        <span
                            v-if="authUser.student_id"
                            class="inline-flex items-center rounded-md bg-slate-200/80 px-2 py-0.5 text-[0.625rem] font-medium text-slate-700"
                        >
                            ID: {{ authUser.student_id }}
                        </span>
                        <span
                            v-if="authUser.course?.code"
                            class="inline-flex items-center rounded-md border border-emerald-200/60 bg-emerald-50 px-2 py-0.5 text-[0.625rem] font-semibold text-emerald-700"
                        >
                            {{ authUser.course.code }}
                        </span>
                        <span
                            v-if="authUser.year_level"
                            class="inline-flex items-center rounded-md border border-indigo-200/60 bg-indigo-50 px-2 py-0.5 text-[0.625rem] font-semibold text-indigo-700"
                        >
                            {{ authUser.year_level }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Category label -->
            <p
                class="mb-2 px-1 text-[0.65rem] font-bold tracking-wider text-slate-400 uppercase"
            >
                Navigation & Shortcuts
            </p>

            <!-- Role-Specific Action Links -->
            <div class="space-y-2">
                <!-- Admin Secondary Links -->
                <template v-if="userRole === 'admin'">
                    <Link
                        href="/admin/course-modules"
                        class="group flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-slate-50/70 p-3 shadow-xs transition-all hover:border-slate-300 hover:bg-slate-100/80 focus:outline-hidden active:scale-[0.99]"
                        @click="closeMoreSheet"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-100/80 text-blue-700 transition group-hover:bg-blue-100"
                        >
                            <Layers class="size-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="text-sm leading-tight font-bold text-slate-900"
                            >
                                Course Modules
                            </p>
                            <p
                                class="mt-0.5 text-xs leading-snug text-slate-500"
                            >
                                Manage academic courses and assigned offices
                            </p>
                        </div>
                        <ChevronRight
                            class="size-4.5 shrink-0 text-slate-400 transition-transform group-hover:translate-x-0.5"
                        />
                    </Link>

                    <Link
                        href="/admin/office-prerequisites"
                        class="group flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-slate-50/70 p-3 shadow-xs transition-all hover:border-slate-300 hover:bg-slate-100/80 focus:outline-hidden active:scale-[0.99]"
                        @click="closeMoreSheet"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100/80 text-indigo-700 transition group-hover:bg-indigo-100"
                        >
                            <GraduationCap class="size-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="text-sm leading-tight font-bold text-slate-900"
                            >
                                Office Prerequisites
                            </p>
                            <p
                                class="mt-0.5 text-xs leading-snug text-slate-500"
                            >
                                Configure dependency workflows for offices
                            </p>
                        </div>
                        <ChevronRight
                            class="size-4.5 shrink-0 text-slate-400 transition-transform group-hover:translate-x-0.5"
                        />
                    </Link>

                    <Link
                        href="/admin/settings"
                        class="group flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-slate-50/70 p-3 shadow-xs transition-all hover:border-slate-300 hover:bg-slate-100/80 focus:outline-hidden active:scale-[0.99]"
                        @click="closeMoreSheet"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-slate-200/80 text-slate-700 transition group-hover:bg-slate-300/70"
                        >
                            <Settings class="size-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="text-sm leading-tight font-bold text-slate-900"
                            >
                                System Settings
                            </p>
                            <p
                                class="mt-0.5 text-xs leading-snug text-slate-500"
                            >
                                Active semester and clearance rules
                            </p>
                        </div>
                        <ChevronRight
                            class="size-4.5 shrink-0 text-slate-400 transition-transform group-hover:translate-x-0.5"
                        />
                    </Link>
                </template>

                <!-- Student Official Clearance Receipt Action -->
                <button
                    v-if="userRole === 'student' && canPrintReceipt"
                    type="button"
                    class="group flex w-full items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-3 text-left transition hover:bg-emerald-100/80 focus:outline-hidden active:scale-[0.99]"
                    @click="handleReceiptClick"
                >
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700"
                    >
                        <Printer class="size-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p
                            class="text-sm leading-tight font-bold text-emerald-900"
                        >
                            Official Clearance Receipt
                        </p>
                        <p
                            class="mt-0.5 text-xs leading-snug text-emerald-700/80"
                        >
                            View or print your signed clearance
                        </p>
                    </div>
                    <ChevronRight
                        class="size-4.5 shrink-0 text-emerald-500 transition-transform group-hover:translate-x-0.5"
                    />
                </button>
            </div>
        </SheetContent>
    </Sheet>
</template>
