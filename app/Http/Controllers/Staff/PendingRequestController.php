<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ClearanceApproval;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PendingRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user()->load('office');

        $approvals = ClearanceApproval::query()
            ->where('office_id', $user->office_id)
            ->with([
                'clearanceRequest.user.course',
                'office',
                'approver',
            ])
            ->latest()
            ->get();

        return Inertia::render('staff/PendingRequests', [
            'staff' => $user,
            'approvals' => $approvals,
        ]);
    }

    public function approve(Request $request, ClearanceApproval $approval): RedirectResponse
    {
        $user = $request->user();

        if ($approval->office_id !== $user->office_id) {
            abort(403);
        }

        $approval->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'remarks' => null,
            'acted_at' => now(),
        ]);

        return back()->with('success', 'Clearance request approved successfully.');
    }

    public function reject(Request $request, ClearanceApproval $approval): RedirectResponse
    {
        $user = $request->user();

        if ($approval->office_id !== $user->office_id) {
            abort(403);
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

        return back()->with('success', 'Clearance request rejected successfully.');
    }
}