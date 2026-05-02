<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CheckCircle2,
    Crown,
    GraduationCap,
    LayoutDashboard,
    Pencil,
    Plus,
    Power,
    RotateCcw,
    Search,
    ShieldCheck,
    UserCheck,
    UserRoundCog,
    Users,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Course = {
    id: number;
    code: string;
    name: string;
};

type Office = {
    id: number;
    name: string;
    group: string;
};

type UserRole = 'student' | 'staff' | 'admin' | 'president';
type RoleFilter = 'all' | UserRole;

type User = {
    id: number;
    name: string;
    student_id: string;
    role: UserRole;
    is_active: boolean;
    deactivated_at: string | null;
    course: Course | null;
    office: Office | null;
};

const props = defineProps<{
    users: User[];
    offices: Office[];
    courses: Course[];
}>();

const showCreateStaffModal = ref(false);
const showEditUserModal = ref(false);
const showStatusModal = ref(false);
const selectedUser = ref<User | null>(null);
const selectedStatusUser = ref<User | null>(null);
const successMessage = ref('');
const errorMessage = ref('');
const userSearchQuery = ref('');
const activeRoleFilter = ref<RoleFilter>('all');

const form = useForm({
    name: '',
    student_id: '',
    office_id: '',
    password: '',
    password_confirmation: '',
});

const editForm = useForm<{
    name: string;
    role: UserRole;
    course_id: string | number;
    office_id: string | number;
    password: string;
    password_confirmation: string;
}>({
    name: '',
    role: 'student',
    course_id: '',
    office_id: '',
    password: '',
    password_confirmation: '',
});

const roleLabel = (role: string) => {
    if (role === 'student') {
        return 'Student';
    }

    if (role === 'staff') {
        return 'Staff / Approver';
    }

    if (role === 'admin') {
        return 'Admin / OSAS';
    }

    if (role === 'president') {
        return 'President';
    }

    return role;
};

const filteredUsers = computed(() => {
    return props.users.filter((user) => {
        const searchValue = userSearchQuery.value.toLowerCase().trim();

        const courseCode = user.course?.code ?? '';
        const courseName = user.course?.name ?? '';
        const officeName = user.office?.name ?? '';
        const activeStatus = user.is_active ? 'active' : 'inactive';

        const matchesRole =
            activeRoleFilter.value === 'all' ||
            user.role === activeRoleFilter.value;

        const matchesSearch =
            searchValue === '' ||
            user.name.toLowerCase().includes(searchValue) ||
            user.student_id.toLowerCase().includes(searchValue) ||
            roleLabel(user.role).toLowerCase().includes(searchValue) ||
            courseCode.toLowerCase().includes(searchValue) ||
            courseName.toLowerCase().includes(searchValue) ||
            officeName.toLowerCase().includes(searchValue) ||
            activeStatus.includes(searchValue);

        return matchesRole && matchesSearch;
    });
});

const studentCount = computed(() => {
    return props.users.filter((user) => user.role === 'student').length;
});

const staffCount = computed(() => {
    return props.users.filter((user) => user.role === 'staff').length;
});

const adminCount = computed(() => {
    return props.users.filter((user) => user.role === 'admin').length;
});

const presidentCount = computed(() => {
    return props.users.filter((user) => user.role === 'president').length;
});

const setRoleFilter = (role: RoleFilter) => {
    activeRoleFilter.value = role;
};

const clearUserFilters = () => {
    activeRoleFilter.value = 'all';
    userSearchQuery.value = '';
};

const openCreateStaffModal = () => {
    successMessage.value = '';
    errorMessage.value = '';
    form.clearErrors();
    form.reset();

    showCreateStaffModal.value = true;
};

const closeCreateStaffModal = () => {
    showCreateStaffModal.value = false;
    form.clearErrors();
    form.reset();
};

const submitCreateStaff = () => {
    successMessage.value = '';
    errorMessage.value = '';

    form.post('/admin/users', {
        preserveScroll: true,
        onSuccess: () => {
            closeCreateStaffModal();
            successMessage.value = 'Staff account created successfully.';

            router.reload({
                only: ['users'],
            });
        },
        onError: () => {
            errorMessage.value =
                'Unable to create staff account. Please check the form.';
        },
    });
};

const openEditUserModal = (user: User) => {
    successMessage.value = '';
    errorMessage.value = '';
    selectedUser.value = user;

    editForm.clearErrors();
    editForm.name = user.name;
    editForm.role = user.role;
    editForm.course_id = user.course ? user.course.id : '';
    editForm.office_id = user.office ? user.office.id : '';
    editForm.password = '';
    editForm.password_confirmation = '';

    showEditUserModal.value = true;
};

const closeEditUserModal = () => {
    showEditUserModal.value = false;
    selectedUser.value = null;
    editForm.clearErrors();
    editForm.reset();
};

const submitEditUser = () => {
    if (!selectedUser.value) {
        return;
    }

    successMessage.value = '';
    errorMessage.value = '';

    editForm.patch(`/admin/users/${selectedUser.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeEditUserModal();
            successMessage.value = 'User account updated successfully.';

            router.reload({
                only: ['users'],
            });
        },
        onError: () => {
            errorMessage.value =
                'Unable to update user account. Please check the form.';
        },
    });
};

const openStatusModal = (user: User) => {
    successMessage.value = '';
    errorMessage.value = '';
    selectedStatusUser.value = user;
    showStatusModal.value = true;
};

const closeStatusModal = () => {
    showStatusModal.value = false;
    selectedStatusUser.value = null;
};

const submitToggleUserActive = () => {
    if (!selectedStatusUser.value) {
        return;
    }

    const user = selectedStatusUser.value;

    successMessage.value = '';
    errorMessage.value = '';

    router.patch(
        `/admin/users/${user.id}/toggle-active`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                closeStatusModal();

                successMessage.value = user.is_active
                    ? 'User account deactivated successfully.'
                    : 'User account activated successfully.';

                router.reload({
                    only: ['users'],
                });
            },
            onError: () => {
                errorMessage.value =
                    'Unable to update user account status. Please try again.';
            },
        },
    );
};

const roleBadgeClass = (role: string) => {
    if (role === 'student') {
        return 'border-blue-200 bg-blue-50 text-blue-700';
    }

    if (role === 'staff') {
        return 'border-green-200 bg-green-50 text-green-700';
    }

    if (role === 'admin') {
        return 'border-purple-200 bg-purple-50 text-purple-700';
    }

    if (role === 'president') {
        return 'border-orange-200 bg-orange-50 text-orange-700';
    }

    return 'border-slate-200 bg-slate-50 text-slate-700';
};

const activeStatusLabel = (user: User) => {
    if (user.is_active) {
        return 'Active';
    }

    return 'Inactive';
};

const activeStatusBadgeClass = (user: User) => {
    if (user.is_active) {
        return 'border-green-200 bg-green-50 text-green-700';
    }

    return 'border-red-200 bg-red-50 text-red-700';
};

const roleFilterButtonClass = (role: RoleFilter) => {
    if (activeRoleFilter.value === role) {
        return 'border-blue-700 bg-blue-700 text-white shadow-lg shadow-blue-700/20';
    }

    return 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700';
};
</script>

<template>
    <Head title="User Management" />

    <div
        class="min-h-screen bg-linear-to-br from-slate-50 via-white to-blue-50/40 p-4 text-slate-900 md:p-6"
    >
        <div class="mx-auto flex max-w-7xl flex-col gap-6">
            <!-- Hero -->
            <section
                class="overflow-hidden rounded-4xl border border-slate-200 bg-white/95 shadow-xl shadow-slate-200/70"
            >
                <div class="grid gap-8 p-6 lg:grid-cols-[1fr_300px] lg:p-8">
                    <div>
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-blue-700"
                        >
                            <ShieldCheck class="size-4" />
                            Admin / OSAS Director Panel
                        </div>

                        <div
                            class="mt-5 flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between"
                        >
                            <div>
                                <h1
                                    class="text-4xl font-black tracking-tight text-blue-950"
                                >
                                    User Management
                                </h1>

                                <p
                                    class="mt-3 max-w-3xl text-base leading-7 text-slate-600"
                                >
                                    View, create, update, activate, and
                                    deactivate student, staff, admin, and
                                    president accounts without deleting user
                                    history.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-2xl bg-blue-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-700/20 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-xl"
                                    @click="openCreateStaffModal"
                                >
                                    <Plus class="size-4" />
                                    Create Staff Account
                                </button>

                                <Link
                                    href="/admin/dashboard"
                                    class="inline-flex items-center gap-2 whitespace-nowrap rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 hover:shadow-md"
                                >
                                    <LayoutDashboard class="size-4" />
                                    Back to Dashboard
                                </Link>
                            </div>
                        </div>

                        <div
                            class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/80 p-4 text-sm font-medium leading-6 text-blue-900"
                        >
                            Admin can create office staff accounts, update role
                            assignments, assign courses or offices, reset user
                            passwords, and deactivate accounts while preserving
                            clearance history.
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
                                <UserRoundCog class="size-10" />
                            </div>

                            <p
                                class="text-center text-sm font-black uppercase tracking-[0.18em] text-blue-700"
                            >
                                Account Center
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Summary Cards -->
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div
                    class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="flex items-center gap-4 xl:block">
                        <div
                            class="grid h-14 w-14 place-items-center rounded-2xl bg-blue-50 text-blue-700 shadow-sm xl:mb-4"
                        >
                            <Users class="size-7" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-black uppercase tracking-wide text-blue-700"
                            >
                                Total Users
                            </p>

                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ users.length }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="flex items-center gap-4 xl:block">
                        <div
                            class="grid h-14 w-14 place-items-center rounded-2xl bg-blue-50 text-blue-700 shadow-sm xl:mb-4"
                        >
                            <GraduationCap class="size-7" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-black uppercase tracking-wide text-blue-700"
                            >
                                Students
                            </p>

                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ studentCount }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="flex items-center gap-4 xl:block">
                        <div
                            class="grid h-14 w-14 place-items-center rounded-2xl bg-green-50 text-green-700 shadow-sm xl:mb-4"
                        >
                            <UserCheck class="size-7" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-black uppercase tracking-wide text-green-700"
                            >
                                Staff
                            </p>

                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ staffCount }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="flex items-center gap-4 xl:block">
                        <div
                            class="grid h-14 w-14 place-items-center rounded-2xl bg-purple-50 text-purple-700 shadow-sm xl:mb-4"
                        >
                            <ShieldCheck class="size-7" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-black uppercase tracking-wide text-purple-700"
                            >
                                Admins
                            </p>

                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ adminCount }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-1 hover:shadow-xl md:col-span-2 xl:col-span-1"
                >
                    <div class="flex items-center gap-4 xl:block">
                        <div
                            class="grid h-14 w-14 place-items-center rounded-2xl bg-orange-50 text-orange-700 shadow-sm xl:mb-4"
                        >
                            <Crown class="size-7" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-black uppercase tracking-wide text-orange-700"
                            >
                                Presidents
                            </p>

                            <p class="mt-1 text-4xl font-black text-blue-950">
                                {{ presidentCount }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Users Table -->
            <section
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-sm shadow-slate-200/70"
            >
                <div
                    class="flex flex-col gap-5 border-b border-slate-200 bg-white px-6 py-5 xl:flex-row xl:items-start xl:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-black uppercase tracking-[0.18em] text-slate-400"
                        >
                            Account Registry
                        </p>

                        <h2 class="mt-1 text-xl font-black text-blue-950">
                            System Users
                        </h2>

                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Showing {{ filteredUsers.length }} of
                            {{ users.length }} user accounts.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 xl:items-end">
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="inline-flex cursor-pointer items-center rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                                :class="roleFilterButtonClass('all')"
                                @click="setRoleFilter('all')"
                            >
                                All
                            </button>

                            <button
                                type="button"
                                class="inline-flex cursor-pointer items-center rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                                :class="roleFilterButtonClass('student')"
                                @click="setRoleFilter('student')"
                            >
                                Students
                            </button>

                            <button
                                type="button"
                                class="inline-flex cursor-pointer items-center rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                                :class="roleFilterButtonClass('staff')"
                                @click="setRoleFilter('staff')"
                            >
                                Staff
                            </button>

                            <button
                                type="button"
                                class="inline-flex cursor-pointer items-center rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                                :class="roleFilterButtonClass('admin')"
                                @click="setRoleFilter('admin')"
                            >
                                Admin
                            </button>

                            <button
                                type="button"
                                class="inline-flex cursor-pointer items-center rounded-2xl border px-4 py-2.5 text-sm font-black transition"
                                :class="roleFilterButtonClass('president')"
                                @click="setRoleFilter('president')"
                            >
                                President
                            </button>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row">
                            <div class="relative">
                                <Search
                                    class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="userSearchQuery"
                                    type="text"
                                    class="w-full rounded-2xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm font-medium text-slate-900 shadow-sm outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 sm:w-80"
                                    placeholder="Search name, ID, role, course, office, or status"
                                />
                            </div>

                            <button
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                @click="clearUserFilters"
                            >
                                <RotateCcw class="size-4" />
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="filteredUsers.length === 0" class="p-12 text-center">
                    <div
                        class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-blue-50 text-blue-700"
                    >
                        <Users class="size-8" />
                    </div>

                    <p class="mt-4 font-black text-slate-700">
                        No users found.
                    </p>

                    <p class="mt-1 text-sm font-medium text-slate-500">
                        Try changing the search text or role filter.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                >
                                    Name
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                >
                                    Student/System ID
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                >
                                    Role
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                >
                                    Course
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-black uppercase tracking-wide"
                                >
                                    Office
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-black uppercase tracking-wide"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="user in filteredUsers"
                                :key="user.id"
                                class="transition hover:bg-blue-50/50"
                                :class="!user.is_active ? 'bg-red-50/40' : ''"
                            >
                                <td class="px-6 py-4">
                                    <p class="font-black text-blue-950">
                                        {{ user.name }}
                                    </p>
                                </td>

                                <td
                                    class="px-6 py-4 font-semibold text-slate-700"
                                >
                                    {{ user.student_id }}
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full border px-3 py-1 text-xs font-black"
                                        :class="roleBadgeClass(user.role)"
                                    >
                                        {{ roleLabel(user.role) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full border px-3 py-1 text-xs font-black"
                                        :class="activeStatusBadgeClass(user)"
                                    >
                                        {{ activeStatusLabel(user) }}
                                    </span>
                                </td>

                                <td
                                    class="px-6 py-4 font-semibold text-slate-700"
                                >
                                    {{ user.course ? user.course.code : 'N/A' }}
                                </td>

                                <td
                                    class="px-6 py-4 font-semibold text-slate-700"
                                >
                                    {{ user.office ? user.office.name : 'N/A' }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex flex-wrap justify-end gap-2"
                                    >
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-2xl bg-blue-700 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-blue-700/20 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-lg"
                                            @click="openEditUserModal(user)"
                                        >
                                            <Pencil class="size-4" />
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-black text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                                            :class="
                                                user.is_active
                                                    ? 'bg-red-600 shadow-red-600/20 hover:bg-red-700'
                                                    : 'bg-green-700 shadow-green-700/20 hover:bg-green-800'
                                            "
                                            @click="openStatusModal(user)"
                                        >
                                            <Power class="size-4" />
                                            {{
                                                user.is_active
                                                    ? 'Deactivate'
                                                    : 'Activate'
                                            }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <!-- Create Staff Modal -->
    <div
        v-if="showCreateStaffModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
        @click.self="closeCreateStaffModal"
    >
        <div
            class="w-full max-w-2xl rounded-4xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-900/20"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div
                        class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-blue-50 text-blue-700"
                    >
                        <Plus class="size-6" />
                    </div>

                    <div>
                        <h2 class="text-xl font-black text-blue-950">
                            Create Staff Account
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Add a new office staff account and assign it to one
                            clearance office.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="grid size-10 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                    @click="closeCreateStaffModal"
                >
                    <X class="size-5" />
                </button>
            </div>

            <form
                class="mt-6 flex flex-col gap-5"
                @submit.prevent="submitCreateStaff"
            >
                <div>
                    <label
                        for="name"
                        class="text-sm font-black text-slate-700"
                    >
                        Full Name
                    </label>

                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                        placeholder="Example: Library Staff"
                    />

                    <p
                        v-if="form.errors.name"
                        class="mt-2 text-sm font-medium text-red-600"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label
                        for="student_id"
                        class="text-sm font-black text-slate-700"
                    >
                        Staff/System ID
                    </label>

                    <input
                        id="student_id"
                        v-model="form.student_id"
                        type="text"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                        placeholder="Example: LIB002"
                    />

                    <p
                        v-if="form.errors.student_id"
                        class="mt-2 text-sm font-medium text-red-600"
                    >
                        {{ form.errors.student_id }}
                    </p>
                </div>

                <div>
                    <label
                        for="office_id"
                        class="text-sm font-black text-slate-700"
                    >
                        Assigned Office
                    </label>

                    <select
                        id="office_id"
                        v-model="form.office_id"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                    >
                        <option value="">Select an office</option>

                        <option
                            v-for="office in offices"
                            :key="office.id"
                            :value="office.id"
                        >
                            {{ office.name }}
                        </option>
                    </select>

                    <p
                        v-if="form.errors.office_id"
                        class="mt-2 text-sm font-medium text-red-600"
                    >
                        {{ form.errors.office_id }}
                    </p>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label
                            for="password"
                            class="text-sm font-black text-slate-700"
                        >
                            Password
                        </label>

                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                            placeholder="Minimum 8 characters"
                        />

                        <p
                            v-if="form.errors.password"
                            class="mt-2 text-sm font-medium text-red-600"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="password_confirmation"
                            class="text-sm font-black text-slate-700"
                        >
                            Confirm Password
                        </label>

                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                            placeholder="Re-enter password"
                        />
                    </div>
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-slate-200 pt-5"
                >
                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="closeCreateStaffModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-2xl bg-blue-700 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-blue-700/20 transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Create Staff Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div
        v-if="showEditUserModal && selectedUser"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
        @click.self="closeEditUserModal"
    >
        <div
            class="max-h-[90vh] w-full max-w-2xl overflow-hidden rounded-4xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20"
        >
            <div
                class="flex items-start justify-between gap-4 border-b border-slate-200 p-6"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-blue-50 text-blue-700"
                    >
                        <Pencil class="size-6" />
                    </div>

                    <div>
                        <h2 class="text-xl font-black text-blue-950">
                            Edit User Account
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Update the selected user's name, role, course,
                            assigned office, or password.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="grid size-10 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                    @click="closeEditUserModal"
                >
                    <X class="size-5" />
                </button>
            </div>

            <div class="max-h-[75vh] overflow-y-auto p-6">
                <div
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700"
                >
                    <p>
                        <span class="font-black">System ID:</span>
                        {{ selectedUser.student_id }}
                    </p>

                    <p class="mt-1">
                        <span class="font-black">Account Status:</span>
                        {{ activeStatusLabel(selectedUser) }}
                    </p>
                </div>

                <form
                    class="mt-6 flex flex-col gap-5"
                    @submit.prevent="submitEditUser"
                >
                    <div>
                        <label
                            for="edit_name"
                            class="text-sm font-black text-slate-700"
                        >
                            Full Name
                        </label>

                        <input
                            id="edit_name"
                            v-model="editForm.name"
                            type="text"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                        />

                        <p
                            v-if="editForm.errors.name"
                            class="mt-2 text-sm font-medium text-red-600"
                        >
                            {{ editForm.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="edit_role"
                            class="text-sm font-black text-slate-700"
                        >
                            Role
                        </label>

                        <select
                            id="edit_role"
                            v-model="editForm.role"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                        >
                            <option value="student">Student</option>
                            <option value="staff">Staff / Approver</option>
                            <option value="admin">Admin / OSAS</option>
                            <option value="president">President</option>
                        </select>

                        <p
                            v-if="editForm.errors.role"
                            class="mt-2 text-sm font-medium text-red-600"
                        >
                            {{ editForm.errors.role }}
                        </p>
                    </div>

                    <div v-if="editForm.role === 'student'">
                        <label
                            for="edit_course_id"
                            class="text-sm font-black text-slate-700"
                        >
                            Course
                        </label>

                        <select
                            id="edit_course_id"
                            v-model="editForm.course_id"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                        >
                            <option value="">Select a course</option>

                            <option
                                v-for="course in courses"
                                :key="course.id"
                                :value="course.id"
                            >
                                {{ course.code }} - {{ course.name }}
                            </option>
                        </select>

                        <p
                            v-if="editForm.errors.course_id"
                            class="mt-2 text-sm font-medium text-red-600"
                        >
                            {{ editForm.errors.course_id }}
                        </p>
                    </div>

                    <div v-else>
                        <label
                            for="edit_office_id"
                            class="text-sm font-black text-slate-700"
                        >
                            Assigned Office
                        </label>

                        <select
                            id="edit_office_id"
                            v-model="editForm.office_id"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                        >
                            <option value="">Select an office</option>

                            <option
                                v-for="office in offices"
                                :key="office.id"
                                :value="office.id"
                            >
                                {{ office.name }}
                            </option>
                        </select>

                        <p
                            v-if="editForm.errors.office_id"
                            class="mt-2 text-sm font-medium text-red-600"
                        >
                            {{ editForm.errors.office_id }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm font-medium leading-6 text-blue-800"
                    >
                        Leave the password fields blank if you do not want to
                        change this user's password.
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label
                                for="edit_password"
                                class="text-sm font-black text-slate-700"
                            >
                                New Password
                            </label>

                            <input
                                id="edit_password"
                                v-model="editForm.password"
                                type="password"
                                class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                                placeholder="Leave blank to keep current password"
                            />

                            <p
                                v-if="editForm.errors.password"
                                class="mt-2 text-sm font-medium text-red-600"
                            >
                                {{ editForm.errors.password }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="edit_password_confirmation"
                                class="text-sm font-black text-slate-700"
                            >
                                Confirm New Password
                            </label>

                            <input
                                id="edit_password_confirmation"
                                v-model="editForm.password_confirmation"
                                type="password"
                                class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                                placeholder="Re-enter new password"
                            />
                        </div>
                    </div>

                    <div
                        class="flex justify-end gap-3 border-t border-slate-200 pt-5"
                    >
                        <button
                            type="button"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                            @click="closeEditUserModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="rounded-2xl bg-blue-700 px-4 py-2.5 text-sm font-black text-white shadow-md shadow-blue-700/20 transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Activate / Deactivate Modal -->
    <div
        v-if="showStatusModal && selectedStatusUser"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
        @click.self="closeStatusModal"
    >
        <div
            class="w-full max-w-lg rounded-4xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-900/20"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div
                        class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl"
                        :class="
                            selectedStatusUser.is_active
                                ? 'bg-red-50 text-red-600'
                                : 'bg-green-50 text-green-700'
                        "
                    >
                        <AlertTriangle
                            v-if="selectedStatusUser.is_active"
                            class="size-6"
                        />

                        <CheckCircle2 v-else class="size-6" />
                    </div>

                    <div>
                        <h2 class="text-xl font-black text-blue-950">
                            {{
                                selectedStatusUser.is_active
                                    ? 'Deactivate User Account'
                                    : 'Activate User Account'
                            }}
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{
                                selectedStatusUser.is_active
                                    ? 'This user will no longer be able to access the system, but their records will remain saved.'
                                    : 'This user will be able to access the system again.'
                            }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="grid size-10 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                    @click="closeStatusModal"
                >
                    <X class="size-5" />
                </button>
            </div>

            <div
                class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700"
            >
                <p>
                    <span class="font-black">Name:</span>
                    {{ selectedStatusUser.name }}
                </p>

                <p class="mt-1">
                    <span class="font-black">System ID:</span>
                    {{ selectedStatusUser.student_id }}
                </p>

                <p class="mt-1">
                    <span class="font-black">Current Status:</span>
                    {{ activeStatusLabel(selectedStatusUser) }}
                </p>
            </div>

            <div
                class="mt-5 rounded-2xl border p-4 text-sm font-medium leading-6"
                :class="
                    selectedStatusUser.is_active
                        ? 'border-red-200 bg-red-50 text-red-800'
                        : 'border-green-200 bg-green-50 text-green-800'
                "
            >
                {{
                    selectedStatusUser.is_active
                        ? 'Are you sure you want to deactivate this account?'
                        : 'Are you sure you want to activate this account?'
                }}
            </div>

            <div
                class="mt-6 flex justify-end gap-3 border-t border-slate-200 pt-5"
            >
                <button
                    type="button"
                    class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    @click="closeStatusModal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="rounded-2xl px-4 py-2.5 text-sm font-black text-white shadow-md transition"
                    :class="
                        selectedStatusUser.is_active
                            ? 'bg-red-600 shadow-red-600/20 hover:bg-red-700'
                            : 'bg-green-700 shadow-green-700/20 hover:bg-green-800'
                    "
                    @click="submitToggleUserActive"
                >
                    {{
                        selectedStatusUser.is_active
                            ? 'Deactivate Account'
                            : 'Activate Account'
                    }}
                </button>
            </div>
        </div>
    </div>
</template>