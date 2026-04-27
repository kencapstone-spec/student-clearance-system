<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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

const roleLabel = (role: string) => {
    if (role === 'student') return 'Student';
    if (role === 'staff') return 'Staff / Approver';
    if (role === 'admin') return 'Admin / OSAS';
    if (role === 'president') return 'President';

    return role;
};

const roleBadgeClass = (role: string) => {
    if (role === 'student') {
        return 'bg-blue-100 text-blue-700';
    }

    if (role === 'staff') {
        return 'bg-green-100 text-green-700';
    }

    if (role === 'admin') {
        return 'bg-purple-100 text-purple-700';
    }

    if (role === 'president') {
        return 'bg-orange-100 text-orange-700';
    }

    return 'bg-slate-100 text-slate-700';
};

const activeStatusLabel = (user: User) => {
    if (user.is_active) {
        return 'Active';
    }

    return 'Inactive';
};

const activeStatusBadgeClass = (user: User) => {
    if (user.is_active) {
        return 'bg-green-100 text-green-700';
    }

    return 'bg-red-100 text-red-700';
};

const roleFilterButtonClass = (role: RoleFilter) => {
    if (activeRoleFilter.value === role) {
        return 'bg-blue-600 text-white';
    }

    return 'bg-white text-slate-700 hover:bg-slate-100';
};
</script>

<template>
    <Head title="User Management" />

    <div class="min-h-screen bg-slate-50 p-4 text-slate-900">
        <div class="mx-auto flex max-w-7xl flex-col gap-6">
            <section class="rounded-2xl border bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-blue-700">
                    Admin / OSAS Director Panel
                </p>

                <div
                    class="mt-2 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <h1 class="text-3xl font-bold text-blue-950">
                            User Management
                        </h1>

                        <p class="mt-2 text-slate-600">
                            View, create, update, activate, and deactivate
                            student, staff, admin, and president accounts in the
                            system.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button
                            type="button"
                            class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                            @click="openCreateStaffModal"
                        >
                            Create Staff Account
                        </button>

                        <Link
                            href="/admin/dashboard"
                            class="rounded-xl bg-slate-600 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
                        >
                            Back to Dashboard
                        </Link>
                    </div>
                </div>

                <div
                    class="mt-4 rounded-xl bg-blue-50 p-4 text-sm text-blue-900"
                >
                    Admin can view all accounts, create office staff accounts,
                    update user role assignments, courses, offices, reset user
                    passwords, and deactivate accounts without deleting history.
                </div>

                <div
                    v-if="successMessage"
                    class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700"
                >
                    {{ successMessage }}
                </div>

                <div
                    v-if="errorMessage"
                    class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"
                >
                    {{ errorMessage }}
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-5">
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-blue-700">Total Users</p>

                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ users.length }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-blue-700">Students</p>

                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ studentCount }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-green-700">Staff</p>

                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ staffCount }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-purple-700">Admins</p>

                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ adminCount }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-orange-700">Presidents</p>

                    <p class="mt-2 text-4xl font-bold text-blue-950">
                        {{ presidentCount }}
                    </p>
                </div>
            </section>

            <section class="rounded-2xl border bg-white shadow-sm">
                <div class="border-b p-6">
                    <div
                        class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <h2 class="text-xl font-bold text-blue-950">
                                System Users
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Showing {{ filteredUsers.length }} of
                                {{ users.length }} user accounts.
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 md:items-end">
                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="cursor-pointer rounded-xl px-4 py-2 text-sm font-semibold transition"
                                    :class="roleFilterButtonClass('all')"
                                    @click="setRoleFilter('all')"
                                >
                                    All
                                </button>

                                <button
                                    type="button"
                                    class="cursor-pointer rounded-xl px-4 py-2 text-sm font-semibold transition"
                                    :class="roleFilterButtonClass('student')"
                                    @click="setRoleFilter('student')"
                                >
                                    Students
                                </button>

                                <button
                                    type="button"
                                    class="cursor-pointer rounded-xl px-4 py-2 text-sm font-semibold transition"
                                    :class="roleFilterButtonClass('staff')"
                                    @click="setRoleFilter('staff')"
                                >
                                    Staff
                                </button>

                                <button
                                    type="button"
                                    class="cursor-pointer rounded-xl px-4 py-2 text-sm font-semibold transition"
                                    :class="roleFilterButtonClass('admin')"
                                    @click="setRoleFilter('admin')"
                                >
                                    Admin
                                </button>

                                <button
                                    type="button"
                                    class="cursor-pointer rounded-xl px-4 py-2 text-sm font-semibold transition"
                                    :class="
                                        roleFilterButtonClass('president')
                                    "
                                    @click="setRoleFilter('president')"
                                >
                                    President
                                </button>
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row">
                                <input
                                    v-model="userSearchQuery"
                                    type="text"
                                    class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    placeholder="Search name, ID, role, course, office, or status"
                                />

                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                                    @click="clearUserFilters"
                                >
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="filteredUsers.length === 0" class="p-8 text-center">
                    <p class="font-medium text-slate-700">No users found.</p>

                    <p class="mt-1 text-sm text-slate-500">
                        Try changing the search text or role filter.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100 text-slate-600">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Name</th>

                                <th class="px-6 py-3 font-semibold">
                                    Student/System ID
                                </th>

                                <th class="px-6 py-3 font-semibold">Role</th>

                                <th class="px-6 py-3 font-semibold">Status</th>

                                <th class="px-6 py-3 font-semibold">Course</th>

                                <th class="px-6 py-3 font-semibold">Office</th>

                                <th class="px-6 py-3 text-right font-semibold">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="user in filteredUsers"
                                :key="user.id"
                                class="border-t"
                                :class="!user.is_active ? 'bg-red-50/40' : ''"
                            >
                                <td class="px-6 py-4 font-medium text-blue-950">
                                    {{ user.name }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ user.student_id }}
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="roleBadgeClass(user.role)"
                                    >
                                        {{ roleLabel(user.role) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="activeStatusBadgeClass(user)"
                                    >
                                        {{ activeStatusLabel(user) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    {{ user.course ? user.course.code : 'N/A' }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ user.office ? user.office.name : 'N/A' }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex flex-wrap justify-end gap-2"
                                    >
                                        <button
                                            type="button"
                                            class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                                            @click="openEditUserModal(user)"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-lg px-3 py-2 text-sm font-semibold text-white"
                                            :class="
                                                user.is_active
                                                    ? 'bg-red-600 hover:bg-red-700'
                                                    : 'bg-green-600 hover:bg-green-700'
                                            "
                                            @click="openStatusModal(user)"
                                        >
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

    <div
        v-if="showCreateStaffModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @click.self="closeCreateStaffModal"
    >
        <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-blue-950">
                        Create Staff Account
                    </h2>

                    <p class="mt-2 text-sm text-slate-600">
                        Add a new office staff account and assign it to one
                        clearance office.
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-lg px-3 py-1 text-xl font-bold text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                    @click="closeCreateStaffModal"
                >
                    ×
                </button>
            </div>

            <form
                class="mt-6 flex flex-col gap-5"
                @submit.prevent="submitCreateStaff"
            >
                <div>
                    <label
                        for="name"
                        class="text-sm font-semibold text-slate-700"
                    >
                        Full Name
                    </label>

                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        placeholder="Example: Library Staff"
                    />

                    <p
                        v-if="form.errors.name"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label
                        for="student_id"
                        class="text-sm font-semibold text-slate-700"
                    >
                        Staff/System ID
                    </label>

                    <input
                        id="student_id"
                        v-model="form.student_id"
                        type="text"
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        placeholder="Example: LIB002"
                    />

                    <p
                        v-if="form.errors.student_id"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.student_id }}
                    </p>
                </div>

                <div>
                    <label
                        for="office_id"
                        class="text-sm font-semibold text-slate-700"
                    >
                        Assigned Office
                    </label>

                    <select
                        id="office_id"
                        v-model="form.office_id"
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
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
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.office_id }}
                    </p>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label
                            for="password"
                            class="text-sm font-semibold text-slate-700"
                        >
                            Password
                        </label>

                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            placeholder="Minimum 8 characters"
                        />

                        <p
                            v-if="form.errors.password"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="password_confirmation"
                            class="text-sm font-semibold text-slate-700"
                        >
                            Confirm Password
                        </label>

                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            placeholder="Re-enter password"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t pt-5">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                        @click="closeCreateStaffModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Create Staff Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        v-if="showEditUserModal && selectedUser"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @click.self="closeEditUserModal"
    >
        <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-blue-950">
                        Edit User Account
                    </h2>

                    <p class="mt-2 text-sm text-slate-600">
                        Update the selected user's name, role, course, assigned
                        office, or password.
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-lg px-3 py-1 text-xl font-bold text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                    @click="closeEditUserModal"
                >
                    ×
                </button>
            </div>

            <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-700">
                <p>
                    <span class="font-semibold">System ID:</span>
                    {{ selectedUser.student_id }}
                </p>

                <p class="mt-1">
                    <span class="font-semibold">Account Status:</span>
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
                        class="text-sm font-semibold text-slate-700"
                    >
                        Full Name
                    </label>

                    <input
                        id="edit_name"
                        v-model="editForm.name"
                        type="text"
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    />

                    <p
                        v-if="editForm.errors.name"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ editForm.errors.name }}
                    </p>
                </div>

                <div>
                    <label
                        for="edit_role"
                        class="text-sm font-semibold text-slate-700"
                    >
                        Role
                    </label>

                    <select
                        id="edit_role"
                        v-model="editForm.role"
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="student">Student</option>
                        <option value="staff">Staff / Approver</option>
                        <option value="admin">Admin / OSAS</option>
                        <option value="president">President</option>
                    </select>

                    <p
                        v-if="editForm.errors.role"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ editForm.errors.role }}
                    </p>
                </div>

                <div v-if="editForm.role === 'student'">
                    <label
                        for="edit_course_id"
                        class="text-sm font-semibold text-slate-700"
                    >
                        Course
                    </label>

                    <select
                        id="edit_course_id"
                        v-model="editForm.course_id"
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
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
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ editForm.errors.course_id }}
                    </p>
                </div>

                <div v-else>
                    <label
                        for="edit_office_id"
                        class="text-sm font-semibold text-slate-700"
                    >
                        Assigned Office
                    </label>

                    <select
                        id="edit_office_id"
                        v-model="editForm.office_id"
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
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
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ editForm.errors.office_id }}
                    </p>
                </div>

                <div class="rounded-xl bg-blue-50 p-4 text-sm text-blue-800">
                    Leave the password fields blank if you do not want to change
                    this user's password.
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label
                            for="edit_password"
                            class="text-sm font-semibold text-slate-700"
                        >
                            New Password
                        </label>

                        <input
                            id="edit_password"
                            v-model="editForm.password"
                            type="password"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            placeholder="Leave blank to keep current password"
                        />

                        <p
                            v-if="editForm.errors.password"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ editForm.errors.password }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="edit_password_confirmation"
                            class="text-sm font-semibold text-slate-700"
                        >
                            Confirm New Password
                        </label>

                        <input
                            id="edit_password_confirmation"
                            v-model="editForm.password_confirmation"
                            type="password"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            placeholder="Re-enter new password"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t pt-5">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                        @click="closeEditUserModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        :disabled="editForm.processing"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        v-if="showStatusModal && selectedStatusUser"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @click.self="closeStatusModal"
    >
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-blue-950">
                        {{
                            selectedStatusUser.is_active
                                ? 'Deactivate User Account'
                                : 'Activate User Account'
                        }}
                    </h2>

                    <p class="mt-2 text-sm text-slate-600">
                        {{
                            selectedStatusUser.is_active
                                ? 'This user will no longer be able to access the system, but their records will remain saved.'
                                : 'This user will be able to access the system again.'
                        }}
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-lg px-3 py-1 text-xl font-bold text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                    @click="closeStatusModal"
                >
                    ×
                </button>
            </div>

            <div class="mt-5 rounded-xl bg-slate-50 p-4 text-sm text-slate-700">
                <p>
                    <span class="font-semibold">Name:</span>
                    {{ selectedStatusUser.name }}
                </p>

                <p class="mt-1">
                    <span class="font-semibold">System ID:</span>
                    {{ selectedStatusUser.student_id }}
                </p>

                <p class="mt-1">
                    <span class="font-semibold">Current Status:</span>
                    {{ activeStatusLabel(selectedStatusUser) }}
                </p>
            </div>

            <div
                class="mt-5 rounded-xl p-4 text-sm"
                :class="
                    selectedStatusUser.is_active
                        ? 'bg-red-50 text-red-800'
                        : 'bg-green-50 text-green-800'
                "
            >
                {{
                    selectedStatusUser.is_active
                        ? 'Are you sure you want to deactivate this account?'
                        : 'Are you sure you want to activate this account?'
                }}
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t pt-5">
                <button
                    type="button"
                    class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                    @click="closeStatusModal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="rounded-xl px-4 py-2 text-sm font-semibold text-white"
                    :class="
                        selectedStatusUser.is_active
                            ? 'bg-red-600 hover:bg-red-700'
                            : 'bg-green-600 hover:bg-green-700'
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