<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClearanceApproval;
use App\Models\ClearanceRequest;
use App\Models\Office;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClearanceRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'office_ids' => ['required', 'array', 'min:1'],
            'office_ids.*' => ['integer', 'exists:offices,id'],
        ]);

        $semester = '1st Semester';
        $schoolYear = '2026-2027';

        $existingRequest = ClearanceRequest::where('user_id', $user->id)
            ->where('semester', $semester)
            ->where('school_year', $schoolYear)
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'You already submitted a clearance request for this semester.');
        }

        $courseRegularOfficeIds = $this->courseRegularOfficeIds($user);

        if ($courseRegularOfficeIds->isEmpty()) {
            return back()->with('error', 'No clearance offices are configured for your course yet.');
        }

        $selectedOfficeIds = collect($validated['office_ids'])
            ->unique()
            ->intersect($courseRegularOfficeIds)
            ->values();

        if ($selectedOfficeIds->isEmpty()) {
            return back()->with('error', 'Please select at least one office assigned to your course.');
        }

        $approvalOfficeIds = $this->courseApprovalOfficeIds($user);

        DB::transaction(function () use ($user, $semester, $schoolYear, $selectedOfficeIds, $approvalOfficeIds) {
            $clearanceRequest = ClearanceRequest::create([
                'user_id' => $user->id,
                'semester' => $semester,
                'school_year' => $schoolYear,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            $offices = Office::query()
                ->whereIn('id', $approvalOfficeIds)
                ->orderBy('sort_order')
                ->get();

            foreach ($offices as $office) {
                ClearanceApproval::create([
                    'clearance_request_id' => $clearanceRequest->id,
                    'office_id' => $office->id,
                    'status' => $selectedOfficeIds->contains($office->id)
                        ? 'pending'
                        : 'not_requested',
                ]);
            }

            $this->notifyOfficeStaff(
                $selectedOfficeIds,
                'New clearance request',
                "{$user->name} submitted a clearance request for your office.",
                '/staff/pending-requests'
            );
        });

        return back()->with('success', 'Clearance request submitted successfully.');
    }

    public function requestMoreOffices(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'office_ids' => ['required', 'array', 'min:1'],
            'office_ids.*' => ['integer', 'exists:offices,id'],
        ]);

        $semester = '1st Semester';
        $schoolYear = '2026-2027';

        $clearanceRequest = ClearanceRequest::where('user_id', $user->id)
            ->where('semester', $semester)
            ->where('school_year', $schoolYear)
            ->first();

        if (! $clearanceRequest) {
            return back()->with('error', 'Please submit a clearance request first.');
        }

        $courseRegularOfficeIds = $this->courseRegularOfficeIds($user);

        if ($courseRegularOfficeIds->isEmpty()) {
            return back()->with('error', 'No clearance offices are configured for your course yet.');
        }

        $selectedOfficeIds = collect($validated['office_ids'])
            ->unique()
            ->intersect($courseRegularOfficeIds)
            ->values();

        if ($selectedOfficeIds->isEmpty()) {
            return back()->with('error', 'Please select at least one office assigned to your course.');
        }

        $requestableOfficeIds = ClearanceApproval::where('clearance_request_id', $clearanceRequest->id)
            ->whereIn('office_id', $selectedOfficeIds)
            ->where('status', 'not_requested')
            ->pluck('office_id');

        if ($requestableOfficeIds->isEmpty()) {
            return back()->with('error', 'The selected offices have already been requested or processed.');
        }

        ClearanceApproval::where('clearance_request_id', $clearanceRequest->id)
            ->whereIn('office_id', $requestableOfficeIds)
            ->where('status', 'not_requested')
            ->update([
                'status' => 'pending',
            ]);

        $this->notifyOfficeStaff(
            $requestableOfficeIds,
            'New clearance request',
            "{$user->name} requested clearance from your office.",
            '/staff/pending-requests'
        );

        return back()->with('success', 'Selected offices have been requested successfully.');
    }

    public function markAsComplied(Request $request, ClearanceApproval $approval): RedirectResponse
    {
        $user = $request->user();

        $approval->load(['clearanceRequest', 'office']);

        if ($approval->clearanceRequest->user_id !== $user->id) {
            abort(403);
        }

        if ($approval->status !== 'rejected') {
            return back()->with('error', 'Only rejected clearance items can be marked as complied.');
        }

        $approval->update([
            'status' => 'pending',
            'approved_by' => null,
            'acted_at' => null,
        ]);

        $this->notifyOfficeStaff(
            collect([$approval->office_id]),
            'Clearance marked as complied',
            "{$user->name} marked the {$approval->office->name} clearance requirement as complied.",
            '/staff/pending-requests'
        );

        return back()->with('success', 'Marked as complied. The office can now review your clearance again.');
    }

    private function courseRegularOfficeIds(User $user): Collection
    {
        $user->loadMissing('course.offices');

        if (! $user->course) {
            return collect();
        }

        return $user->course->offices
            ->where('is_final_approver', false)
            ->pluck('id')
            ->values();
    }

    private function courseApprovalOfficeIds(User $user): Collection
    {
        $finalApproverOfficeIds = Office::query()
            ->where('is_final_approver', true)
            ->pluck('id');

        return $this->courseRegularOfficeIds($user)
            ->merge($finalApproverOfficeIds)
            ->unique()
            ->values();
    }

    private function notifyOfficeStaff(Collection $officeIds, string $title, string $message, string $link): void
    {
        User::where('role', 'staff')
            ->where('is_active', true)
            ->whereIn('office_id', $officeIds)
            ->get()
            ->each(function (User $staff) use ($title, $message, $link) {
                NotificationService::send($staff, $title, $message, $link);
            });
    }
}