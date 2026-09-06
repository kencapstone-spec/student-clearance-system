<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Building2,
    CheckCircle2,
    ClipboardCheck,
    Clock3,
    Filter,
    GraduationCap,
    Inbox,
    Search,
    X,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref, onMounted, onUnmounted } from 'vue';

let pollingInterval: ReturnType<typeof setInterval>;

onMounted(() => {
    pollingInterval = setInterval(() => {
        router.reload({
            data: { _t: Date.now() },
            only: ['approvals', 'notifications'],
        });
    }, 5000);
});

onUnmounted(() => {
    clearInterval(pollingInterval);
});

type Office = {
    id: number;
    name: string;
    group: string;
};

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
    course?: Course | null;
};

type ClearanceRequest = {
    id: number;
    semester: string;
    school_year: string;
    user: Student;
};

type Approval = {
    id: number;
    status: 'pending' | 'approved' | 'rejected';
    remarks?: string | null;
    clearance_request: ClearanceRequest;
    office: Office;
};

type Staff = {
    id: number;
    name: string;
    student_id: string;
    role: string;
    office?: Office | null;
};

type FilterStatus = 'all' | 'pending' | 'approved' | 'rejected';

const props = defineProps<{
    staff: Staff;
    approvals: Approval[];
    courses?: Course[];
}>();

const activeFilter = ref<FilterStatus>('pending');
const searchQuery = ref('');
const selectedYearLevel = ref('all');
const selectedDepartment = ref('all');

const successMessage = ref('');
const errorMessage = ref('');

const selectedRejectApprovalId = ref<number | null>(null);
const rejectRemarks = ref('');
const showRejectModal = ref(false);

const selectedApprovalForApproval = ref<Approval | null>(null);
const showApproveModal = ref(false);

const showApproveAllModal = ref(false);
const isApprovingAll = ref(false);

const clearMessages = () => {
    successMessage.value = '';
    errorMessage.value = '';
};

const setFilter = (filter: FilterStatus) => {
    activeFilter.value = filter;
};

const pendingApprovals = computed(() => {
    return props.approvals.filter((approval) => approval.status === 'pending');
});

const approvedApprovals = computed(() => {
    return props.approvals.filter((approval) => approval.status === 'approved');
});

const rejectedApprovals = computed(() => {
    return props.approvals.filter((approval) => approval.status === 'rejected');
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

const availableYearLevels = computed(() => {
    const levels = new Set<string>();
    props.approvals.forEach((approval) => {
        if (approval.clearance_request?.user?.year_level) {
            levels.add(approval.clearance_request.user.year_level);
        }
    });
    const standard = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

    return Array.from(new Set([...standard, ...Array.from(levels)]));
});

const availableDepartments = computed(() => {
    if (props.courses && props.courses.length > 0) {
        return props.courses.map((c) => ({
            code: c.code,
            name: c.name,
        }));
    }

    const map = new Map<string, string>();
    props.approvals.forEach((approval) => {
        const course = approval.clearance_request?.user?.course;

        if (course?.code) {
            map.set(course.code, course.name || course.code);
        }
    });

    return Array.from(map.entries()).map(([code, name]) => ({ code, name }));
});

const filteredApprovals = computed(() => {
    return props.approvals.filter((approval) => {
        // Status filter
        if (
            activeFilter.value !== 'all' &&
            approval.status !== activeFilter.value
        ) {
            return false;
        }

        // Year Level filter
        if (selectedYearLevel.value !== 'all') {
            const studentYear = approval.clearance_request?.user?.year_level;

            if (studentYear !== selectedYearLevel.value) {
                return false;
            }
        }

        // Department / Course filter
        if (selectedDepartment.value !== 'all') {
            const courseCode = approval.clearance_request?.user?.course?.code;

            if (courseCode !== selectedDepartment.value) {
                return false;
            }
        }

        // Search query filter (matches Name, Student ID, Course Code, Course Name, or Year Level)
        if (searchQuery.value.trim()) {
            const query = searchQuery.value.toLowerCase().trim();
            const student = approval.clearance_request?.user;

            if (!student) {
                return false;
            }

            const formattedName = formatStudentName(student).toLowerCase();
            const matchesName =
                student.name?.toLowerCase().includes(query) ||
                formattedName.includes(query);
            const matchesFirst = student.first_name
                ?.toLowerCase()
                .includes(query);
            const matchesLast = student.last_name
                ?.toLowerCase()
                .includes(query);
            const matchesId = student.student_id?.toLowerCase().includes(query);
            const matchesCourseCode = student.course?.code
                ?.toLowerCase()
                .includes(query);
            const matchesCourseName = student.course?.name
                ?.toLowerCase()
                .includes(query);
            const matchesYear = student.year_level
                ?.toLowerCase()
                .includes(query);

            if (
                !matchesName &&
                !matchesFirst &&
                !matchesLast &&
                !matchesId &&
                !matchesCourseCode &&
                !matchesCourseName &&
                !matchesYear
            ) {
                return false;
            }
        }

        return true;
    });
});

const selectedApprovalForReject = computed(() => {
    if (selectedRejectApprovalId.value === null) {
        return null;
    }

    return (
        props.approvals.find((a) => a.id === selectedRejectApprovalId.value) ??
        null
    );
});

const resetFilters = () => {
    searchQuery.value = '';
    selectedYearLevel.value = 'all';
    selectedDepartment.value = 'all';
    activeFilter.value = 'pending';
};

const openApproveModal = (approval: Approval) => {
    clearMessages();
    selectedApprovalForApproval.value = approval;
    showApproveModal.value = true;
};

const closeApproveModal = () => {
    selectedApprovalForApproval.value = null;
    showApproveModal.value = false;
};

const confirmApprove = () => {
    if (!selectedApprovalForApproval.value) {
        return;
    }

    clearMessages();

    router.patch(
        `/staff/clearance-approvals/${selectedApprovalForApproval.value.id}/approve`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                closeApproveModal();
                successMessage.value = 'Request approved successfully.';
            },
            onError: () => {
                errorMessage.value =
                    'Unable to approve request. Please try again.';
            },
        },
    );
};

const openApproveAllModal = () => {
    clearMessages();

    if (pendingApprovals.value.length === 0) {
        errorMessage.value = 'There are no pending requests to approve.';

        return;
    }

    showApproveAllModal.value = true;
};

const closeApproveAllModal = () => {
    if (isApprovingAll.value) {
        return;
    }

    showApproveAllModal.value = false;
};

const confirmApproveAll = () => {
    clearMessages();
    isApprovingAll.value = true;

    router.patch(
        '/staff/clearance-approvals/approve-all',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showApproveAllModal.value = false;
                successMessage.value =
                    'All pending requests for your office were approved successfully.';
                activeFilter.value = 'approved';
            },
            onError: () => {
                errorMessage.value =
                    'Unable to approve all pending requests. Please try again.';
            },
            onFinish: () => {
                isApprovingAll.value = false;
            },
        },
    );
};

const rejectRequest = (approvalId: number) => {
    clearMessages();
    selectedRejectApprovalId.value = approvalId;
    rejectRemarks.value = '';
    showRejectModal.value = true;
};

const closeRejectModal = () => {
    selectedRejectApprovalId.value = null;
    rejectRemarks.value = '';
    showRejectModal.value = false;
};

const submitRejectRequest = () => {
    if (selectedRejectApprovalId.value === null) {
        return;
    }

    if (!rejectRemarks.value.trim()) {
        errorMessage.value = 'Remarks are required.';

        return;
    }

    clearMessages();

    router.patch(
        `/staff/clearance-approvals/${selectedRejectApprovalId.value}/reject`,
        {
            remarks: rejectRemarks.value.trim(),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                closeRejectModal();
                successMessage.value = 'Request rejected successfully.';
            },
            onError: () => {
                errorMessage.value =
                    'Unable to reject request. Please try again.';
            },
        },
    );
};

const markAsComplied = (approvalId: number) => {
    clearMessages();
    router.patch(
        `/staff/clearance-approvals/${approvalId}/mark-as-complied`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                successMessage.value =
                    'Clearance marked as complied and moved to pending queue.';
            },
            onError: () => {
                errorMessage.value =
                    'Unable to mark as complied. Please try again.';
            },
        },
    );
};

const statusLabel = (status: string) => {
    if (status === 'approved') {
        return 'Approved';
    }

    if (status === 'rejected') {
        return 'Rejected';
    }

    return 'Pending';
};

const statusBadgeClass = (status: string) => {
    if (status === 'approved') {
        return 'border-green-200 bg-green-50 text-green-700';
    }

    if (status === 'rejected') {
        return 'border-red-200 bg-red-50 text-red-700';
    }

    return 'border-orange-200 bg-orange-50 text-orange-700';
};

const filterButtonClass = (filter: FilterStatus) => {
    if (activeFilter.value === filter) {
        return 'border-blue-700 bg-blue-700 text-white shadow-lg shadow-blue-700/20';
    }

    return 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700';
};
</script>

<template>
    <Head title="Staff Clearance Requests" />

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
                            <Building2 class="size-4" />
                            {{ staff.office?.name ?? 'Assigned Office' }} •
                            Approver Panel
                        </div>

                        <h1
                            class="mt-5 text-3xl font-black tracking-tight text-blue-950 sm:text-4xl"
                        >
                            Clearance Requests
                        </h1>

                        <p
                            class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base sm:leading-7"
                        >
                            Review, approve, or reject student clearance
                            requests assigned to your office.
                        </p>

                        <div
                            class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/80 p-4 text-sm leading-6 font-medium text-blue-900"
                        >
                            <span class="font-black">Logged in as:</span>
                            {{ staff.name }}
                            <br />
                            <span class="font-black">Role:</span>
                            Staff Approver for
                            {{ staff.office?.name ?? 'Assigned Office' }}
                        </div>

                        <div class="mt-5 flex flex-wrap gap-3">
                            <button
                                type="button"
                                class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl bg-green-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-green-700/20 transition hover:-translate-y-0.5 hover:bg-green-800 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                                :disabled="pendingApprovals.length === 0"
                                @click="openApproveAllModal"
                            >
                                <CheckCircle2 class="size-4" />
                                Approve All Pending
                            </button>
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
                                <ClipboardCheck class="size-10" />
                            </div>

                            <p
                                class="text-center text-sm font-black tracking-[0.18em] text-blue-700 uppercase"
                            >
                                Review Queue
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Summary Cards -->
            <section
                class="grid grid-cols-2 gap-3 sm:gap-4 md:gap-5 lg:grid-cols-4"
            >
                <!-- Card 1: Total -->
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
                                {{ approvals.length }}
                            </p>

                            <p
                                class="text-xs font-medium text-slate-500 sm:text-sm"
                            >
                                Office requests
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
                                {{ pendingApprovals.length }}
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
                                {{ approvedApprovals.length }}
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
                                {{ rejectedApprovals.length }}
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

            <!-- Records -->
            <section
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-sm shadow-slate-200/70"
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
                                Office Queue
                            </p>

                            <h2 class="mt-1 text-xl font-black text-blue-950">
                                Office Clearance Records
                            </h2>

                            <p class="mt-1 text-sm font-medium text-slate-500">
                                Filter pending, approved, and rejected requests
                                assigned to your office.
                            </p>
                        </div>

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
                                    {{ props.approvals.length }}
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
                                    {{ pendingApprovals.length }}
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
                                    {{ approvedApprovals.length }}
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
                                    {{ rejectedApprovals.length }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Secondary Bar: Search, Department & Year Level Filters -->
                    <div
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
                                placeholder="Search by student name, ID, course, or year..."
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
                                    selectedDepartment !== 'all'
                                "
                                type="button"
                                class="inline-flex h-11 items-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-3.5 text-xs font-bold text-slate-600 shadow-sm transition hover:bg-slate-100 hover:text-slate-900"
                                @click="
                                    searchQuery = '';
                                    selectedYearLevel = 'all';
                                    selectedDepartment = 'all';
                                "
                                title="Clear search and filters"
                            >
                                <X class="size-3.5" />
                                <span class="hidden sm:inline">Clear</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    v-if="filteredApprovals.length === 0"
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
                            Records will appear here based on the selected
                            filter.
                        </span>
                    </p>

                    <button
                        v-if="
                            searchQuery ||
                            selectedYearLevel !== 'all' ||
                            selectedDepartment !== 'all' ||
                            activeFilter !== 'pending'
                        "
                        type="button"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-black text-blue-700 shadow-sm transition hover:bg-slate-50"
                        @click="resetFilters"
                    >
                        Reset All Filters
                    </button>
                </div>

                <div v-else>
                    <!-- Mobile Card List -->
                    <div class="grid gap-3 p-4 lg:hidden">
                        <article
                            v-for="approval in filteredApprovals"
                            :key="approval.id"
                            class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3
                                        class="line-clamp-2 text-base leading-tight font-black break-words text-blue-950"
                                    >
                                        {{
                                            formatStudentName(
                                                approval.clearance_request.user,
                                            )
                                        }}
                                    </h3>

                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-600"
                                    >
                                        {{
                                            approval.clearance_request.user
                                                .student_id
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs font-medium text-slate-500"
                                    >
                                        Request #{{
                                            approval.clearance_request.id
                                        }}
                                    </p>
                                </div>

                                <span
                                    class="shrink-0 rounded-full border px-3 py-1 text-xs font-black"
                                    :class="statusBadgeClass(approval.status)"
                                >
                                    {{ statusLabel(approval.status) }}
                                </span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span
                                    class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700"
                                >
                                    {{
                                        approval.clearance_request.user.course
                                            ?.code ?? 'N/A'
                                    }}
                                </span>

                                <span
                                    v-if="
                                        approval.clearance_request.user
                                            .year_level
                                    "
                                    class="inline-flex items-center gap-1 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-xs font-black text-indigo-700"
                                >
                                    <GraduationCap class="size-3" />
                                    {{
                                        approval.clearance_request.user
                                            .year_level
                                    }}
                                </span>

                                <span
                                    class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-600"
                                >
                                    {{ approval.clearance_request.semester }},
                                    {{ approval.clearance_request.school_year }}
                                </span>
                            </div>

                            <div
                                class="mt-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700"
                            >
                                <span class="font-black text-slate-600">
                                    Remarks:
                                </span>
                                {{ approval.remarks ?? '-' }}
                            </div>

                            <div
                                v-if="approval.status === 'pending'"
                                class="mt-4 grid grid-cols-2 gap-2"
                            >
                                <button
                                    type="button"
                                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl bg-green-700 px-4 py-3 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:bg-green-800"
                                    @click="openApproveModal(approval)"
                                >
                                    <CheckCircle2 class="size-4" />
                                    Approve
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl bg-red-600 px-4 py-3 text-sm font-black text-white shadow-md shadow-red-600/20 transition hover:bg-red-700"
                                    @click="rejectRequest(approval.id)"
                                >
                                    <XCircle class="size-4" />
                                    Reject
                                </button>
                            </div>

                            <div
                                v-else
                                class="mt-4 flex items-center justify-between gap-2"
                            >
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black"
                                    :class="
                                        approval.status === 'approved'
                                            ? 'bg-slate-100 text-slate-500'
                                            : 'bg-red-100 text-red-700'
                                    "
                                >
                                    <CheckCircle2
                                        v-if="approval.status === 'approved'"
                                        class="size-3.5"
                                    />
                                    <XCircle v-else class="size-3.5" />
                                    {{
                                        approval.status === 'approved'
                                            ? 'Completed'
                                            : 'Rejected'
                                    }}
                                </span>

                                <button
                                    v-if="approval.status === 'rejected'"
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl bg-orange-100 px-3 py-1 text-xs font-black text-orange-700 transition hover:bg-orange-200"
                                    @click="markAsComplied(approval.id)"
                                >
                                    <CheckCircle2 class="size-3.5" />
                                    Mark as Complied
                                </button>
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
                                        Department / Course
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
                                        Status
                                    </th>

                                    <th
                                        class="px-6 py-4 text-xs font-black tracking-wide uppercase"
                                    >
                                        Remarks
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
                                    v-for="approval in filteredApprovals"
                                    :key="approval.id"
                                    class="transition hover:bg-blue-50/50"
                                >
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-black text-blue-950">
                                                {{
                                                    formatStudentName(
                                                        approval
                                                            .clearance_request
                                                            .user,
                                                    )
                                                }}
                                            </p>

                                            <p
                                                class="mt-1 text-xs font-medium text-slate-500"
                                            >
                                                Request #{{
                                                    approval.clearance_request
                                                        .id
                                                }}
                                            </p>
                                        </div>
                                    </td>

                                    <td
                                        class="px-6 py-4 font-semibold text-slate-700"
                                    >
                                        {{
                                            approval.clearance_request.user
                                                .student_id
                                        }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700"
                                        >
                                            {{
                                                approval.clearance_request.user
                                                    .course?.code ?? 'N/A'
                                            }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            v-if="
                                                approval.clearance_request.user
                                                    .year_level
                                            "
                                            class="inline-flex items-center gap-1.5 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-xs font-black text-indigo-700 shadow-xs"
                                        >
                                            <GraduationCap class="size-3" />
                                            {{
                                                approval.clearance_request.user
                                                    .year_level
                                            }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-xs font-medium text-slate-400"
                                        >
                                            Not specified
                                        </span>
                                    </td>

                                    <td
                                        class="px-6 py-4 font-semibold text-slate-700"
                                    >
                                        {{
                                            approval.clearance_request.semester
                                        }},
                                        {{
                                            approval.clearance_request
                                                .school_year
                                        }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                            :class="
                                                statusBadgeClass(
                                                    approval.status,
                                                )
                                            "
                                        >
                                            {{ statusLabel(approval.status) }}
                                        </span>
                                    </td>

                                    <td
                                        class="max-w-xs px-6 py-4 text-sm font-medium text-slate-600"
                                    >
                                        {{ approval.remarks ?? '-' }}
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div
                                            v-if="approval.status === 'pending'"
                                            class="flex justify-end gap-2"
                                        >
                                            <button
                                                type="button"
                                                class="inline-flex min-h-11 items-center gap-2 rounded-2xl bg-green-700 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:-translate-y-0.5 hover:bg-green-800 hover:shadow-lg"
                                                @click="
                                                    openApproveModal(approval)
                                                "
                                            >
                                                <CheckCircle2 class="size-4" />
                                                Approve
                                            </button>

                                            <button
                                                type="button"
                                                class="inline-flex min-h-11 items-center gap-2 rounded-2xl bg-red-600 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-red-600/20 transition hover:-translate-y-0.5 hover:bg-red-700 hover:shadow-lg"
                                                @click="
                                                    rejectRequest(approval.id)
                                                "
                                            >
                                                <XCircle class="size-4" />
                                                Reject
                                            </button>
                                        </div>

                                        <div
                                            v-else
                                            class="flex items-center justify-end gap-2"
                                        >
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black"
                                                :class="
                                                    approval.status ===
                                                    'approved'
                                                        ? 'bg-slate-100 text-slate-500'
                                                        : 'bg-red-100 text-red-700'
                                                "
                                            >
                                                <CheckCircle2
                                                    v-if="
                                                        approval.status ===
                                                        'approved'
                                                    "
                                                    class="size-3.5"
                                                />
                                                <XCircle
                                                    v-else
                                                    class="size-3.5"
                                                />
                                                {{
                                                    approval.status ===
                                                    'approved'
                                                        ? 'Completed'
                                                        : 'Rejected'
                                                }}
                                            </span>

                                            <button
                                                v-if="
                                                    approval.status ===
                                                    'rejected'
                                                "
                                                type="button"
                                                class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl bg-orange-100 px-3 py-1.5 text-xs font-black text-orange-700 transition hover:bg-orange-200"
                                                @click="
                                                    markAsComplied(approval.id)
                                                "
                                            >
                                                <CheckCircle2
                                                    class="size-3.5"
                                                />
                                                Mark as Complied
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Approve All Modal -->
    <div
        v-if="showApproveAllModal"
        class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/50 p-3 backdrop-blur-sm sm:items-center sm:p-4"
        @click.self="closeApproveAllModal"
    >
        <div
            class="flex max-h-[92dvh] w-full max-w-md flex-col overflow-hidden rounded-t-4xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20 sm:max-h-[90vh] sm:rounded-4xl"
        >
            <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6">
                <div class="flex items-start gap-4">
                    <div
                        class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-green-50 text-green-700"
                    >
                        <CheckCircle2 class="size-6" />
                    </div>

                    <div>
                        <h2 class="text-xl font-black text-blue-950">
                            Approve All Pending Requests
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            This will approve all pending clearance requests
                            currently assigned to your office.
                        </p>
                    </div>
                </div>

                <div
                    class="mt-5 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm leading-6 font-medium text-green-800"
                >
                    <span class="font-black">
                        {{ pendingApprovals.length }}
                    </span>
                    pending request(s) will be approved.
                </div>

                <div
                    class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 font-medium text-amber-900"
                >
                    Individual review and action is still available if you want
                    to check each request one by one.
                </div>
            </div>

            <div class="shrink-0 border-t border-slate-200 bg-white p-4 sm:p-6">
                <div class="grid grid-cols-2 gap-3 sm:flex sm:justify-end">
                    <button
                        type="button"
                        class="min-h-11 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="isApprovingAll"
                        @click="closeApproveAllModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="min-h-11 rounded-2xl bg-green-700 px-4 py-3 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:bg-green-800 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="isApprovingAll"
                        @click="confirmApproveAll"
                    >
                        {{ isApprovingAll ? 'Approving...' : 'Approve All' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
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
                            <h3
                                class="text-lg font-black text-blue-950 sm:text-xl"
                            >
                                Reject Clearance Request
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Provide a clear reason why this clearance
                                request is being rejected. The student will use
                                this remark as their guide for correction.
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
                <!-- Selected student preview in reject modal -->
                <div
                    v-if="selectedApprovalForReject"
                    class="mb-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700"
                >
                    <div class="grid gap-2">
                        <p>
                            <span class="font-black">Student:</span>
                            {{
                                formatStudentName(
                                    selectedApprovalForReject.clearance_request
                                        .user,
                                )
                            }}
                            <span class="text-xs font-semibold text-slate-500">
                                ({{
                                    selectedApprovalForReject.clearance_request
                                        .user.student_id
                                }})
                            </span>
                        </p>
                        <p>
                            <span class="font-black">Course & Year:</span>
                            {{
                                selectedApprovalForReject.clearance_request.user
                                    .course?.code ?? 'N/A'
                            }}
                            <span
                                v-if="
                                    selectedApprovalForReject.clearance_request
                                        .user.year_level
                                "
                                class="ml-1.5 inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-black text-indigo-700"
                            >
                                <GraduationCap class="size-3" />
                                {{
                                    selectedApprovalForReject.clearance_request
                                        .user.year_level
                                }}
                            </span>
                        </p>
                    </div>
                </div>

                <label
                    for="reject-remarks"
                    class="text-sm font-black text-slate-700"
                >
                    Rejection Remarks
                </label>

                <textarea
                    id="reject-remarks"
                    v-model="rejectRemarks"
                    rows="6"
                    class="mt-2 min-h-36 w-full rounded-2xl border border-slate-200 bg-white p-4 text-sm font-medium text-slate-900 shadow-sm outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                    placeholder="Example: Please settle your library clearance before requesting approval."
                ></textarea>

                <p class="mt-2 text-xs font-medium text-slate-500">
                    Remarks are required before submitting a rejection.
                </p>
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
                        :disabled="!rejectRemarks.trim()"
                        @click="submitRejectRequest"
                    >
                        Submit Rejection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div
        v-if="showApproveModal"
        class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/50 p-3 backdrop-blur-sm sm:items-center sm:p-4"
        @click.self="closeApproveModal"
    >
        <div
            class="flex max-h-[92dvh] w-full max-w-md flex-col overflow-hidden rounded-t-4xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20 sm:max-h-[90vh] sm:rounded-4xl"
        >
            <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6">
                <div class="flex items-start gap-4">
                    <div
                        class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-green-50 text-green-700"
                    >
                        <CheckCircle2 class="size-6" />
                    </div>

                    <div>
                        <h2 class="text-xl font-black text-blue-950">
                            Confirm Clearance Approval
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Are you sure you want to approve this clearance
                            request? This will mark the student as approved for
                            your assigned office.
                        </p>
                    </div>
                </div>

                <div
                    v-if="selectedApprovalForApproval"
                    class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700"
                >
                    <div class="grid gap-3">
                        <p>
                            <span class="font-black">Student:</span>
                            {{
                                formatStudentName(
                                    selectedApprovalForApproval
                                        .clearance_request.user,
                                )
                            }}
                        </p>

                        <p>
                            <span class="font-black">Student ID:</span>
                            {{
                                selectedApprovalForApproval.clearance_request
                                    .user.student_id
                            }}
                        </p>

                        <p>
                            <span class="font-black">Course:</span>
                            {{
                                selectedApprovalForApproval.clearance_request
                                    .user.course?.code ?? 'N/A'
                            }}
                        </p>

                        <p>
                            <span class="font-black">Year Level:</span>
                            <span
                                class="ml-1 inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-black text-indigo-700"
                            >
                                <GraduationCap class="size-3" />
                                {{
                                    selectedApprovalForApproval
                                        .clearance_request.user.year_level ??
                                    'Not specified'
                                }}
                            </span>
                        </p>

                        <p>
                            <span class="font-black">Semester:</span>
                            {{
                                selectedApprovalForApproval.clearance_request
                                    .semester
                            }}
                        </p>

                        <p>
                            <span class="font-black">School Year:</span>
                            {{
                                selectedApprovalForApproval.clearance_request
                                    .school_year
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="shrink-0 border-t border-slate-200 bg-white p-4 sm:p-6">
                <div class="grid grid-cols-2 gap-3 sm:flex sm:justify-end">
                    <button
                        type="button"
                        class="min-h-11 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="closeApproveModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="min-h-11 rounded-2xl bg-green-700 px-4 py-3 text-sm font-black text-white shadow-md shadow-green-700/20 transition hover:bg-green-800"
                        @click="confirmApprove"
                    >
                        Confirm Approval
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
