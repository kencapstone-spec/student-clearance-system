<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\ClearanceApproval;
use App\Models\ClearanceRequest;
use App\Models\Course;
use App\Models\Office;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        $semester = AppSetting::get('active_semester', '1st Semester');
        $schoolYear = AppSetting::get('active_school_year', '2026-2027');

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'students' => User::where('role', 'student')->count(),
                'staff' => User::where('role', 'staff')->count(),
                'courses' => Course::count(),
                'offices' => Office::count(),
                'clearanceRequests' => ClearanceRequest::where('semester', $semester)->where('school_year', $schoolYear)->count(),
                'pendingApprovals' => ClearanceApproval::whereHas('clearanceRequest', fn ($q) => $q->where('semester', $semester)->where('school_year', $schoolYear))->where('status', 'pending')->count(),
                'approvedApprovals' => ClearanceApproval::whereHas('clearanceRequest', fn ($q) => $q->where('semester', $semester)->where('school_year', $schoolYear))->where('status', 'approved')->count(),
                'rejectedApprovals' => ClearanceApproval::whereHas('clearanceRequest', fn ($q) => $q->where('semester', $semester)->where('school_year', $schoolYear))->where('status', 'rejected')->count(),
            ],

            'recentRequests' => ClearanceRequest::with(['user.course'])
                ->where('semester', $semester)
                ->where('school_year', $schoolYear)
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
