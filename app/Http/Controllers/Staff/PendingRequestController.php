<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\ClearanceApproval;
use App\Models\Course;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PendingRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user()->load('office');

        $semester = AppSetting::get('active_semester', '1st Semester');
        $schoolYear = AppSetting::get('active_school_year', '2026-2027');

        $approvals = ClearanceApproval::query()
            ->where('office_id', $user->office_id)
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->whereHas('clearanceRequest', function ($query) use ($semester, $schoolYear) {
                $query->where('semester', $semester)
                    ->where('school_year', $schoolYear);
            })
            ->with([
                'clearanceRequest.user.course',
                'office',
                'approver',
            ])
            ->latest()
            ->get();

        $courses = Course::query()->orderBy('code')->get(['id', 'code', 'name']);

        return Inertia::render('staff/PendingRequests', [
            'staff' => $user,
            'approvals' => $approvals,
            'courses' => $courses,
        ]);
    }

    public function approve(Request $request, ClearanceApproval $approval): RedirectResponse
    {
        $user = $request->user();

        if ($approval->office_id !== $user->office_id) {
            abort(403);
        }

        if ($approval->status !== 'pending') {
            return back()->with('error', 'Only pending clearance requests can be approved.');
        }

        $approval->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'remarks' => null,
            'acted_at' => now(),
        ]);

        $approval->load(['clearanceRequest.user', 'office']);

        NotificationService::send(
            $approval->clearanceRequest->user,
            'Clearance request approved',
            "Your {$approval->office->name} clearance request has been approved.",
            '/dashboard'
        );

        return back()->with('success', 'Clearance request approved successfully.');
    }

    public function approveAll(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->office_id) {
            abort(403, 'No office assigned.');
        }

        $pendingApprovals = ClearanceApproval::query()
            ->where('office_id', $user->office_id)
            ->where('status', 'pending')
            ->with([
                'clearanceRequest.user',
                'clearanceRequest.approvals.office',
                'office',
            ])
            ->get();

        if ($pendingApprovals->isEmpty()) {
            return back()->with('error', 'There are no pending clearance requests to approve.');
        }

        foreach ($pendingApprovals as $approval) {
            $approval->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'remarks' => null,
                'acted_at' => now(),
            ]);

            $approval->load(['clearanceRequest.user', 'office']);

            NotificationService::send(
                $approval->clearanceRequest->user,
                'Clearance request approved',
                "Your {$approval->office->name} clearance request has been approved.",
                '/dashboard'
            );
        }

        return back()->with(
            'success',
            $pendingApprovals->count().' pending clearance request(s) approved successfully.'
        );
    }

    public function reject(Request $request, ClearanceApproval $approval): RedirectResponse
    {
        $user = $request->user();

        if ($approval->office_id !== $user->office_id) {
            abort(403);
        }

        if ($approval->status !== 'pending') {
            return back()->with('error', 'Only pending clearance requests can be rejected.');
        }

        $validated = $request->validate([
            'remarks' => ['required', 'string', 'max:1000'],
        ]);

        $approval->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'remarks' => $validated['remarks'],
            'acted_at' => now(),
        ]);

        $approval->load(['clearanceRequest.user', 'office']);

        NotificationService::send(
            $approval->clearanceRequest->user,
            'Clearance request rejected',
            "Your {$approval->office->name} clearance request was rejected. Please check the remarks.",
            '/dashboard'
        );

        return back()->with('success', 'Clearance request rejected successfully.');
    }

    public function markAsComplied(Request $request, ClearanceApproval $approval): RedirectResponse
    {
        $user = $request->user();

        if ($approval->office_id !== $user->office_id) {
            abort(403);
        }

        if ($approval->status !== 'rejected') {
            return back()->with('error', 'Only rejected clearance requests can be marked as complied.');
        }

        $approval->update([
            'status' => 'pending',
            'approved_by' => null,
            'acted_at' => null,
        ]);

        $approval->load(['clearanceRequest.user', 'office']);

        if ($approval->clearanceRequest && $approval->clearanceRequest->user) {
            NotificationService::send(
                $approval->clearanceRequest->user,
                'Clearance marked as complied',
                "Your {$approval->office->name} clearance requirement was marked as complied by office staff.",
                '/dashboard'
            );
        }

        return back()->with('success', 'Clearance marked as complied and moved to pending queue.');
    }
}
