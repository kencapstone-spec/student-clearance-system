<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle2, Download, ChevronRight, LogOut } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { dashboard } from '@/routes';
import { resolveCourseTheme } from '@/utils/courseThemes';
import type { CourseTheme } from '@/utils/courseThemes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

type Course = {
    id: number;
    code: string;
    name: string;
};

type Student = {
    id: number;
    name: string;
    student_id: string;
    role: string;
    course?: Course | null;
};

type Office = {
    id: number;
    name: string;
    group: string;
    sort_order: number;
    is_final_approver: boolean;
    prerequisites?: { id: number; name: string }[];
};

type Approval = {
    id: number;
    office_id: number;
    status: 'not_requested' | 'pending' | 'approved' | 'rejected';
    remarks?: string | null;
    office?: Office;
};

type ClearanceRequest = {
    id: number;
    semester: string;
    school_year: string;
    status: 'pending' | 'cleared';
    approvals: Approval[];
} | null;

const props = defineProps<{
    student: Student;
    offices: Office[];
    clearanceRequest: ClearanceRequest;
}>();

const courseTheme = computed<CourseTheme>(() =>
    resolveCourseTheme(props.student.course?.code),
);

const approvals = computed(() => props.clearanceRequest?.approvals ?? []);

const approvedCount = computed(() => {
    return approvals.value.filter((approval) => approval.status === 'approved')
        .length;
});

const pendingCount = computed(() => {
    if (!props.clearanceRequest) {
        return 0;
    }

    return approvals.value.filter((approval) => approval.status === 'pending')
        .length;
});

const notClearedCount = computed(() => {
    if (!props.clearanceRequest) {
        return props.offices.length;
    }

    return props.offices.length - approvedCount.value;
});

const regularApprovals = computed(() => {
    if (!props.clearanceRequest) {
        return [];
    }

    return props.clearanceRequest.approvals.filter((approval) => {
        return !approval.office?.is_final_approver;
    });
});

const presidentApproval = computed(() => {
    if (!props.clearanceRequest) {
        return null;
    }

    return (
        props.clearanceRequest.approvals.find((approval) => {
            return approval.office?.is_final_approver;
        }) ?? null
    );
});

const isFullyCleared = computed(() => {
    if (!props.clearanceRequest) {
        return false;
    }

    if (regularApprovals.value.length === 0) {
        return false;
    }

    const allRegularApproved = regularApprovals.value.every((approval) => {
        return approval.status === 'approved';
    });

    const presidentApproved = presidentApproval.value?.status === 'approved';

    return allRegularApproved && presidentApproved;
});

const finalClearanceLabel = computed(() => {
    if (isFullyCleared.value) {
        return 'Fully Cleared';
    }

    if (props.clearanceRequest) {
        return 'In Progress';
    }

    return 'Not Started';
});

const progressPercentage = computed(() => {
    if (isFullyCleared.value) {
        return 100;
    }

    if (props.offices.length === 0) {
        return 0;
    }

    return Math.round((approvedCount.value / props.offices.length) * 100);
});

const officeStatuses = computed(() => {
    const statuses = props.offices.map((office) => {
        const approval = approvals.value.find(
            (item) => item.office_id === office.id,
        );

        return {
            ...office,
            approvalId: approval?.id ?? null,
            status: approval?.status ?? 'not_started',
            remarks: approval?.remarks ?? null,
        };
    });

    return statuses.sort((a, b) => {
        const aCount = a.prerequisites?.length || 0;
        const bCount = b.prerequisites?.length || 0;

        if (aCount !== bCount) {
            return aCount - bCount;
        }

        return a.sort_order - b.sort_order;
    });
});

const progressMessage = computed(() => {
    if (!props.clearanceRequest) {
        return 'Submit your first clearance request to start your clearance progress.';
    }

    if (isFullyCleared.value) {
        return 'Your clearance is fully cleared and approved by the College President.';
    }

    if (progressPercentage.value === 100) {
        return 'All offices have approved your clearance request. Waiting for final approval.';
    }

    return 'Keep going! Your clearance request is being processed.';
});

const statusLabel = (status: string) => {
    if (status === 'approved') {
        return 'Cleared';
    }

    if (status === 'pending') {
        return 'Pending';
    }

    if (status === 'rejected') {
        return 'Not Cleared';
    }

    if (status === 'not_requested') {
        return 'Not Requested';
    }

    return 'Not Started';
};

const statusClass = (status: string) => {
    if (status === 'approved') {
        return 'text-green-700';
    }

    if (status === 'pending') {
        return 'text-orange-600';
    }

    if (status === 'rejected') {
        return 'text-red-600';
    }

    if (status === 'not_requested') {
        return 'text-slate-500';
    }

    return 'text-slate-500';
};

const showClearanceDetailsModal = ref(false);

const showSubmitRequestModal = ref(false);

const selectedOfficeIds = ref<number[]>([]);

const selectedCompliedApproval = ref<{
    approvalId: number | null;
    officeName: string;
} | null>(null);

const showMobileMoreMenu = ref(false);

const showMobileLogoutModal = ref(false);

const activeMobileNav = ref<'home' | 'request' | 'offices' | 'more'>('home');

const courseCode = computed(() => {
    return props.student.course?.code ?? 'N/A';
});

const studentInitials = computed(() => {
    return props.student.name
        .split(' ')
        .map((name) => name.charAt(0))
        .slice(0, 2)
        .join('');
});

const openClearanceDetailsModal = () => {
    showMobileMoreMenu.value = false;
    showClearanceDetailsModal.value = true;
};

const closeClearanceDetailsModal = () => {
    showClearanceDetailsModal.value = false;
    activeMobileNav.value = 'home';
};

const openClearanceReceipt = () => {
    if (!props.clearanceRequest || !isFullyCleared.value) {
        return;
    }

    showMobileMoreMenu.value = false;
    router.visit(`/clearance-receipts/${props.clearanceRequest.id}`);
};

const regularOffices = computed(() => {
    return props.offices.filter((office) => !office.is_final_approver);
});

const requestableOffices = computed(() => {
    let offices = [];

    if (!props.clearanceRequest) {
        offices = regularOffices.value;
    } else {
        offices = officeStatuses.value.filter((office) => {
            return (
                !office.is_final_approver && office.status === 'not_requested'
            );
        });
    }

    return [...offices].sort((a, b) => {
        const aCount = a.prerequisites?.length || 0;
        const bCount = b.prerequisites?.length || 0;

        if (aCount !== bCount) {
            return aCount - bCount;
        }

        // Fallback to sort_order if they have the same number of prerequisites
        return a.sort_order - b.sort_order;
    });
});

const isOfficeRequestable = (office: Office) => {
    if (!office.prerequisites || office.prerequisites.length === 0) {
        return true;
    }

    if (!props.clearanceRequest) {
        return false;
    }

    return office.prerequisites.every((prereq) => {
        const approval = props.clearanceRequest!.approvals.find(
            (a) => a.office_id === prereq.id,
        );

        return approval?.status === 'approved';
    });
};

const unmetPrerequisites = (office: Office) => {
    if (!office.prerequisites || office.prerequisites.length === 0) {
        return [];
    }

    if (!props.clearanceRequest) {
        return office.prerequisites.map((p) => p.name);
    }

    return office.prerequisites
        .filter((prereq) => {
            const approval = props.clearanceRequest!.approvals.find(
                (a) => a.office_id === prereq.id,
            );

            return approval?.status !== 'approved';
        })
        .map((p) => p.name);
};

const openSubmitRequestModal = () => {
    selectedOfficeIds.value = [];
    showMobileMoreMenu.value = false;
    showSubmitRequestModal.value = true;
};

const closeSubmitRequestModal = () => {
    selectedOfficeIds.value = [];
    showSubmitRequestModal.value = false;
    activeMobileNav.value = 'home';
};

const toggleOfficeSelection = (officeId: number) => {
    const office = props.offices.find((o) => o.id === officeId);

    if (!office || !isOfficeRequestable(office)) {
        return;
    }

    if (selectedOfficeIds.value.includes(officeId)) {
        selectedOfficeIds.value = selectedOfficeIds.value.filter(
            (id) => id !== officeId,
        );

        return;
    }

    selectedOfficeIds.value = [...selectedOfficeIds.value, officeId];
};

const openMarkAsCompliedModal = (
    approvalId: number | null,
    officeName: string,
) => {
    selectedCompliedApproval.value = {
        approvalId,
        officeName,
    };
};

const closeMarkAsCompliedModal = () => {
    selectedCompliedApproval.value = null;
};

const confirmMarkAsComplied = () => {
    if (!selectedCompliedApproval.value) {
        return;
    }

    markAsComplied(selectedCompliedApproval.value.approvalId);
    closeMarkAsCompliedModal();
};

const markAsComplied = (approvalId: number | null) => {
    if (!approvalId) {
        return;
    }

    router.patch(`/student/clearance-approvals/${approvalId}/mark-as-complied`);
};

const submitClearanceRequest = () => {
    if (selectedOfficeIds.value.length === 0) {
        return;
    }

    const payload = {
        office_ids: selectedOfficeIds.value,
    };

    const options = {
        onSuccess: () => {
            closeSubmitRequestModal();
        },
    };

    if (props.clearanceRequest) {
        router.patch(
            '/student/clearance-requests/request-more-offices',
            payload,
            options,
        );

        return;
    }

    router.post('/student/clearance-requests', payload, options);
};

const scrollToDashboardTop = () => {
    activeMobileNav.value = 'home';
    showMobileMoreMenu.value = false;

    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    });
};

const openMobileRequests = () => {
    showMobileMoreMenu.value = false;

    if (requestableOffices.value.length === 0) {
        return;
    }

    activeMobileNav.value = 'request';
    openSubmitRequestModal();
};

const openMobileOffices = () => {
    activeMobileNav.value = 'offices';
    showMobileMoreMenu.value = false;
    openClearanceDetailsModal();
};

const toggleMobileMoreMenu = () => {
    activeMobileNav.value = 'more';
    showMobileMoreMenu.value = !showMobileMoreMenu.value;
};

const closeMobileMoreMenu = () => {
    showMobileMoreMenu.value = false;
    activeMobileNav.value = 'home';
};

const openMobileLogoutModal = () => {
    showMobileMoreMenu.value = false;
    showMobileLogoutModal.value = true;
};

const closeMobileLogoutModal = () => {
    showMobileLogoutModal.value = false;
};

const confirmMobileLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <Head title="Student Dashboard" />

    <div
        class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50/40 p-3 pb-28 text-slate-900 sm:p-4 sm:pb-28 md:p-6 md:pb-6"
    >
        <div class="mx-auto flex max-w-7xl flex-col gap-4 md:gap-6">
            <!-- Welcome Banner -->
            <section
                class="overflow-hidden rounded-3xl border shadow-xl shadow-slate-200/70 md:rounded-[2rem]"
                :class="courseTheme.bannerClass"
            >
                <div
                    class="grid gap-4 p-4 md:grid-cols-[1fr_360px] md:gap-6 md:p-8"
                >
                    <div class="flex flex-col justify-center gap-4 md:gap-5">
                        <div>
                            <p
                                class="text-xs font-black tracking-[0.14em] uppercase md:text-sm md:tracking-[0.16em]"
                                :class="courseTheme.accentTextClass"
                            >
                                Student Clearance System
                            </p>

                            <h1
                                class="mt-2 text-3xl font-black tracking-tight md:mt-3 md:text-4xl"
                                :class="courseTheme.headingTextClass"
                            >
                                Welcome, {{ student.name }}!
                            </h1>

                            <p
                                class="mt-2 max-w-2xl text-sm leading-6 md:mt-3 md:text-base md:leading-7"
                                :class="courseTheme.softTextClass"
                            >
                                Track your clearance progress, review office
                                statuses, and submit additional clearance
                                requests when needed.
                            </p>

                            <div
                                class="mt-3 flex flex-wrap items-center gap-2 md:mt-4"
                            >
                                <span
                                    class="inline-flex rounded-full bg-white/85 px-3 py-1 text-xs font-black shadow-sm"
                                    :class="courseTheme.accentTextClass"
                                >
                                    Student ID: {{ student.student_id }}
                                </span>

                                <span
                                    v-if="student.course"
                                    class="inline-flex rounded-full bg-white/85 px-3 py-1 text-xs font-black shadow-sm"
                                    :class="courseTheme.accentTextClass"
                                >
                                    {{ student.course.code }} Course Theme
                                </span>
                            </div>
                        </div>

                        <div
                            class="rounded-2xl border border-white/70 bg-white/90 p-4 shadow-lg shadow-slate-200/70 backdrop-blur md:max-w-2xl md:rounded-[1.5rem] md:p-5"
                        >
                            <div
                                class="mb-2 flex items-center justify-between gap-3 md:mb-3"
                            >
                                <span
                                    class="text-base font-black md:text-lg"
                                    :class="courseTheme.accentTextClass"
                                >
                                    {{ progressPercentage }}% Completed
                                </span>

                                <span
                                    class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600"
                                >
                                    {{ finalClearanceLabel }}
                                </span>
                            </div>

                            <div
                                class="h-2.5 overflow-hidden rounded-full bg-slate-100 md:h-3"
                            >
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="courseTheme.progressBarClass"
                                    :style="{ width: `${progressPercentage}%` }"
                                ></div>
                            </div>

                            <p
                                class="mt-2 text-xs leading-5 text-slate-600 md:mt-3 md:text-sm md:leading-6"
                            >
                                {{ progressMessage }}
                            </p>
                        </div>
                    </div>

                    <div class="hidden items-center justify-center md:flex">
                        <!-- Fully Cleared: show celebration card with print button -->
                        <div
                            v-if="isFullyCleared"
                            class="flex w-full flex-col items-center justify-center gap-6 text-center"
                        >
                            <div
                                class="relative flex h-72 w-72 items-center justify-center"
                            >
                                <div
                                    class="absolute inset-0 animate-pulse rounded-full opacity-70 blur-2xl transition duration-1000"
                                    :class="courseTheme.iconBgClass"
                                ></div>
                                <div
                                    class="relative flex h-60 w-60 flex-col items-center justify-center gap-3 rounded-full bg-white/80 shadow-2xl shadow-slate-300/70 backdrop-blur"
                                >
                                    <!-- Beautiful large green check mark instead of emoji -->
                                    <div
                                        class="flex items-center justify-center rounded-full bg-green-100 p-4 shadow-inner"
                                    >
                                        <CheckCircle2
                                            class="size-16 text-green-600"
                                            stroke-width="2.5"
                                        />
                                    </div>
                                    <span
                                        class="text-xl font-black tracking-tight"
                                        :class="courseTheme.headingTextClass"
                                    >
                                        Fully Cleared!
                                    </span>
                                </div>
                            </div>

                            <div class="w-full px-4">
                                <p
                                    class="mb-3 text-[0.65rem] font-black tracking-[0.16em] uppercase opacity-70"
                                    :class="courseTheme.headingTextClass"
                                >
                                    All approvals verified
                                </p>
                                <button
                                    type="button"
                                    class="group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-green-600 px-6 py-4 text-sm font-black text-white shadow-xl shadow-green-600/20 transition-all hover:-translate-y-1 hover:bg-green-700 hover:shadow-2xl hover:shadow-green-600/40 active:translate-y-0"
                                    @click="openClearanceReceipt"
                                >
                                    <div
                                        class="absolute inset-0 bg-black/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                    ></div>
                                    <span class="relative"
                                        >Print Clearance Receipt</span
                                    >
                                    <Download
                                        class="relative size-4 transition-transform group-hover:translate-y-0.5"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Not cleared: show course code circle + status -->
                        <div v-else class="flex flex-col items-center gap-4">
                            <div
                                class="relative flex h-72 w-72 items-center justify-center"
                            >
                                <div
                                    class="absolute inset-0 rounded-full opacity-70 blur-2xl"
                                    :class="courseTheme.iconBgClass"
                                ></div>

                                <div
                                    class="relative flex h-60 w-60 flex-col items-center justify-center gap-1 rounded-full bg-white/70 shadow-2xl shadow-slate-300/70 backdrop-blur"
                                >
                                    <span
                                        class="text-6xl leading-none font-black tracking-tighter"
                                        :class="courseTheme.headingTextClass"
                                    >
                                        {{ student.course?.code ?? '—' }}
                                    </span>
                                    <span
                                        class="text-[0.65rem] font-black tracking-[0.2em] uppercase opacity-40"
                                        :class="courseTheme.headingTextClass"
                                    >
                                        Course
                                    </span>
                                </div>
                            </div>

                            <div class="text-center">
                                <p
                                    class="text-[0.65rem] font-black tracking-[0.16em] uppercase opacity-50"
                                    :class="courseTheme.accentTextClass"
                                >
                                    Clearance Status
                                </p>
                                <p
                                    class="mt-1 text-lg font-black"
                                    :class="courseTheme.headingTextClass"
                                >
                                    {{ finalClearanceLabel }}
                                </p>
                                <p
                                    class="mt-0.5 text-xs font-bold opacity-50"
                                    :class="courseTheme.accentTextClass"
                                >
                                    {{ progressPercentage }}% complete
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Summary Cards -->
            <section class="grid grid-cols-2 gap-3 md:gap-4 xl:grid-cols-4">
                <div
                    class="rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:rounded-[1.5rem] md:p-6"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-xl shadow-sm md:h-14 md:w-14 md:rounded-2xl md:text-2xl"
                            :class="courseTheme.iconBgClass"
                        >
                            👥
                        </div>
                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide uppercase md:text-sm"
                                :class="courseTheme.accentTextClass"
                            >
                                Total Cleared
                            </p>
                            <p
                                class="mt-1 text-2xl font-black md:text-3xl"
                                :class="courseTheme.headingTextClass"
                            >
                                {{ approvedCount }} / {{ offices.length }}
                            </p>
                            <p
                                class="text-xs font-medium text-slate-500 md:text-sm"
                            >
                                Departments
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:rounded-[1.5rem] md:p-6"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-xl shadow-sm md:h-14 md:w-14 md:rounded-2xl md:text-2xl"
                        >
                            ⏰
                        </div>
                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-orange-600 uppercase md:text-sm"
                            >
                                Pending Requests
                            </p>
                            <p
                                class="mt-1 text-2xl font-black md:text-3xl"
                                :class="courseTheme.headingTextClass"
                            >
                                {{ pendingCount }}
                            </p>
                            <p
                                class="text-xs font-medium text-slate-500 md:text-sm"
                            >
                                Departments
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:rounded-[1.5rem] md:p-6"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-green-100 text-xl shadow-sm md:h-14 md:w-14 md:rounded-2xl md:text-2xl"
                        >
                            ✅
                        </div>
                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-green-700 uppercase md:text-sm"
                            >
                                Approved
                            </p>
                            <p
                                class="mt-1 text-2xl font-black md:text-3xl"
                                :class="courseTheme.headingTextClass"
                            >
                                {{ approvedCount }}
                            </p>
                            <p
                                class="text-xs font-medium text-slate-500 md:text-sm"
                            >
                                Departments
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:rounded-[1.5rem] md:p-6"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-100 text-xl shadow-sm md:h-14 md:w-14 md:rounded-2xl md:text-2xl"
                        >
                            ❌
                        </div>
                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-red-600 uppercase md:text-sm"
                            >
                                Not Cleared
                            </p>
                            <p
                                class="mt-1 text-2xl font-black md:text-3xl"
                                :class="courseTheme.headingTextClass"
                            >
                                {{ notClearedCount }}
                            </p>
                            <p
                                class="text-xs font-medium text-slate-500 md:text-sm"
                            >
                                Departments
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main Content -->
            <section
                class="order-4 grid gap-4 xl:order-3 xl:grid-cols-2 xl:gap-6"
            >
                <!-- Recent Activity -->
                <div
                    class="order-2 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm shadow-slate-200/70 md:rounded-[1.5rem] md:p-6 xl:order-1"
                >
                    <div class="mb-3 flex items-center justify-between md:mb-5">
                        <div>
                            <p
                                class="hidden text-xs font-black tracking-[0.18em] text-slate-400 uppercase md:block"
                            >
                                Latest Updates
                            </p>
                            <h2
                                class="mt-1 text-xl font-black"
                                :class="courseTheme.headingTextClass"
                            >
                                Recent Activity
                            </h2>
                        </div>

                        <button
                            type="button"
                            class="rounded-full px-3 py-1 text-sm font-bold transition hover:bg-slate-100"
                            :class="courseTheme.accentTextClass"
                            @click="openClearanceDetailsModal"
                        >
                            View All
                        </button>
                    </div>

                    <div
                        v-if="!clearanceRequest"
                        class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-center md:p-8"
                    >
                        <p class="font-bold text-slate-700">
                            No recent activity yet.
                        </p>
                        <p class="mt-1 text-sm text-slate-500">
                            Your clearance updates will appear here.
                        </p>
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="office in officeStatuses.slice(0, 5)"
                            :key="office.id"
                            class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2.5 text-sm transition hover:border-slate-200 hover:bg-white hover:shadow-sm md:rounded-2xl md:px-4 md:py-3"
                        >
                            <div>
                                <p
                                    class="font-black"
                                    :class="courseTheme.headingTextClass"
                                >
                                    {{ office.name }}
                                </p>
                                <p
                                    class="mt-1 text-xs font-medium text-slate-500"
                                >
                                    {{ statusLabel(office.status) }}
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-white px-3 py-1 text-xs font-black shadow-sm"
                                :class="statusClass(office.status)"
                            >
                                {{ statusLabel(office.status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- My Clearance Status -->
                <div
                    class="order-1 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm shadow-slate-200/70 md:rounded-[1.5rem] md:p-6 xl:order-2"
                >
                    <div class="mb-3 flex items-center justify-between md:mb-5">
                        <div>
                            <p
                                class="hidden text-xs font-black tracking-[0.18em] text-slate-400 uppercase md:block"
                            >
                                Office Breakdown
                            </p>
                            <h2
                                class="mt-1 text-xl font-black"
                                :class="courseTheme.headingTextClass"
                            >
                                My Clearance Status
                            </h2>
                        </div>

                        <button
                            type="button"
                            class="rounded-full px-3 py-1 text-sm font-bold transition hover:bg-slate-100"
                            :class="courseTheme.accentTextClass"
                            @click="openClearanceDetailsModal"
                        >
                            View All
                        </button>
                    </div>

                    <div
                        class="max-h-[22rem] space-y-2 overflow-y-auto pr-1 md:max-h-96 md:space-y-3 md:pr-2"
                    >
                        <div
                            v-for="office in officeStatuses"
                            :key="office.id"
                            class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2.5 text-sm transition hover:border-slate-200 hover:bg-white hover:shadow-sm md:rounded-2xl md:px-4 md:py-3"
                        >
                            <div>
                                <span
                                    class="font-black"
                                    :class="courseTheme.headingTextClass"
                                >
                                    {{ office.name }}
                                </span>

                                <p
                                    v-if="office.remarks"
                                    class="mt-1 text-xs font-semibold text-red-600"
                                >
                                    {{ office.remarks }}
                                </p>
                            </div>

                            <div class="flex shrink-0 flex-col items-end gap-2">
                                <span
                                    class="rounded-full bg-white px-3 py-1 text-xs font-black shadow-sm"
                                    :class="statusClass(office.status)"
                                >
                                    {{ statusLabel(office.status) }}
                                </span>

                                <button
                                    v-if="office.status === 'rejected'"
                                    type="button"
                                    class="rounded-xl bg-orange-100 px-3 py-1 text-xs font-black text-orange-700 transition hover:bg-orange-200"
                                    @click="
                                        openMarkAsCompliedModal(
                                            office.approvalId,
                                            office.name,
                                        )
                                    "
                                >
                                    Mark as Complied
                                </button>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="mt-3 w-full rounded-xl px-4 py-2.5 text-sm font-black text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg md:mt-4 md:rounded-2xl md:py-3 md:text-base"
                            :class="[
                                courseTheme.primaryButtonClass,
                                courseTheme.primaryButtonHoverClass,
                            ]"
                            @click="openClearanceDetailsModal"
                        >
                            View Clearance Details →
                        </button>

                        <button
                            v-if="isFullyCleared"
                            type="button"
                            class="mt-3 w-full rounded-2xl bg-green-700 px-4 py-3 font-black text-white shadow-md transition hover:-translate-y-0.5 hover:bg-green-800 hover:shadow-lg"
                            @click="openClearanceReceipt"
                        >
                            Print Clearance Receipt →
                        </button>
                    </div>
                </div>
            </section>

            <!-- Submit Request -->
            <section
                class="order-3 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm shadow-slate-200/70 md:flex-row md:items-center md:justify-between md:rounded-[1.5rem] md:p-6 xl:order-4"
            >
                <div class="flex items-center gap-3 md:gap-4">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-xl shadow-sm md:h-14 md:w-14 md:rounded-2xl md:text-2xl"
                        :class="courseTheme.iconBgClass"
                    >
                        📄
                    </div>

                    <div>
                        <p
                            class="hidden text-xs font-black tracking-[0.18em] text-slate-400 uppercase md:block"
                        >
                            Clearance Request
                        </p>

                        <h2
                            class="mt-1 text-lg font-black md:text-xl"
                            :class="courseTheme.headingTextClass"
                        >
                            Submit New Clearance Request
                        </h2>

                        <p
                            class="text-xs font-medium text-slate-500 md:text-sm"
                        >
                            Need to request clearance from all required offices?
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="w-full rounded-2xl px-5 py-3 text-sm font-black text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg disabled:translate-y-0 disabled:shadow-none md:w-auto md:px-6 md:text-base"
                    :class="
                        requestableOffices.length === 0
                            ? 'cursor-not-allowed bg-slate-400'
                            : [
                                  courseTheme.primaryButtonClass,
                                  courseTheme.primaryButtonHoverClass,
                              ]
                    "
                    :disabled="requestableOffices.length === 0"
                    @click="openSubmitRequestModal"
                >
                    {{
                        !clearanceRequest
                            ? 'Submit Clearance Request →'
                            : requestableOffices.length > 0
                              ? 'Request More Offices →'
                              : 'All Offices Requested'
                    }}
                </button>
            </section>

            <!-- Submit Clearance Request Office Selection Modal -->
            <div
                v-if="showSubmitRequestModal"
                class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 px-3 py-4 sm:items-center sm:px-4 sm:py-6"
            >
                <div
                    class="max-h-[88vh] w-full max-w-3xl overflow-y-auto rounded-t-2xl bg-white shadow-xl sm:max-h-[90vh] sm:rounded-2xl"
                >
                    <div
                        class="flex items-start justify-between gap-3 border-b border-slate-200 px-4 py-4 sm:px-6"
                    >
                        <div>
                            <h2
                                class="text-xl font-bold"
                                :class="courseTheme.headingTextClass"
                            >
                                Select Offices to Request
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Choose the offices you want to request clearance
                                from. Only offices that are still Not Requested
                                will appear here.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg px-3 py-1 text-lg font-bold text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                            @click="closeSubmitRequestModal"
                        >
                            ×
                        </button>
                    </div>

                    <div class="space-y-4 px-4 py-5 sm:px-6">
                        <div
                            class="rounded-xl border p-4 text-sm"
                            :class="courseTheme.statusBoxClass"
                        >
                            Select at least one office. Selected offices will
                            become
                            <span class="font-semibold">Pending</span>.
                            Unselected offices will stay as
                            <span class="font-semibold">Not Requested</span>.
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <button
                                v-for="office in requestableOffices"
                                :key="office.id"
                                type="button"
                                :disabled="!isOfficeRequestable(office)"
                                class="relative min-h-16 overflow-hidden rounded-xl border p-4 text-left transition"
                                :class="
                                    !isOfficeRequestable(office)
                                        ? 'cursor-not-allowed border-slate-100 bg-slate-50 text-slate-400 opacity-75'
                                        : selectedOfficeIds.includes(office.id)
                                          ? courseTheme.selectedOfficeClass
                                          : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                "
                                @click="toggleOfficeSelection(office.id)"
                            >
                                <div
                                    class="relative z-10 flex items-start gap-3"
                                >
                                    <div
                                        class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded border"
                                        :class="
                                            !isOfficeRequestable(office)
                                                ? 'border-slate-200 bg-slate-100'
                                                : selectedOfficeIds.includes(
                                                        office.id,
                                                    )
                                                  ? courseTheme.selectedCheckClass
                                                  : 'border-slate-300 bg-white'
                                        "
                                    >
                                        <span
                                            v-if="
                                                selectedOfficeIds.includes(
                                                    office.id,
                                                )
                                            "
                                            class="text-xs font-bold"
                                        >
                                            ✓
                                        </span>
                                    </div>

                                    <div>
                                        <p
                                            class="font-semibold"
                                            :class="{
                                                'text-slate-500':
                                                    !isOfficeRequestable(
                                                        office,
                                                    ),
                                            }"
                                        >
                                            {{ office.name }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ office.group }}
                                        </p>

                                        <p
                                            v-if="!isOfficeRequestable(office)"
                                            class="mt-2 text-xs font-bold tracking-wide text-orange-600/80 uppercase"
                                        >
                                            Requires:
                                            {{
                                                unmetPrerequisites(office).join(
                                                    ', ',
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div
                        class="flex flex-col gap-3 border-t border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                    >
                        <p class="text-sm text-slate-500">
                            Selected offices:
                            <span
                                class="font-semibold"
                                :class="courseTheme.accentTextClass"
                            >
                                {{ selectedOfficeIds.length }}
                            </span>
                        </p>

                        <div
                            class="grid grid-cols-2 gap-3 sm:flex sm:justify-end"
                        >
                            <button
                                type="button"
                                class="min-h-11 rounded-xl border border-slate-300 px-4 py-3 font-semibold text-slate-700 transition hover:bg-slate-100"
                                @click="closeSubmitRequestModal"
                            >
                                Cancel
                            </button>

                            <button
                                type="button"
                                class="min-h-11 rounded-xl px-4 py-3 font-semibold text-white transition"
                                :class="
                                    selectedOfficeIds.length === 0
                                        ? 'cursor-not-allowed bg-slate-400'
                                        : [
                                              courseTheme.primaryButtonClass,
                                              courseTheme.primaryButtonHoverClass,
                                          ]
                                "
                                :disabled="selectedOfficeIds.length === 0"
                                @click="submitClearanceRequest"
                            >
                                Submit Selected Offices
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clearance Details Modal -->
            <div
                v-if="showClearanceDetailsModal"
                class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 px-3 py-4 sm:items-center sm:px-4 sm:py-6"
            >
                <div
                    class="max-h-[88vh] w-full max-w-5xl overflow-y-auto rounded-t-2xl bg-white shadow-xl sm:max-h-[90vh] sm:rounded-2xl"
                >
                    <div
                        class="flex items-start justify-between gap-3 border-b border-slate-200 px-4 py-4 sm:px-6"
                    >
                        <div>
                            <h2
                                class="text-xl font-bold"
                                :class="courseTheme.headingTextClass"
                            >
                                Clearance Details
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                View your complete clearance request status and
                                office approval breakdown.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg px-3 py-1 text-lg font-bold text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                            @click="closeClearanceDetailsModal"
                        >
                            ×
                        </button>
                    </div>

                    <div class="space-y-6 px-4 py-5 sm:px-6">
                        <!-- Student Information -->
                        <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-3"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Student Name
                                </p>
                                <p
                                    class="mt-1 font-bold"
                                    :class="courseTheme.headingTextClass"
                                >
                                    {{ student.name }}
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-3"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Student ID
                                </p>
                                <p
                                    class="mt-1 font-bold"
                                    :class="courseTheme.headingTextClass"
                                >
                                    {{ student.student_id }}
                                </p>
                            </div>

                            <div
                                class="col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-1"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Course
                                </p>
                                <p
                                    class="mt-1 font-bold"
                                    :class="courseTheme.headingTextClass"
                                >
                                    {{ student.course?.code ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <!-- Request Information -->
                        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                            <div class="rounded-xl border p-3">
                                <p
                                    class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Semester
                                </p>
                                <p
                                    class="mt-1 font-bold"
                                    :class="courseTheme.headingTextClass"
                                >
                                    {{
                                        clearanceRequest?.semester ??
                                        'No request yet'
                                    }}
                                </p>
                            </div>

                            <div class="rounded-xl border p-3">
                                <p
                                    class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    School Year
                                </p>
                                <p
                                    class="mt-1 font-bold"
                                    :class="courseTheme.headingTextClass"
                                >
                                    {{
                                        clearanceRequest?.school_year ??
                                        'No request yet'
                                    }}
                                </p>
                            </div>

                            <div class="rounded-xl border p-3">
                                <p
                                    class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Progress
                                </p>
                                <p
                                    class="mt-1 font-bold"
                                    :class="courseTheme.headingTextClass"
                                >
                                    {{ approvedCount }} / {{ offices.length }}
                                </p>
                            </div>

                            <div class="rounded-xl border p-3">
                                <p
                                    class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Final Status
                                </p>
                                <span
                                    class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-bold"
                                    :class="
                                        isFullyCleared
                                            ? 'bg-green-100 text-green-700'
                                            : courseTheme.statusBoxClass
                                    "
                                >
                                    {{ finalClearanceLabel }}
                                </span>
                            </div>
                        </div>

                        <!-- Office Breakdown -->
                        <div>
                            <h3
                                class="mb-3 text-lg font-bold"
                                :class="courseTheme.headingTextClass"
                            >
                                Office Approval Breakdown
                            </h3>

                            <div
                                class="grid max-h-[60vh] gap-3 overflow-y-auto px-1 pb-4"
                            >
                                <div
                                    v-for="office in officeStatuses"
                                    :key="office.id"
                                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-300"
                                >
                                    <div
                                        class="flex items-start justify-between gap-4"
                                    >
                                        <div class="flex-1">
                                            <h4
                                                class="text-sm leading-tight font-bold"
                                                :class="
                                                    courseTheme.headingTextClass
                                                "
                                            >
                                                {{ office.name }}
                                            </h4>

                                            <div class="mt-1.5 text-xs">
                                                <span
                                                    v-if="office.remarks"
                                                    class="font-medium text-red-600"
                                                >
                                                    {{ office.remarks }}
                                                </span>
                                                <span
                                                    v-else
                                                    class="text-slate-400"
                                                >
                                                    No remarks
                                                </span>
                                            </div>

                                            <div
                                                v-if="
                                                    office.status === 'rejected'
                                                "
                                                class="mt-3"
                                            >
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center rounded-lg bg-orange-100 px-3 py-1.5 text-[0.7rem] font-black tracking-wider text-orange-700 uppercase shadow-sm transition hover:bg-orange-200"
                                                    @click="
                                                        openMarkAsCompliedModal(
                                                            office.approvalId,
                                                            office.name,
                                                        )
                                                    "
                                                >
                                                    Mark as Complied
                                                </button>
                                            </div>
                                        </div>

                                        <div class="shrink-0 text-right">
                                            <span
                                                class="inline-flex rounded-full px-2.5 py-1 text-[0.65rem] font-bold tracking-wider uppercase"
                                                :class="
                                                    statusClass(office.status)
                                                "
                                            >
                                                {{ statusLabel(office.status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex justify-end border-t border-slate-200 px-4 py-4 sm:px-6"
                    >
                        <button
                            type="button"
                            class="min-h-11 w-full rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white transition hover:bg-slate-800 sm:w-auto"
                            @click="closeClearanceDetailsModal"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mark as Complied Confirmation Modal -->
            <div
                v-if="selectedCompliedApproval"
                class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 px-3 py-4 sm:items-center sm:px-4 sm:py-6"
            >
                <div
                    class="w-full max-w-md rounded-t-2xl bg-white shadow-xl sm:rounded-2xl"
                >
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2
                            class="text-xl font-bold"
                            :class="courseTheme.headingTextClass"
                        >
                            Mark as Complied?
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Confirm that you have already complied with the
                            requirement for this office.
                        </p>
                    </div>

                    <div class="space-y-4 px-6 py-5">
                        <div
                            class="rounded-xl border border-orange-200 bg-orange-50 p-4"
                        >
                            <p class="text-sm font-semibold text-orange-700">
                                Office
                            </p>

                            <p class="mt-1 font-bold text-orange-900">
                                {{ selectedCompliedApproval.officeName }}
                            </p>
                        </div>

                        <p class="text-sm text-slate-600">
                            Once confirmed, this office status will return to
                            <span class="font-semibold text-orange-600"
                                >Pending</span
                            >
                            so the assigned staff can review your clearance
                            again.
                        </p>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-3 border-t border-slate-200 px-6 py-4 sm:flex sm:justify-end"
                    >
                        <button
                            type="button"
                            class="min-h-11 rounded-xl border border-slate-300 px-4 py-3 font-semibold text-slate-700 transition hover:bg-slate-100"
                            @click="closeMarkAsCompliedModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            class="min-h-11 rounded-xl bg-orange-600 px-4 py-3 font-semibold text-white transition hover:bg-orange-700"
                            @click="confirmMarkAsComplied"
                        >
                            Confirm
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile More / Profile Sheet -->
            <div
                v-if="showMobileMoreMenu"
                class="fixed inset-x-3 bottom-24 z-50 rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl shadow-slate-900/20 md:hidden"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-sm font-black shadow-sm"
                            :class="courseTheme.iconBgClass"
                        >
                            {{ studentInitials }}
                        </div>

                        <div class="min-w-0">
                            <p
                                class="truncate text-sm font-black"
                                :class="courseTheme.headingTextClass"
                            >
                                {{ student.name }}
                            </p>

                            <p class="text-xs font-semibold text-slate-500">
                                Student ID: {{ student.student_id }}
                            </p>

                            <p
                                class="mt-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[0.65rem] font-black"
                                :class="courseTheme.accentTextClass"
                            >
                                {{ courseCode }} Course Theme
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-lg font-black text-slate-500 transition hover:bg-slate-100"
                        @click="closeMobileMoreMenu"
                    >
                        ×
                    </button>
                </div>

                <div class="mt-4 grid gap-2">
                    <button
                        type="button"
                        class="flex min-h-12 items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-black text-slate-700 transition hover:bg-slate-100 active:bg-slate-200"
                        @click="openClearanceDetailsModal"
                    >
                        <span>View Clearance Details</span>
                        <ChevronRight class="size-4 opacity-50" />
                    </button>

                    <button
                        v-if="isFullyCleared"
                        type="button"
                        class="flex min-h-12 items-center justify-between rounded-xl bg-green-600 px-4 py-3 text-left text-sm font-black text-white shadow-md shadow-green-600/20 transition hover:bg-green-700 active:translate-y-0.5"
                        @click="openClearanceReceipt"
                    >
                        <span>Print Clearance Receipt</span>
                        <Download class="size-4 opacity-90" />
                    </button>

                    <button
                        type="button"
                        class="flex min-h-12 items-center justify-between rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-left text-sm font-black text-red-700 transition hover:bg-red-100 active:bg-red-200"
                        @click="openMobileLogoutModal"
                    >
                        <span>Logout</span>
                        <LogOut class="size-4 opacity-70" />
                    </button>
                </div>
            </div>

            <!-- Mobile Logout Confirmation Modal -->
            <div
                v-if="showMobileLogoutModal"
                class="fixed inset-0 z-[60] flex items-end justify-center bg-black/50 px-3 py-4 sm:items-center"
            >
                <div
                    class="w-full max-w-md rounded-t-2xl bg-white shadow-xl sm:rounded-2xl"
                >
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2
                            class="text-xl font-bold"
                            :class="courseTheme.headingTextClass"
                        >
                            Logout?
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Are you sure you want to log out of your student
                            account?
                        </p>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-3 border-t border-slate-200 px-6 py-4"
                    >
                        <button
                            type="button"
                            class="min-h-11 rounded-xl border border-slate-300 px-4 py-3 font-semibold text-slate-700 transition hover:bg-slate-100"
                            @click="closeMobileLogoutModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            class="min-h-11 rounded-xl bg-red-600 px-4 py-3 font-semibold text-white transition hover:bg-red-700"
                            @click="confirmMobileLogout"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Thumb Navigation -->
            <nav
                class="fixed inset-x-3 bottom-3 z-40 rounded-2xl border p-2 shadow-2xl backdrop-blur md:hidden"
                :class="courseTheme.mobileNavClass"
            >
                <div class="grid grid-cols-4 gap-1">
                    <button
                        type="button"
                        class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-[0.65rem] font-black text-white transition hover:bg-white/10"
                        :class="
                            activeMobileNav === 'home'
                                ? courseTheme.mobileNavActiveClass
                                : ''
                        "
                        @click="scrollToDashboardTop"
                    >
                        <span class="text-base">⌂</span>
                        <span>Home</span>
                    </button>

                    <button
                        type="button"
                        class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-[0.65rem] font-black text-white transition hover:bg-white/10 disabled:cursor-not-allowed disabled:opacity-45"
                        :class="
                            activeMobileNav === 'request'
                                ? courseTheme.mobileNavActiveClass
                                : ''
                        "
                        :disabled="requestableOffices.length === 0"
                        @click="openMobileRequests"
                    >
                        <span class="text-base">📄</span>
                        <span>Request</span>
                    </button>

                    <button
                        type="button"
                        class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-[0.65rem] font-black text-white transition hover:bg-white/10"
                        :class="
                            activeMobileNav === 'offices'
                                ? courseTheme.mobileNavActiveClass
                                : ''
                        "
                        @click="openMobileOffices"
                    >
                        <span class="text-base">🏢</span>
                        <span>Offices</span>
                    </button>

                    <button
                        type="button"
                        class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-[0.65rem] font-black text-white transition hover:bg-white/10"
                        :class="
                            activeMobileNav === 'more' || showMobileMoreMenu
                                ? courseTheme.mobileNavActiveClass
                                : ''
                        "
                        @click="toggleMobileMoreMenu"
                    >
                        <span class="text-base">•••</span>
                        <span>More</span>
                    </button>
                </div>
            </nav>
        </div>
    </div>
</template>
