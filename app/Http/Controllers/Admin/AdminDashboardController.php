<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'students' => User::where('role', 'student')->count(),
                'staff' => User::where('role', 'staff')->count(),
                'courses' => Course::count(),
                'offices' => Office::count(),
                'clearanceRequests' => ClearanceRequest::count(),
                'pendingApprovals' => ClearanceApproval::where('status', 'pending')->count(),
                'approvedApprovals' => ClearanceApproval::where('status', 'approved')->count(),
                'rejectedApprovals' => ClearanceApproval::where('status', 'rejected')->count(),
            ],

            'recentRequests' => ClearanceRequest::with(['user.course'])
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}