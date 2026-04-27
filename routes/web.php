<?php

use App\Http\Controllers\Admin\AdminClearanceRequestController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\President\FinalApprovalController;
use App\Http\Controllers\Staff\PendingRequestController;
use App\Http\Controllers\Student\ClearanceRequestController;
use App\Models\ClearanceRequest;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function (Request $request) {
        $user = $request->user()->load('course');

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'president') {
            return redirect()->route('president.final-approvals.index');
        }

        if ($user->role === 'staff') {
            return redirect()->route('staff.pending-requests.index');
        }

        $offices = Office::orderBy('sort_order')
            ->get(['id', 'name', 'group', 'sort_order', 'is_final_approver']);

        $clearanceRequest = ClearanceRequest::query()
            ->where('user_id', $user->id)
            ->with(['approvals.office'])
            ->latest()
            ->first();

        return Inertia::render('Dashboard', [
            'student' => $user,
            'offices' => $offices,
            'clearanceRequest' => $clearanceRequest,
        ]);
    })->name('dashboard');

    Route::middleware('role:student')->group(function () {
        Route::post('student/clearance-requests', [ClearanceRequestController::class, 'store'])
            ->name('student.clearance-requests.store');
    });

    Route::middleware('role:staff')->group(function () {
        Route::get('staff/pending-requests', [PendingRequestController::class, 'index'])
            ->name('staff.pending-requests.index');

        Route::patch('staff/clearance-approvals/{approval}/approve', [PendingRequestController::class, 'approve'])
            ->name('staff.clearance-approvals.approve');

        Route::patch('staff/clearance-approvals/{approval}/reject', [PendingRequestController::class, 'reject'])
            ->name('staff.clearance-approvals.reject');
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
            ->name('admin.dashboard');

        Route::get('/admin/users', [AdminUserController::class, 'index'])
            ->name('admin.users.index');

        Route::get('/admin/users/create', [AdminUserController::class, 'create'])
            ->name('admin.users.create');

        Route::post('/admin/users', [AdminUserController::class, 'store'])
            ->name('admin.users.store');

        Route::patch('/admin/users/{user}', [AdminUserController::class, 'update'])
            ->name('admin.users.update');

        Route::patch('/admin/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])
            ->name('admin.users.toggle-active');

        Route::get('/admin/clearance-requests', [AdminClearanceRequestController::class, 'index'])
            ->name('admin.clearance-requests.index');

        Route::get('/admin/clearance-requests/{clearanceRequest}', [AdminClearanceRequestController::class, 'show'])
            ->name('admin.clearance-requests.show');
    });

    Route::middleware('role:president')->group(function () {
        Route::get('/president/final-approvals', [FinalApprovalController::class, 'index'])
            ->name('president.final-approvals.index');

        Route::patch('/president/final-approvals/{clearanceRequest}/approve', [FinalApprovalController::class, 'approve'])
            ->name('president.final-approvals.approve');
    });
});

require __DIR__ . '/settings.php';