<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

import { AlertTriangle, CheckCircle2, Download } from 'lucide-vue-next';
import { computed, ref, onMounted, onUnmounted } from 'vue';

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

let pollingInterval: ReturnType<typeof setInterval>;

const handleOpenClearanceStatus = () => {
    openClearanceDetailsModal();
};

const handleOpenSubmitRequest = () => {
    openSubmitRequestModal();
};

onMounted(() => {
    window.addEventListener('open-clearance-status', handleOpenClearanceStatus);
    window.addEventListener('open-submit-request', handleOpenSubmitRequest);

    const params = new URLSearchParams(window.location.search);

    if (params.get('view') === 'status') {
        openClearanceDetailsModal();
    } else if (params.get('view') === 'request') {
        openSubmitRequestModal();
    }

    pollingInterval = setInterval(() => {
        router.reload({
            data: { _t: Date.now() },
            only: [
                'clearanceRequest',
                'offices',
                'notifications',
                'studentClearance',
            ],
        });
    }, 5000);
});

onUnmounted(() => {
    window.removeEventListener(
        'open-clearance-status',
        handleOpenClearanceStatus,
    );
    window.removeEventListener('open-submit-request', handleOpenSubmitRequest);
    clearInterval(pollingInterval);
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
    year_level?: string | null;
    first_name?: string | null;
    last_name?: string | null;
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
        return 'Fully Approved';
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

const calculateOfficePrerequisiteDepths = <T extends Office>(
    offices: T[],
): Map<number, number> => {
    const map = new Map<number, Office>();
    props.offices.forEach((o) => map.set(o.id, o));
    offices.forEach((o) => map.set(o.id, o));

    const depths = new Map<number, number>();
    const visiting = new Set<number>();

    const getDepth = (id: number): number => {
        if (depths.has(id)) {
            return depths.get(id)!;
        }

        if (visiting.has(id)) {
            return 0;
        }

        const office = map.get(id);

        if (!office) {
            return 0;
        }

        if (office.is_final_approver) {
            depths.set(id, 99999);

            return 99999;
        }

        if (!office.prerequisites || office.prerequisites.length === 0) {
            depths.set(id, 0);

            return 0;
        }

        visiting.add(id);
        let maxPrereqDepth = 0;

        for (const prereq of office.prerequisites) {
            const d = getDepth(prereq.id);

            if (d > maxPrereqDepth) {
                maxPrereqDepth = d;
            }
        }

        visiting.delete(id);

        const depth = maxPrereqDepth + 1;
        depths.set(id, depth);

        return depth;
    };

    offices.forEach((o) => getDepth(o.id));

    return depths;
};

const sortOfficesByPrerequisites = <T extends Office>(offices: T[]): T[] => {
    const depths = calculateOfficePrerequisiteDepths(offices);

    return [...offices].sort((a, b) => {
        const depthA = depths.get(a.id) ?? 0;
        const depthB = depths.get(b.id) ?? 0;

        if (depthA !== depthB) {
            return depthA - depthB;
        }

        if (a.sort_order !== b.sort_order) {
            return a.sort_order - b.sort_order;
        }

        return a.name.localeCompare(b.name);
    });
};

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

    return sortOfficesByPrerequisites(statuses);
});

const rejectedOffices = computed(() => {
    return officeStatuses.value.filter((o) => o.status === 'rejected');
});

const hasRejectedOffices = computed(() => rejectedOffices.value.length > 0);

const progressMessage = computed(() => {
    if (!props.clearanceRequest) {
        return 'Submit your first clearance request to start your clearance progress.';
    }

    if (isFullyCleared.value) {
        return 'Your clearance is fully approved by the College President.';
    }

    if (progressPercentage.value === 100) {
        return 'All offices have approved your clearance request. Waiting for final approval.';
    }

    return 'Keep going! Your clearance request is being processed.';
});

const statusLabel = (status: string) => {
    if (status === 'approved') {
        return 'Approved';
    }

    if (status === 'pending') {
        return 'Pending';
    }

    if (status === 'rejected') {
        return 'Not Approved';
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

const openClearanceDetailsModal = () => {
    showClearanceDetailsModal.value = true;
};

const closeClearanceDetailsModal = () => {
    showClearanceDetailsModal.value = false;
};

const openClearanceReceipt = () => {
    if (!props.clearanceRequest || !isFullyCleared.value) {
        return;
    }

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
        const allRegularApproved = regularOffices.value.every((ro) => {
            const approval = props.clearanceRequest!.approvals.find(
                (a) => a.office_id === ro.id,
            );

            return approval?.status === 'approved';
        });

        offices = officeStatuses.value.filter((office) => {
            if (office.is_final_approver) {
                return (
                    office.status === 'not_requested' &&
                    allRegularApproved &&
                    regularOffices.value.length > 0
                );
            }

            return office.status === 'not_requested';
        });
    }

    return sortOfficesByPrerequisites(offices);
});

const isOfficeRequestable = (office: Office) => {
    if (office.is_final_approver) {
        if (!props.clearanceRequest) {
            return false;
        }

        const allRegularApproved = regularOffices.value.every((ro) => {
            const approval = props.clearanceRequest!.approvals.find(
                (a) => a.office_id === ro.id,
            );

            return approval?.status === 'approved';
        });

        return allRegularApproved && regularOffices.value.length > 0;
    }

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
    if (office.is_final_approver) {
        if (!props.clearanceRequest) {
            return regularOffices.value.map((o) => o.name);
        }

        const unapprovedRegularOffices = regularOffices.value.filter((ro) => {
            const approval = props.clearanceRequest!.approvals.find(
                (a) => a.office_id === ro.id,
            );

            return approval?.status !== 'approved';
        });

        return unapprovedRegularOffices.map((o) => o.name);
    }

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
    showSubmitRequestModal.value = true;
};

const closeSubmitRequestModal = () => {
    selectedOfficeIds.value = [];
    showSubmitRequestModal.value = false;
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

                                <span
                                    v-if="student.year_level"
                                    class="inline-flex rounded-full bg-white/85 px-3 py-1 text-xs font-black shadow-sm"
                                    :class="courseTheme.accentTextClass"
                                >
                                    {{ student.year_level }}
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
                                        Fully Approved!
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
                                Total Approved
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
                                Not Approved
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

            <!-- Submit / Comply Request -->
            <section
                class="order-3 flex flex-col gap-4 rounded-2xl border p-4 shadow-sm transition-all md:rounded-[1.5rem] md:p-6 xl:order-4"
                :class="
                    hasRejectedOffices
                        ? 'border-amber-300 bg-gradient-to-br from-amber-50/80 via-white to-orange-50/40 shadow-amber-200/50'
                        : 'border-slate-200 bg-white/95 shadow-slate-200/70'
                "
            >
                <div
                    class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-xl shadow-sm md:h-14 md:w-14 md:rounded-2xl md:text-2xl"
                            :class="
                                hasRejectedOffices
                                    ? 'border border-amber-300 bg-amber-100 text-amber-800'
                                    : courseTheme.iconBgClass
                            "
                        >
                            {{ hasRejectedOffices ? '⚠️' : '📄' }}
                        </div>

                        <div>
                            <div class="flex items-center gap-2">
                                <p
                                    class="hidden text-xs font-black tracking-[0.18em] uppercase md:block"
                                    :class="
                                        hasRejectedOffices
                                            ? 'text-amber-800'
                                            : 'text-slate-400'
                                    "
                                >
                                    Clearance Request
                                </p>

                                <span
                                    v-if="hasRejectedOffices"
                                    class="inline-flex items-center gap-1 rounded-full bg-amber-200/90 px-2 py-0.5 text-[0.65rem] font-black tracking-wider text-amber-900 uppercase"
                                >
                                    <AlertTriangle class="size-3" />
                                    Action Required
                                </span>
                            </div>

                            <h2
                                class="mt-1 text-lg font-black md:text-xl"
                                :class="
                                    hasRejectedOffices
                                        ? 'text-amber-950'
                                        : courseTheme.headingTextClass
                                "
                            >
                                {{
                                    hasRejectedOffices
                                        ? 'Comply Rejected Requirements'
                                        : 'Submit New Clearance Request'
                                }}
                            </h2>

                            <p
                                class="text-xs font-medium md:text-sm"
                                :class="
                                    hasRejectedOffices
                                        ? 'text-amber-800/90'
                                        : 'text-slate-500'
                                "
                            >
                                {{
                                    hasRejectedOffices
                                        ? 'One or more offices rejected your clearance. Review the remarks below and mark as complied to resubmit.'
                                        : 'Need to request clearance from all required offices?'
                                }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            v-if="requestableOffices.length > 0"
                            type="button"
                            class="w-full cursor-pointer rounded-2xl px-5 py-3 text-sm font-black text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg md:w-auto md:px-6 md:text-base"
                            :class="[
                                courseTheme.primaryButtonClass,
                                courseTheme.primaryButtonHoverClass,
                            ]"
                            @click="openSubmitRequestModal"
                        >
                            {{
                                !clearanceRequest
                                    ? 'Submit Clearance Request →'
                                    : 'Request More Offices →'
                            }}
                        </button>

                        <button
                            v-else-if="hasRejectedOffices"
                            type="button"
                            class="w-full cursor-pointer rounded-2xl bg-amber-600 px-5 py-3 text-sm font-black text-white shadow-md shadow-amber-600/30 transition hover:-translate-y-0.5 hover:bg-amber-700 hover:shadow-lg md:w-auto md:px-6 md:text-base"
                            @click="
                                rejectedOffices.length === 1
                                    ? openMarkAsCompliedModal(
                                          rejectedOffices[0].approvalId,
                                          rejectedOffices[0].name,
                                      )
                                    : openSubmitRequestModal()
                            "
                        >
                            {{
                                rejectedOffices.length === 1
                                    ? 'Mark as Complied →'
                                    : `Review Rejected Items (${rejectedOffices.length}) →`
                            }}
                        </button>

                        <button
                            v-else
                            type="button"
                            disabled
                            class="w-full cursor-not-allowed rounded-2xl bg-slate-400 px-5 py-3 text-sm font-black text-white shadow-none md:w-auto md:px-6 md:text-base"
                        >
                            All Offices Requested
                        </button>
                    </div>
                </div>

                <!-- Rejected requirements list inside Clearance Request section -->
                <div
                    v-if="hasRejectedOffices"
                    class="mt-2 space-y-3 border-t border-amber-200/80 pt-3"
                >
                    <div
                        class="flex items-center justify-between text-xs font-bold tracking-wider text-amber-950"
                    >
                        <span class="uppercase">
                            Requirements Awaiting Compliance
                        </span>
                        <span
                            class="rounded-full bg-amber-200 px-2 py-0.5 text-[0.7rem] font-black text-amber-900"
                        >
                            {{ rejectedOffices.length }} item{{
                                rejectedOffices.length > 1 ? 's' : ''
                            }}
                        </span>
                    </div>

                    <div
                        v-for="office in rejectedOffices"
                        :key="'request-sec-' + office.id"
                        class="flex flex-col gap-3 rounded-2xl border border-amber-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="text-sm font-black text-slate-900 md:text-base"
                                >
                                    {{ office.name }}
                                </span>

                                <span
                                    v-if="office.is_final_approver"
                                    class="rounded-full bg-purple-100 px-2.5 py-0.5 text-[0.65rem] font-black text-purple-700 uppercase"
                                >
                                    College President
                                </span>

                                <span
                                    class="rounded-full bg-red-100 px-2.5 py-0.5 text-[0.65rem] font-black text-red-700 uppercase"
                                >
                                    Rejected
                                </span>
                            </div>

                            <div
                                v-if="office.remarks"
                                class="mt-2 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50/80 px-3 py-2 text-xs text-red-700"
                            >
                                <span class="shrink-0 font-black">
                                    Office Remarks:
                                </span>
                                <span class="font-medium break-words">
                                    {{ office.remarks }}
                                </span>
                            </div>

                            <p
                                v-else
                                class="mt-1 text-xs text-slate-400 italic"
                            >
                                No remarks provided.
                            </p>
                        </div>

                        <div class="shrink-0">
                            <button
                                type="button"
                                class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-orange-600 px-4 py-2.5 text-xs font-black text-white shadow-sm shadow-orange-600/30 transition hover:-translate-y-0.5 hover:bg-orange-700 hover:shadow-md sm:w-auto"
                                @click="
                                    openMarkAsCompliedModal(
                                        office.approvalId,
                                        office.name,
                                    )
                                "
                            >
                                <CheckCircle2 class="size-4" />
                                Mark as Complied
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Submit Clearance Request Office Selection Modal -->
            <div
                v-if="showSubmitRequestModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 sm:p-4"
            >
                <div
                    class="flex max-h-[85dvh] w-full max-w-3xl flex-col rounded-2xl bg-white shadow-xl sm:max-h-[90dvh]"
                >
                    <div
                        class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-200 px-4 py-3 sm:px-6 sm:py-4"
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

                    <div
                        class="flex-1 space-y-3 overflow-y-auto px-4 py-4 sm:px-6"
                    >
                        <!-- Rejected Offices Awaiting Compliance in Modal -->
                        <div
                            v-if="hasRejectedOffices"
                            class="space-y-2.5 rounded-xl border border-amber-200 bg-amber-50/90 p-3.5 sm:p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <AlertTriangle
                                        class="size-4 text-amber-600"
                                    />
                                    <h3
                                        class="text-xs font-black text-amber-950 sm:text-sm"
                                    >
                                        Rejected Requirements (Action Needed)
                                    </h3>
                                </div>
                                <span
                                    class="rounded-full bg-amber-200 px-2 py-0.5 text-[0.65rem] font-black text-amber-900"
                                >
                                    {{ rejectedOffices.length }} requirement{{
                                        rejectedOffices.length > 1 ? 's' : ''
                                    }}
                                </span>
                            </div>

                            <p class="text-xs leading-snug text-amber-800">
                                The offices below rejected your clearance
                                request. Once you have addressed their remarks,
                                click
                                <span class="font-bold">Mark as Complied</span>
                                to resubmit your clearance.
                            </p>

                            <div class="space-y-2 pt-1">
                                <div
                                    v-for="office in rejectedOffices"
                                    :key="'modal-rej-' + office.id"
                                    class="flex flex-col gap-2 rounded-xl border border-amber-200 bg-white p-3 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p
                                                class="text-xs font-black text-slate-900 sm:text-sm"
                                            >
                                                {{ office.name }}
                                            </p>
                                            <span
                                                v-if="office.is_final_approver"
                                                class="rounded-full bg-purple-100 px-2 py-0.5 text-[0.6rem] font-black text-purple-700 uppercase"
                                            >
                                                College President
                                            </span>
                                        </div>
                                        <div
                                            v-if="office.remarks"
                                            class="mt-1 rounded-lg bg-red-50 px-2.5 py-1.5 text-[0.7rem] text-red-700"
                                        >
                                            <span class="font-bold"
                                                >Remarks:
                                            </span>
                                            <span>{{ office.remarks }}</span>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-black text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-orange-700"
                                        @click="
                                            closeSubmitRequestModal();
                                            openMarkAsCompliedModal(
                                                office.approvalId,
                                                office.name,
                                            );
                                        "
                                    >
                                        <CheckCircle2 class="size-3.5" />
                                        Mark as Complied
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="requestableOffices.length > 0"
                            class="rounded-xl border p-3 text-sm leading-snug"
                            :class="courseTheme.statusBoxClass"
                        >
                            Select at least one office. Selected offices will
                            become
                            <span class="font-semibold">Pending</span>.
                            Unselected offices will stay as
                            <span class="font-semibold">Not Requested</span>.
                        </div>

                        <div
                            v-else-if="!hasRejectedOffices"
                            class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-center text-sm font-medium text-slate-500"
                        >
                            All required offices have already been requested.
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <button
                                v-for="office in requestableOffices"
                                :key="office.id"
                                type="button"
                                :disabled="!isOfficeRequestable(office)"
                                class="relative min-h-12 overflow-hidden rounded-xl border p-3 text-left transition"
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

                                        <div
                                            v-if="!isOfficeRequestable(office)"
                                            class="mt-2.5"
                                        >
                                            <p
                                                class="text-[0.65rem] font-bold tracking-[0.05em] text-orange-600/90 uppercase"
                                            >
                                                Requires:
                                            </p>
                                            <ul
                                                class="mt-1 flex flex-col gap-0.5 pl-0.5"
                                            >
                                                <li
                                                    v-for="prereq in unmetPrerequisites(
                                                        office,
                                                    )"
                                                    :key="prereq"
                                                    class="flex text-xs font-medium text-orange-600/80"
                                                >
                                                    <span
                                                        class="mr-1.5 opacity-60"
                                                        >-</span
                                                    >
                                                    <span>{{ prereq }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div
                        class="flex shrink-0 flex-col gap-3 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6"
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
                                class="min-h-10 rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                                @click="closeSubmitRequestModal"
                            >
                                Cancel
                            </button>

                            <button
                                type="button"
                                class="min-h-10 rounded-lg px-3 py-2 text-sm font-semibold text-white transition"
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
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 sm:p-4"
            >
                <div
                    class="flex max-h-[85dvh] w-full max-w-5xl flex-col rounded-2xl bg-white shadow-xl sm:max-h-[90dvh]"
                >
                    <div
                        class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-200 px-4 py-3 sm:px-6 sm:py-4"
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

                    <div
                        class="flex-1 space-y-5 overflow-y-auto px-4 py-4 sm:px-6"
                    >
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

                            <div class="grid gap-2 px-1 pb-4">
                                <div
                                    v-for="office in officeStatuses"
                                    :key="office.id"
                                    class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-slate-300"
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
                                                class="mt-2"
                                            >
                                                <p
                                                    class="text-[0.7rem] font-bold text-amber-700"
                                                >
                                                    Comply in Clearance Request
                                                    section
                                                </p>
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
                        class="flex shrink-0 justify-end border-t border-slate-200 px-4 py-3 sm:px-6"
                    >
                        <button
                            type="button"
                            class="min-h-10 w-full rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 sm:w-auto"
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
        </div>
    </div>
</template>
