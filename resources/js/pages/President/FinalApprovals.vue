<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Building2,
    CheckCircle2,
    Clock3,
    Filter,
    GraduationCap,
    Inbox,
    Search,
    ShieldCheck,
    Sparkles,
    Users,
    X,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref, onMounted, onUnmounted } from 'vue';

let pollingInterval: ReturnType<typeof setInterval>;

onMounted(() => {
    pollingInterval = setInterval(() => {
        router.reload({
            data: { _t: Date.now() },
            only: ['clearanceRequests'],
        });
    }, 5000);
});

onUnmounted(() => {
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
    first_name?: string | null;
    last_name?: string | null;
    student_id: string;
    year_level?: string | null;
    course: Course | null;
};

type Office = {
    id: number;
    name: string;
    group: string;
    is_final_approver: boolean;
};

type Approval = {
    id: number;
    status: 'pending' | 'approved' | 'rejected';
    remarks: string | null;
    acted_at?: string | null;
    approved_by?: number | null;
    office: Office;
};

type ClearanceRequest = {
    id: number;
    semester: string;
    school_year: string;
    status?: string;
    receipt_number?: string | null;
    cleared_at?: string | null;
    user: Student;
    approvals: Approval[];
};

type FilterStatus = 'all' | 'pending' | 'approved' | 'rejected';

const props = defineProps<{
    clearanceRequests?: ClearanceRequest[];
    requests?: ClearanceRequest[];
    readyCount?: number;
    courses?: Course[];
}>();

const approvalRequests = computed(() => {
    return props.clearanceRequests ?? props.requests ?? [];
});

const getPresidentApproval = (
    request: ClearanceRequest,
): Approval | undefined => {
    return request.approvals?.find((a) => a.office?.is_final_approver);
};

const getRequestStatus = (
    request: ClearanceRequest,
): 'pending' | 'approved' | 'rejected' => {
    const presApproval = getPresidentApproval(request);

    if (presApproval) {
        return presApproval.status;
    }

    if (request.status === 'cleared') {
        return 'approved';
    }

    return 'pending';
};

const activeFilter = ref<FilterStatus>('pending');
const setFilter = (filter: FilterStatus) => {
    activeFilter.value = filter;
};

const pendingRequests = computed(() => {
    return approvalRequests.value.filter(
        (req) => getRequestStatus(req) === 'pending',
    );
});

const approvedRequests = computed(() => {
    return approvalRequests.value.filter(
        (req) => getRequestStatus(req) === 'approved',
    );
});

const rejectedRequests = computed(() => {
    return approvalRequests.value.filter(
        (req) => getRequestStatus(req) === 'rejected',
    );
});

const readyApprovalCount = computed(() => pendingRequests.value.length);

const filterButtonClass = (filter: FilterStatus) => {
    if (activeFilter.value === filter) {
        if (filter === 'pending') {
            return 'bg-orange-500 text-white border-orange-500 shadow-md shadow-orange-500/20';
        }

        if (filter === 'approved') {
            return 'bg-green-700 text-white border-green-700 shadow-md shadow-green-700/20';
        }

        if (filter === 'rejected') {
            return 'bg-red-600 text-white border-red-600 shadow-md shadow-red-600/20';
        }

        return 'bg-blue-950 text-white border-blue-950 shadow-md shadow-blue-950/20';
    }

    return 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 shadow-xs';
};

const statusBadgeClass = (status: 'pending' | 'approved' | 'rejected') => {
    if (status === 'approved') {
        return 'bg-green-50 text-green-700 border-green-200';
    }

    if (status === 'rejected') {
        return 'bg-red-50 text-red-700 border-red-200';
    }

    return 'bg-blue-50 text-blue-700 border-blue-200';
};

const statusLabel = (status: 'pending' | 'approved' | 'rejected') => {
    if (status === 'approved') {
        return 'Final Approved';
    }

    if (status === 'rejected') {
        return 'Rejected';
    }

    return 'Ready for Final Approval';
};

const resetFilters = () => {
    searchQuery.value = '';
    selectedYearLevel.value = 'all';
    selectedDepartment.value = 'all';
    activeFilter.value = 'all';
};

const selectedRequest = ref<ClearanceRequest | null>(null);
const showFinalApproveModal = ref(false);
const showAutoApproveModal = ref(false);
const showRejectModal = ref(false);
const rejectRemarks = ref('');
const isSubmittingReject = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const searchQuery = ref('');
const selectedDepartment = ref('all');
const selectedYearLevel = ref('all');

const availableYearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

const availableDepartments = computed(() => {
    if (props.courses && props.courses.length > 0) {
        return props.courses.map((c) => ({ code: c.code, name: c.name }));
    }

    const map = new Map<string, string>();

    approvalRequests.value.forEach((r) => {
        if (r.user?.course?.code) {
            map.set(
                r.user.course.code,
                r.user.course.name || r.user.course.code,
            );
        }
    });

    return Array.from(map.entries()).map(([code, name]) => ({ code, name }));
});

const filteredApprovalRequests = computed(() => {
    return approvalRequests.value.filter((req) => {
        if (activeFilter.value !== 'all') {
            if (getRequestStatus(req) !== activeFilter.value) {
                return false;
            }
        }

        if (selectedDepartment.value !== 'all') {
            if (req.user?.course?.code !== selectedDepartment.value) {
                return false;
            }
        }

        if (selectedYearLevel.value !== 'all') {
            if (req.user?.year_level !== selectedYearLevel.value) {
                return false;
            }
        }

        if (searchQuery.value.trim()) {
            const q = searchQuery.value.toLowerCase().trim();
            const student = req.user;

            if (!student) {
                return false;
            }

            const formattedName = formatStudentName(student).toLowerCase();
            const matchesName =
                student.name?.toLowerCase().includes(q) ||
                formattedName.includes(q);
            const matchesFirst = student.first_name?.toLowerCase().includes(q);
            const matchesLast = student.last_name?.toLowerCase().includes(q);
            const matchesId = student.student_id?.toLowerCase().includes(q);
            const matchesCourse =
                student.course?.code?.toLowerCase().includes(q) ||
                student.course?.name?.toLowerCase().includes(q);
            const matchesYear = student.year_level?.toLowerCase().includes(q);
            const matchesReceipt = req.receipt_number
                ?.toLowerCase()
                .includes(q);

            if (
                !matchesName &&
                !matchesFirst &&
                !matchesLast &&
                !matchesId &&
                !matchesCourse &&
                !matchesYear &&
                !matchesReceipt
            ) {
                return false;
            }
        }

        return true;
    });
});

const formatStudentName = (user?: Student | null) => {
    if (!user) {
        return 'N/A';
    }

    if (user.last_name && user.first_name) {
        return `${user.last_name}, ${user.first_name}`;
    }

    if (user.last_name) {
        return user.last_name;
    }

    if (user.name) {
        if (user.name.includes(',')) {
            return user.name;
        }

        const parts = user.name.trim().split(/\s+/);

        if (parts.length > 1) {
            const lastName = parts.pop();
            const firstName = parts.join(' ');

            return `${lastName}, ${firstName}`;
        }

        return user.name;
    }

    return 'N/A';
};

const clearMessages = () => {
    successMessage.value = '';
    errorMessage.value = '';
};

const openFinalApproveModal = (request: ClearanceRequest) => {
    clearMessages();
    selectedRequest.value = request;
    showFinalApproveModal.value = true;
};

const closeFinalApproveModal = () => {
    selectedRequest.value = null;
    showFinalApproveModal.value = false;
};

const openAutoApproveModal = () => {
    if (readyApprovalCount.value === 0) {
        return;
    }

    clearMessages();
    showAutoApproveModal.value = true;
};

const closeAutoApproveModal = () => {
    showAutoApproveModal.value = false;
};

const confirmFinalApproval = () => {
    if (!selectedRequest.value) {
        return;
    }

    clearMessages();

    router.patch(
        `/president/final-approvals/${selectedRequest.value.id}/approve`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                closeFinalApproveModal();
                successMessage.value =
                    'Final clearance approval completed successfully.';
            },
            onError: () => {
                errorMessage.value =
                    'Unable to complete final approval. Please try again.';
            },
        },
    );
};

const openRejectModal = (request: ClearanceRequest) => {
    clearMessages();
    selectedRequest.value = request;
    rejectRemarks.value = '';
    showRejectModal.value = true;
};

const closeRejectModal = () => {
    selectedRequest.value = null;
    rejectRemarks.value = '';
    showRejectModal.value = false;
};

const confirmReject = () => {
    if (!selectedRequest.value || !rejectRemarks.value.trim()) {
        return;
    }

    clearMessages();
    isSubmittingReject.value = true;

    router.patch(
        `/president/final-approvals/${selectedRequest.value.id}/reject`,
        {
            remarks: rejectRemarks.value.trim(),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                closeRejectModal();
                successMessage.value = 'Clearance request has been rejected.';
            },
            onError: () => {
                errorMessage.value =
                    'Unable to reject clearance request. Please try again.';
            },
            onFinish: () => {
                isSubmittingReject.value = false;
            },
        },
    );
};

const confirmAutoApproveAll = () => {
    clearMessages();

    router.patch(
        '/president/final-approvals/approve-all',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                closeAutoApproveModal();
                successMessage.value =
                    'All ready clearance requests have been automatically approved.';
            },
            onError: () => {
                errorMessage.value =
                    'Unable to auto approve ready requests. Please try again.';
            },
        },
    );
};
</script>

<template>
    <Head title="President Final Approvals" />

    <div
        class="min-h-screen bg-linear-to-br from-slate-50 via-white to-blue-50/40 p-4 pb-12 text-slate-900 sm:p-6 sm:pb-12 md:p-8 md:pb-8"
    >
        <div class="mx-auto flex max-w-7xl flex-col gap-4 md:gap-6">
            <!-- Hero -->
            <section
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-xl shadow-slate-200/70 md:rounded-4xl"
            >
                <div
                    class="grid gap-6 p-4 sm:p-6 lg:grid-cols-[1fr_300px] lg:p-8"
                >
                    <div>
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-2 text-[0.65rem] font-black tracking-[0.14em] text-blue-700 uppercase sm:px-4 sm:text-xs sm:tracking-[0.18em]"
                        >
                            <ShieldCheck class="size-4" />
                            College President • Final Clearance Authority
                        </div>

                        <div
                            class="mt-5 flex flex-col gap-5 md:flex-row md:items-start md:justify-between"
                        >
                            <div class="min-w-0">
                                <h1
                                    class="text-3xl font-black tracking-tight text-blue-950 sm:text-4xl"
                                >
                                    Final Clearance Approvals
                                </h1>

                                <p
                                    class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base sm:leading-7"
                                >
                                    Review students whose clearance requests
                                    have passed all regular office approvals and
                                    are ready for final clearance approval.
                                </p>
                            </div>

                            <button
                                type="button"
                                class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black text-white shadow-lg transition disabled:translate-y-0 disabled:cursor-not-allowed disabled:shadow-none md:w-auto"
                                :class="
                                    readyApprovalCount === 0
                                        ? 'bg-slate-400 shadow-slate-400/20'
                                        : 'bg-green-700 shadow-green-700/20 hover:-translate-y-0.5 hover:bg-green-800 hover:shadow-xl'
                                "
                                :disabled="readyApprovalCount === 0"
                                @click="openAutoApproveModal"
                            >
                                <Sparkles class="size-4" />
                                Auto Approve All Ready Requests
                            </button>
                        </div>

                        <div
                            class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/80 p-4 text-sm leading-6 font-medium text-blue-900"
                        >
                            Only requests approved by all regular offices are
                            shown here. You may approve one student manually, or
                            use Auto Approve All to approve every currently
                            ready request.
                        </div>

                        <div
                            v-if="successMessage"
                            class="mt-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-black text-green-700"
                        >
                            {{ successMessage }}
                        </div>

                        <div
                            v-if="errorMessage"
                            class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-black text-red-700"
                        >
                            {{ errorMessage }}
                        </div>
                    </div>

                    <div class="hidden items-center justify-center lg:flex">
                        <div
                            class="relative grid h-52 w-52 place-items-center rounded-4xl border border-blue-100 bg-linear-to-br from-white to-blue-50 shadow-2xl shadow-slate-300/70"
                        >
                            <div
                                class="grid h-20 w-20 place-items-center rounded-3xl bg-blue-700 text-white shadow-xl shadow-blue-700/25"
                            >
                                <ShieldCheck class="size-10" />
                            </div>

                            <p
                                class="text-center text-sm font-black tracking-[0.18em] text-blue-700 uppercase"
                            >
                                Final Authority
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Summary Stats -->
            <section
                class="grid grid-cols-2 gap-3 sm:gap-4 md:gap-5 lg:grid-cols-4"
            >
                <!-- Card 1: Total Requests -->
                <div
                    class="cursor-pointer rounded-2xl border bg-white/95 p-4 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:rounded-3xl md:p-6"
                    :class="
                        activeFilter === 'all'
                            ? 'border-blue-500 ring-2 ring-blue-500/30'
                            : 'border-slate-200'
                    "
                    @click="setFilter('all')"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-blue-50 text-blue-700 shadow-sm md:h-14 md:w-14"
                        >
                            <Inbox class="size-6 md:size-7" />
                        </div>

                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-blue-700 uppercase sm:text-sm"
                            >
                                Total
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ approvalRequests.length }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                All submissions
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Pending -->
                <div
                    class="cursor-pointer rounded-2xl border bg-white/95 p-4 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:rounded-3xl md:p-6"
                    :class="
                        activeFilter === 'pending'
                            ? 'border-orange-400 ring-2 ring-orange-400/30'
                            : 'border-slate-200'
                    "
                    @click="setFilter('pending')"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600 shadow-sm md:h-14 md:w-14"
                        >
                            <Clock3 class="size-6 md:size-7" />
                        </div>

                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-orange-600 uppercase sm:text-sm"
                            >
                                Pending
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ pendingRequests.length }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Waiting review
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Approved -->
                <div
                    class="cursor-pointer rounded-2xl border bg-white/95 p-4 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:rounded-3xl md:p-6"
                    :class="
                        activeFilter === 'approved'
                            ? 'border-green-500 ring-2 ring-green-500/30'
                            : 'border-slate-200'
                    "
                    @click="setFilter('approved')"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-green-50 text-green-700 shadow-sm md:h-14 md:w-14"
                        >
                            <CheckCircle2 class="size-6 md:size-7" />
                        </div>

                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-green-700 uppercase sm:text-sm"
                            >
                                Approved
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ approvedRequests.length }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Completed reviews
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Rejected -->
                <div
                    class="cursor-pointer rounded-2xl border bg-white/95 p-4 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:rounded-3xl md:p-6"
                    :class="
                        activeFilter === 'rejected'
                            ? 'border-red-400 ring-2 ring-red-400/30'
                            : 'border-slate-200'
                    "
                    @click="setFilter('rejected')"
                >
                    <div class="flex items-center gap-3 md:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-red-50 text-red-600 shadow-sm md:h-14 md:w-14"
                        >
                            <XCircle class="size-6 md:size-7" />
                        </div>

                        <div>
                            <p
                                class="text-[0.65rem] leading-tight font-black tracking-wide text-red-600 uppercase sm:text-sm"
                            >
                                Rejected
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-blue-950 md:text-4xl"
                            >
                                {{ rejectedRequests.length }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Needs correction
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Requests -->
            <section
                id="final-approval-queue"
                class="scroll-mt-4 overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-sm shadow-slate-200/70"
            >
                <div
                    class="border-b border-slate-200 bg-white px-4 py-5 sm:px-6"
                >
                    <div
                        class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-black tracking-[0.18em] text-slate-400 uppercase"
                            >
                                Final Approval Queue
                            </p>

                            <h2 class="mt-1 text-xl font-black text-blue-950">
                                Clearance Requests
                            </h2>

                            <p class="mt-1 text-sm font-medium text-slate-500">
                                Filter pending, approved, and rejected requests
                                for final clearance.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <div
                                class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap"
                            >
                                <button
                                    type="button"
                                    class="inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                                    :class="filterButtonClass('all')"
                                    @click.prevent.stop="setFilter('all')"
                                >
                                    <Filter class="size-4" />
                                    All
                                    <span
                                        class="ml-1 rounded-full px-2 py-0.5 text-[0.7rem] font-black"
                                        :class="
                                            activeFilter === 'all'
                                                ? 'bg-white/25 text-white'
                                                : 'bg-slate-100 text-slate-600'
                                        "
                                    >
                                        {{ approvalRequests.length }}
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                                    :class="filterButtonClass('pending')"
                                    @click.prevent.stop="setFilter('pending')"
                                >
                                    <Clock3 class="size-4" />
                                    Pending
                                    <span
                                        class="ml-1 rounded-full px-2 py-0.5 text-[0.7rem] font-black"
                                        :class="
                                            activeFilter === 'pending'
                                                ? 'bg-white/25 text-white'
                                                : 'bg-orange-100 text-orange-700'
                                        "
                                    >
                                        {{ pendingRequests.length }}
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                                    :class="filterButtonClass('approved')"
                                    @click.prevent.stop="setFilter('approved')"
                                >
                                    <CheckCircle2 class="size-4" />
                                    Approved
                                    <span
                                        class="ml-1 rounded-full px-2 py-0.5 text-[0.7rem] font-black"
                                        :class="
                                            activeFilter === 'approved'
                                                ? 'bg-white/25 text-white'
                                                : 'bg-green-100 text-green-700'
                                        "
                                    >
                                        {{ approvedRequests.length }}
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                                    :class="filterButtonClass('rejected')"
                                    @click.prevent.stop="setFilter('rejected')"
                                >
                                    <XCircle class="size-4" />
                                    Rejected
                                    <span
                                        class="ml-1 rounded-full px-2 py-0.5 text-[0.7rem] font-black"
                                        :class="
                                            activeFilter === 'rejected'
                                                ? 'bg-white/25 text-white'
                                                : 'bg-red-100 text-red-700'
                                        "
                                    >
                                        {{ rejectedRequests.length }}
                                    </span>
                                </button>
                            </div>

                            <button
                                type="button"
                                class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-black text-white shadow-md transition disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                                :class="
                                    readyApprovalCount === 0
                                        ? 'bg-slate-400 shadow-slate-400/20'
                                        : 'bg-green-700 shadow-green-700/20 hover:-translate-y-0.5 hover:bg-green-800 hover:shadow-lg'
                                "
                                :disabled="readyApprovalCount === 0"
                                @click="openAutoApproveModal"
                            >
                                <Sparkles class="size-4" />
                                Auto Approve All
                            </button>
                        </div>
                    </div>

                    <!-- Secondary Bar: Search, Department & Year Level Filters -->
                    <div
                        v-if="approvalRequests.length > 0"
                        class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <!-- Search Box -->
                        <div class="relative min-w-[200px] flex-1">
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-slate-400"
                            />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by student name, ID, course, year, or receipt..."
                                class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50/70 pr-10 pl-10 text-sm font-semibold text-slate-900 shadow-inner transition placeholder:font-medium placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:outline-none"
                            />
                            <button
                                v-if="searchQuery"
                                type="button"
                                class="absolute top-1/2 right-3 -translate-y-1/2 rounded-full p-1 text-slate-400 transition hover:bg-slate-200 hover:text-slate-700"
                                @click="searchQuery = ''"
                            >
                                <X class="size-3.5" />
                            </button>
                        </div>

                        <!-- Dropdowns Container -->
                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            <!-- Department Selector -->
                            <div
                                class="relative min-w-[160px] sm:min-w-[185px]"
                            >
                                <Building2
                                    class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-blue-600"
                                />
                                <select
                                    v-model="selectedDepartment"
                                    class="h-11 w-full cursor-pointer appearance-none rounded-2xl border border-slate-200 bg-slate-50/70 pr-9 pl-10 text-sm font-semibold text-slate-900 shadow-inner transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:outline-none"
                                >
                                    <option value="all">All Departments</option>
                                    <option
                                        v-for="dept in availableDepartments"
                                        :key="dept.code"
                                        :value="dept.code"
                                    >
                                        {{ dept.code }} - {{ dept.name }}
                                    </option>
                                </select>
                                <div
                                    class="pointer-events-none absolute top-1/2 right-3.5 -translate-y-1/2 text-slate-400"
                                >
                                    <svg
                                        class="size-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 9l-7 7-7-7"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <!-- Year Level Selector -->
                            <div
                                class="relative min-w-[150px] sm:min-w-[170px]"
                            >
                                <GraduationCap
                                    class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-indigo-600"
                                />
                                <select
                                    v-model="selectedYearLevel"
                                    class="h-11 w-full cursor-pointer appearance-none rounded-2xl border border-slate-200 bg-slate-50/70 pr-9 pl-10 text-sm font-semibold text-slate-900 shadow-inner transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:outline-none"
                                >
                                    <option value="all">All Year Levels</option>
                                    <option
                                        v-for="year in availableYearLevels"
                                        :key="year"
                                        :value="year"
                                    >
                                        {{ year }}
                                    </option>
                                </select>
                                <div
                                    class="pointer-events-none absolute top-1/2 right-3.5 -translate-y-1/2 text-slate-400"
                                >
                                    <svg
                                        class="size-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 9l-7 7-7-7"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <button
                                v-if="
                                    searchQuery ||
                                    selectedYearLevel !== 'all' ||
                                    selectedDepartment !== 'all' ||
                                    activeFilter !== 'pending'
                                "
                                type="button"
                                class="inline-flex h-11 items-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-3.5 text-xs font-bold text-slate-600 shadow-sm transition hover:bg-slate-100 hover:text-slate-900"
                                title="Clear search and filters"
                                @click="resetFilters"
                            >
                                <X class="size-3.5" />
                                <span class="hidden sm:inline">Reset</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    v-if="approvalRequests.length === 0"
                    class="p-8 text-center sm:p-12"
                >
                    <div
                        class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-blue-50 text-blue-700"
                    >
                        <Users class="size-8" />
                    </div>

                    <p class="mt-4 font-black text-slate-700">
                        No clearance requests found.
                    </p>

                    <p class="mt-1 text-sm font-medium text-slate-500">
                        Requests will appear here after all regular offices have
                        approved them.
                    </p>
                </div>

                <div
                    v-else-if="filteredApprovalRequests.length === 0"
                    class="p-8 text-center sm:p-12"
                >
                    <div
                        class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-blue-50 text-blue-700"
                    >
                        <Inbox class="size-8" />
                    </div>

                    <p class="mt-4 font-black text-slate-700">
                        No records found.
                    </p>

                    <p class="mt-1 text-sm font-medium text-slate-500">
                        <span
                            v-if="
                                searchQuery ||
                                selectedYearLevel !== 'all' ||
                                selectedDepartment !== 'all'
                            "
                        >
                            No requests match your current search, department,
                            or year level filter.
                        </span>
                        <span v-else>
                            No {{ activeFilter }} clearance requests found.
                        </span>
                    </p>

                    <button
                        type="button"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-black text-blue-700 shadow-sm transition hover:bg-slate-50"
                        @click="resetFilters"
                    >
                        <X class="size-3.5" />
                        Reset All Filters
                    </button>
                </div>

                <div v-else>
                    <!-- Mobile Card List -->
                    <div class="grid gap-3 p-4 lg:hidden">
                        <article
                            v-for="request in filteredApprovalRequests"
                            :key="request.id"
                            class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3
                                        class="line-clamp-2 text-base leading-tight font-black break-words text-blue-950"
                                    >
                                        {{ formatStudentName(request.user) }}
                                    </h3>

                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-600"
                                    >
                                        {{ request.user.student_id }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs font-medium text-slate-500"
                                    >
                                        Clearance request #{{ request.id }}
                                    </p>
                                </div>

                                <span
                                    class="shrink-0 rounded-full border px-3 py-1 text-xs font-black"
                                    :class="
                                        statusBadgeClass(
                                            getRequestStatus(request),
                                        )
                                    "
                                >
                                    {{ statusLabel(getRequestStatus(request)) }}
                                </span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span
                                    class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700"
                                >
                                    {{
                                        request.user.course
                                            ? request.user.course.code
                                            : 'N/A'
                                    }}
                                </span>

                                <span
                                    v-if="request.user.year_level"
                                    class="inline-flex items-center gap-1 rounded-full border border-purple-200 bg-purple-50 px-2.5 py-1 text-xs font-black text-purple-700"
                                >
                                    <GraduationCap class="size-3" />
                                    {{ request.user.year_level }}
                                </span>

                                <span
                                    class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-600"
                                >
                                    {{ request.semester }}
                                </span>

                                <span
                                    class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-600"
                                >
                                    {{ request.school_year }}
                                </span>
                            </div>

                            <div
                                v-if="getRequestStatus(request) === 'pending'"
                                class="mt-3 rounded-xl border border-green-100 bg-green-50 px-3 py-2 text-sm leading-6 font-medium text-green-800"
                            >
                                <span class="font-black">
                                    Office approval status:
                                </span>
                                All regular office approvals are complete. Ready
                                for President final action.
                            </div>

                            <div
                                v-else-if="
                                    getRequestStatus(request) === 'approved'
                                "
                                class="mt-3 rounded-xl border border-green-200 bg-green-50 px-3 py-2 text-sm font-medium text-green-800"
                            >
                                <span class="font-black">Receipt No:</span>
                                {{
                                    request.receipt_number ??
                                    'Cleared & Receipt Generated'
                                }}
                            </div>

                            <div
                                v-else-if="
                                    getRequestStatus(request) === 'rejected'
                                "
                                class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-800"
                            >
                                <span class="font-black"
                                    >Rejection Remarks:</span
                                >
                                {{
                                    getPresidentApproval(request)?.remarks ??
                                    '-'
                                }}
                            </div>

                            <div
                                v-if="getRequestStatus(request) === 'pending'"
                                class="mt-4 grid grid-cols-2 gap-2"
                            >
                                <button
                                    type="button"
                                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-2xl border border-red-200 bg-white px-4 py-3 text-sm font-black text-red-600 shadow-sm transition hover:bg-red-50"
                                    @click="openRejectModal(request)"
                                >
                                    <XCircle class="size-4" />
                                    Reject
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-2xl bg-green-700 px-4 py-3 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:bg-green-800"
                                    @click="openFinalApproveModal(request)"
                                >
                                    <CheckCircle2 class="size-4" />
                                    Final Approve
                                </button>
                            </div>

                            <div v-else class="mt-4">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black"
                                    :class="
                                        getRequestStatus(request) === 'approved'
                                            ? 'bg-slate-100 text-slate-500'
                                            : 'bg-red-100 text-red-700'
                                    "
                                >
                                    <CheckCircle2
                                        v-if="
                                            getRequestStatus(request) ===
                                            'approved'
                                        "
                                        class="size-3.5"
                                    />
                                    <XCircle v-else class="size-3.5" />
                                    {{
                                        getRequestStatus(request) === 'approved'
                                            ? 'Completed'
                                            : 'Rejected'
                                    }}
                                </span>
                            </div>
                        </article>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden overflow-x-auto lg:block">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        Student
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        Student ID
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        Course
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        Year Level
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        Semester
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        School Year
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        Status
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        Remarks / Receipt
                                    </th>

                                    <th
                                        class="px-6 py-4 text-right text-xs font-black tracking-wide uppercase"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="request in filteredApprovalRequests"
                                    :key="request.id"
                                    class="transition hover:bg-blue-50/50"
                                >
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-black text-blue-950">
                                                {{
                                                    formatStudentName(
                                                        request.user,
                                                    )
                                                }}
                                            </p>

                                            <p
                                                class="mt-1 text-xs font-medium text-slate-500"
                                            >
                                                Clearance request #{{
                                                    request.id
                                                }}
                                            </p>
                                        </div>
                                    </td>

                                    <td
                                        class="px-6 py-4 font-semibold text-slate-700"
                                    >
                                        {{ request.user.student_id }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700"
                                        >
                                            {{
                                                request.user.course
                                                    ? request.user.course.code
                                                    : 'N/A'
                                            }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            v-if="request.user.year_level"
                                            class="inline-flex items-center gap-1.5 rounded-full border border-purple-200 bg-purple-50 px-3 py-1 text-xs font-black text-purple-700"
                                        >
                                            <GraduationCap class="size-3.5" />
                                            {{ request.user.year_level }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-xs font-medium text-slate-400"
                                        >
                                            N/A
                                        </span>
                                    </td>

                                    <td
                                        class="px-6 py-4 font-semibold text-slate-700"
                                    >
                                        {{ request.semester }}
                                    </td>

                                    <td
                                        class="px-6 py-4 font-semibold text-slate-700"
                                    >
                                        {{ request.school_year }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                            :class="
                                                statusBadgeClass(
                                                    getRequestStatus(request),
                                                )
                                            "
                                        >
                                            {{
                                                statusLabel(
                                                    getRequestStatus(request),
                                                )
                                            }}
                                        </span>
                                    </td>

                                    <td
                                        class="max-w-xs px-6 py-4 text-sm font-medium text-slate-600"
                                    >
                                        <span
                                            v-if="
                                                getRequestStatus(request) ===
                                                'approved'
                                            "
                                            class="font-semibold text-green-700"
                                        >
                                            {{
                                                request.receipt_number ??
                                                'Receipt Generated'
                                            }}
                                        </span>
                                        <span
                                            v-else-if="
                                                getRequestStatus(request) ===
                                                'rejected'
                                            "
                                            class="font-medium text-red-700"
                                        >
                                            {{
                                                getPresidentApproval(request)
                                                    ?.remarks ?? '-'
                                            }}
                                        </span>
                                        <span v-else class="text-slate-400">
                                            Ready for final review
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div
                                            v-if="
                                                getRequestStatus(request) ===
                                                'pending'
                                            "
                                            class="inline-flex items-center gap-2"
                                        >
                                            <button
                                                type="button"
                                                class="inline-flex min-h-11 items-center gap-2 rounded-2xl border border-red-200 bg-white px-4 py-2.5 text-sm font-black text-red-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-red-50 hover:text-red-700 hover:shadow-md"
                                                @click="
                                                    openRejectModal(request)
                                                "
                                            >
                                                <XCircle class="size-4" />
                                                Reject
                                            </button>

                                            <button
                                                type="button"
                                                class="inline-flex min-h-11 items-center gap-2 rounded-2xl bg-green-700 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:-translate-y-0.5 hover:bg-green-800 hover:shadow-lg"
                                                @click="
                                                    openFinalApproveModal(
                                                        request,
                                                    )
                                                "
                                            >
                                                <CheckCircle2 class="size-4" />
                                                Final Approve
                                            </button>
                                        </div>

                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black"
                                            :class="
                                                getRequestStatus(request) ===
                                                'approved'
                                                    ? 'bg-slate-100 text-slate-500'
                                                    : 'bg-red-100 text-red-700'
                                            "
                                        >
                                            <CheckCircle2
                                                v-if="
                                                    getRequestStatus(
                                                        request,
                                                    ) === 'approved'
                                                "
                                                class="size-3.5"
                                            />
                                            <XCircle v-else class="size-3.5" />
                                            {{
                                                getRequestStatus(request) ===
                                                'approved'
                                                    ? 'Completed'
                                                    : 'Rejected'
                                            }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Single Final Approval Modal -->
    <div
        v-if="showFinalApproveModal"
        class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/50 p-3 backdrop-blur-sm sm:items-center sm:p-4"
        @click.self="closeFinalApproveModal"
    >
        <div
            class="flex max-h-[92dvh] w-full max-w-md flex-col overflow-hidden rounded-t-4xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20 sm:max-h-[90vh] sm:rounded-4xl"
        >
            <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-start gap-3 sm:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-green-50 text-green-700 sm:h-12 sm:w-12"
                        >
                            <CheckCircle2 class="size-6" />
                        </div>

                        <div class="min-w-0">
                            <h2
                                class="text-lg font-black text-blue-950 sm:text-xl"
                            >
                                Confirm Final Approval
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Are you sure you want to give final clearance
                                approval to this student?
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="grid size-10 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                        @click="closeFinalApproveModal"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <div
                    v-if="selectedRequest"
                    class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700"
                >
                    <div class="grid gap-3">
                        <p>
                            <span class="font-black">Student:</span>
                            {{ formatStudentName(selectedRequest.user) }}
                        </p>

                        <p>
                            <span class="font-black">Student ID:</span>
                            {{ selectedRequest.user.student_id }}
                        </p>

                        <p>
                            <span class="font-black">Course:</span>
                            {{
                                selectedRequest.user.course
                                    ? selectedRequest.user.course.code
                                    : 'N/A'
                            }}
                        </p>

                        <p>
                            <span class="font-black">Year Level:</span>
                            {{ selectedRequest.user.year_level || 'N/A' }}
                        </p>

                        <p>
                            <span class="font-black">Semester:</span>
                            {{ selectedRequest.semester }}
                        </p>

                        <p>
                            <span class="font-black">School Year:</span>
                            {{ selectedRequest.school_year }}
                        </p>
                    </div>
                </div>

                <div
                    class="mt-4 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm leading-6 font-medium text-green-800"
                >
                    This action will mark the request as approved and enable the
                    student's clearance receipt and public QR verification.
                </div>
            </div>

            <div class="shrink-0 border-t border-slate-200 bg-white p-4 sm:p-6">
                <div class="grid grid-cols-2 gap-3 sm:flex sm:justify-end">
                    <button
                        type="button"
                        class="min-h-11 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="closeFinalApproveModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="min-h-11 rounded-2xl bg-green-700 px-4 py-3 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:bg-green-800"
                        @click="confirmFinalApproval"
                    >
                        Confirm Approval
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto Approve All Modal -->
    <div
        v-if="showAutoApproveModal"
        class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/50 p-3 backdrop-blur-sm sm:items-center sm:p-4"
        @click.self="closeAutoApproveModal"
    >
        <div
            class="flex max-h-[92dvh] w-full max-w-md flex-col overflow-hidden rounded-t-4xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20 sm:max-h-[90vh] sm:rounded-4xl"
        >
            <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-start gap-3 sm:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-green-50 text-green-700 sm:h-12 sm:w-12"
                        >
                            <Sparkles class="size-6" />
                        </div>

                        <div class="min-w-0">
                            <h2
                                class="text-lg font-black text-blue-950 sm:text-xl"
                            >
                                Auto Approve All Ready Requests?
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                This will give final approval to all clearance
                                requests currently ready for President approval.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="grid size-10 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                        @click="closeAutoApproveModal"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <div
                    class="mt-5 rounded-2xl border border-green-200 bg-green-50 p-4"
                >
                    <p class="text-sm font-black text-green-800">
                        Requests to approve:
                    </p>

                    <p class="mt-1 text-4xl font-black text-green-900">
                        {{ readyApprovalCount }}
                    </p>

                    <p class="mt-2 text-sm leading-6 text-green-800">
                        Each request will be marked as approved, assigned a
                        receipt number, assigned a verification code, and the
                        student will receive a notification.
                    </p>
                </div>

                <div
                    class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 font-medium text-amber-900"
                >
                    Manual final approval is still available if you want to
                    review each student one by one before approving.
                </div>
            </div>

            <div class="shrink-0 border-t border-slate-200 bg-white p-4 sm:p-6">
                <div class="grid grid-cols-2 gap-3 sm:flex sm:justify-end">
                    <button
                        type="button"
                        class="min-h-11 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="closeAutoApproveModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="min-h-11 rounded-2xl bg-green-700 px-4 py-3 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:bg-green-800"
                        @click="confirmAutoApproveAll"
                    >
                        Confirm Auto Approve All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Single Final Rejection Modal -->
    <div
        v-if="showRejectModal"
        class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/50 p-3 backdrop-blur-sm sm:items-center sm:p-4"
        @click.self="closeRejectModal"
    >
        <div
            class="flex max-h-[92dvh] w-full max-w-lg flex-col overflow-hidden rounded-t-4xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20 sm:max-h-[90vh] sm:rounded-4xl"
        >
            <div class="shrink-0 border-b border-slate-200 p-4 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-start gap-3 sm:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-red-50 text-red-600 sm:h-12 sm:w-12"
                        >
                            <XCircle class="size-6" />
                        </div>

                        <div class="min-w-0">
                            <h2
                                class="text-lg font-black text-blue-950 sm:text-xl"
                            >
                                Reject Final Clearance
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Provide a clear reason why this clearance
                                request is being rejected. The student will be
                                notified and guided to resolve the issue.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="grid size-10 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                        @click="closeRejectModal"
                    >
                        <X class="size-5" />
                    </button>
                </div>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6">
                <div
                    v-if="selectedRequest"
                    class="mb-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700"
                >
                    <div class="grid gap-2">
                        <p>
                            <span class="font-black">Student:</span>
                            {{ formatStudentName(selectedRequest.user) }}
                            <span class="text-xs font-semibold text-slate-500">
                                ({{ selectedRequest.user.student_id }})
                            </span>
                        </p>
                        <p>
                            <span class="font-black">Course & Year:</span>
                            {{ selectedRequest.user.course?.code ?? 'N/A' }}
                            <span
                                v-if="selectedRequest.user.year_level"
                                class="ml-1.5 inline-flex items-center gap-1 rounded-full bg-purple-50 px-2 py-0.5 text-xs font-black text-purple-700"
                            >
                                <GraduationCap class="size-3" />
                                {{ selectedRequest.user.year_level }}
                            </span>
                        </p>
                        <p>
                            <span class="font-black"
                                >Semester & School Year:</span
                            >
                            {{ selectedRequest.semester }} •
                            {{ selectedRequest.school_year }}
                        </p>
                    </div>
                </div>

                <div>
                    <label
                        for="president-reject-remarks"
                        class="text-sm font-black text-slate-700"
                    >
                        Rejection Remarks <span class="text-red-500">*</span>
                    </label>

                    <textarea
                        id="president-reject-remarks"
                        v-model="rejectRemarks"
                        rows="5"
                        class="mt-2 min-h-32 w-full rounded-2xl border border-slate-200 bg-white p-4 text-sm font-medium text-slate-900 shadow-sm outline-none placeholder:text-slate-400 focus:border-red-500 focus:ring-4 focus:ring-red-500/20"
                        placeholder="State the reason for rejecting this clearance request (e.g. pending institutional obligation, discrepancy in records)..."
                    ></textarea>

                    <p class="mt-2 text-xs font-medium text-slate-500">
                        Remarks are required so the student knows what
                        requirements to fulfill.
                    </p>
                </div>
            </div>

            <div class="shrink-0 border-t border-slate-200 bg-white p-4 sm:p-6">
                <div class="grid grid-cols-2 gap-3 sm:flex sm:justify-end">
                    <button
                        type="button"
                        class="min-h-11 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="closeRejectModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="min-h-11 rounded-2xl bg-red-600 px-4 py-3 text-sm font-black text-white shadow-md shadow-red-600/20 transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="!rejectRemarks.trim() || isSubmittingReject"
                        @click="confirmReject"
                    >
                        {{
                            isSubmittingReject
                                ? 'Rejecting...'
                                : 'Reject Clearance'
                        }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
