<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

type Course = {
    id: number;
    code: string;
    name: string;
};

type Student = {
    id: number;
    name: string;
    student_id: string;
    course: Course | null;
};

type Office = {
    id: number;
    name: string;
    group: string;
    is_final_approver: boolean;
};

type Approver = {
    id: number;
    name: string;
    student_id: string;
    role: string;
} | null;

type Approval = {
    id: number;
    status: 'pending' | 'approved' | 'rejected';
    remarks: string | null;
    acted_at: string | null;
    office: Office;
    approver: Approver;
};

type ClearanceRequest = {
    id: number;
    semester: string;
    school_year: string;
    status: 'pending' | 'cleared';
    submitted_at: string | null;
    cleared_at: string | null;
    created_at: string;
    user: Student;
    approvals: Approval[];
};

defineProps<{
    clearanceRequest: ClearanceRequest;
}>();

const statusLabel = (status: string) => {
    if (status === 'approved') return 'Approved';
    if (status === 'rejected') return 'Rejected';
    if (status === 'cleared') return 'Cleared';

    return 'Pending';
};

const statusBadgeClass = (status: string) => {
    if (status === 'approved' || status === 'cleared') {
        return 'bg-green-100 text-green-700';
    }

    if (status === 'rejected') {
        return 'bg-red-100 text-red-700';
    }

    return 'bg-orange-100 text-orange-700';
};

const approvalProgress = (request: ClearanceRequest) => {
    if (request.approvals.length === 0) {
        return 0;
    }

    const approvedCount = request.approvals.filter((approval) => {
        return approval.status === 'approved';
    }).length;

    return Math.round((approvedCount / request.approvals.length) * 100);
};

const approvedApprovalCount = (request: ClearanceRequest) => {
    return request.approvals.filter((approval) => {
        return approval.status === 'approved';
    }).length;
};

const officeTypeLabel = (office: Office) => {
    if (office.is_final_approver) {
        return 'Final Approver';
    }

    return 'Regular Office';
};
</script>

<template>
    <Head title="Clearance Request Details" />

    <div class="min-h-screen bg-slate-50 p-4 text-slate-900">
        <div class="mx-auto flex max-w-7xl flex-col gap-6">
            <section class="rounded-2xl border bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-blue-700">
                    Admin / Clearance Monitoring
                </p>

                <div
                    class="mt-2 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <h1 class="text-3xl font-bold text-blue-950">
                            Clearance Request Details
                        </h1>

                        <p class="mt-2 text-slate-600">
                            Complete approval breakdown for this student's
                            clearance request.
                        </p>
                    </div>

                    <Link
                        href="/admin/clearance-requests"
                        class="rounded-xl bg-slate-600 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
                    >
                        Back to Monitoring
                    </Link>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-blue-700">Student</p>

                    <p class="mt-2 text-2xl font-bold text-blue-950">
                        {{ clearanceRequest.user.name }}
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        ID: {{ clearanceRequest.user.student_id }}
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Course:
                        {{
                            clearanceRequest.user.course
                                ? clearanceRequest.user.course.code
                                : 'N/A'
                        }}
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-purple-700">Request Info</p>

                    <p class="mt-2 text-sm text-slate-600">
                        Semester:
                        <span class="font-semibold text-blue-950">
                            {{ clearanceRequest.semester }}
                        </span>
                    </p>

                    <p class="mt-2 text-sm text-slate-600">
                        School Year:
                        <span class="font-semibold text-blue-950">
                            {{ clearanceRequest.school_year }}
                        </span>
                    </p>

                    <p class="mt-2 text-sm text-slate-600">
                        Cleared At:
                        <span class="font-semibold text-blue-950">
                            {{ clearanceRequest.cleared_at ?? 'N/A' }}
                        </span>
                    </p>
                </div>

                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <p class="font-semibold text-green-700">Overall Status</p>

                    <div class="mt-3">
                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="statusBadgeClass(clearanceRequest.status)"
                        >
                            {{ statusLabel(clearanceRequest.status) }}
                        </span>
                    </div>

                    <p class="mt-4 text-sm text-slate-500">
                        {{
                            approvedApprovalCount(clearanceRequest)
                        }}
                        /
                        {{ clearanceRequest.approvals.length }}
                        approvals completed
                    </p>

                    <div class="mt-2 h-3 overflow-hidden rounded-full bg-slate-100">
                        <div
                            class="h-full rounded-full bg-green-600"
                            :style="{
                                width: `${approvalProgress(clearanceRequest)}%`,
                            }"
                        ></div>
                    </div>

                    <p class="mt-2 text-sm font-semibold text-blue-950">
                        {{ approvalProgress(clearanceRequest) }}%
                    </p>
                </div>
            </section>

            <section class="rounded-2xl border bg-white shadow-sm">
                <div class="border-b p-6">
                    <h2 class="text-xl font-bold text-blue-950">
                        Office Approval Breakdown
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        All office approval records, including remarks,
                        approver, and action timestamp.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100 text-slate-600">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Office</th>
                                <th class="px-6 py-3 font-semibold">Type</th>
                                <th class="px-6 py-3 font-semibold">Status</th>
                                <th class="px-6 py-3 font-semibold">
                                    Approver
                                </th>
                                <th class="px-6 py-3 font-semibold">
                                    Acted At
                                </th>
                                <th class="px-6 py-3 font-semibold">
                                    Remarks
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="approval in clearanceRequest.approvals"
                                :key="approval.id"
                                class="border-t"
                            >
                                <td class="px-6 py-4 font-medium text-blue-950">
                                    {{ approval.office.name }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ officeTypeLabel(approval.office) }}
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="
                                            statusBadgeClass(approval.status)
                                        "
                                    >
                                        {{ statusLabel(approval.status) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    {{
                                        approval.approver
                                            ? approval.approver.name
                                            : 'N/A'
                                    }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ approval.acted_at ?? 'N/A' }}
                                </td>

                                <td class="max-w-xs px-6 py-4 text-slate-600">
                                    {{ approval.remarks ?? '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</template>