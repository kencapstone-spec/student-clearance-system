<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClearanceRequest;
use Inertia\Inertia;
use Inertia\Response;

class AdminClearanceRequestController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/ClearanceRequests/Index', [
            'clearanceRequests' => ClearanceRequest::with([
                'user.course',
                'approvals.office',
                'approvals.approver',
            ])
                ->latest()
                ->get(),
        ]);
    }

    public function show(ClearanceRequest $clearanceRequest): Response
    {
        $clearanceRequest->load([
            'user.course',
            'approvals.office',
            'approvals.approver',
        ]);

        return Inertia::render('Admin/ClearanceRequests/Show', [
            'clearanceRequest' => $clearanceRequest,
        ]);
    }
}
