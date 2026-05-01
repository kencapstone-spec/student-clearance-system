<?php

use App\Http\Controllers\Admin\AdminClearanceRequestController;
use App\Http\Controllers\Admin\AdminCourseModuleController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\ClearanceReceipt\ClearanceReceiptController;
use App\Http\Controllers\ClearanceReceipt\ClearanceVerificationController;
use App\Http\Controllers\Notifications\NotificationController;
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

Route::get('/verify-clearance/{verificationCode}', [ClearanceVerificationController::class, 'show'])
    ->name('clearance-verification.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function (Request $request) {
        $user = $request->user()->load('course.offices');

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'president') {
            return redirect()->route('president.final-approvals.index');
        }

        if ($user->role === 'staff') {
            return redirect()->route('staff.pending-requests.index');
        }

        $courseOffices = $user->course?->offices ?? collect();

        $regularOffices = $courseOffices
            ->where('is_final_approver', false)
            ->sortBy('sort_order')
            ->values();

        $finalApproverOffices = Office::query()
            ->where('is_final_approver', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'group', 'sort_order', 'is_final_approver']);

        $offices = $regularOffices
            ->merge($finalApproverOffices)
            ->values();

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

    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])
        ->name('notifications.open');

    Route::patch('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.mark-all-as-read');

    Route::get('/clearance-receipts/{clearanceRequest}', [ClearanceReceiptController::class, 'show'])
        ->name('clearance-receipts.show');

    Route::middleware('role:student')->group(function () {
        Route::post('student/clearance-requests', [ClearanceRequestController::class, 'store'])
            ->name('student.clearance-requests.store');

        Route::patch('student/clearance-requests/request-more-offices', [ClearanceRequestController::class, 'requestMoreOffices'])
            ->name('student.clearance-requests.request-more-offices');

        Route::patch('student/clearance-approvals/{approval}/mark-as-complied', [ClearanceRequestController::class, 'markAsComplied'])
            ->name('student.clearance-approvals.mark-as-complied');
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

        Route::get('/admin/course-modules', [AdminCourseModuleController::class, 'index'])
            ->name('admin.course-modules.index');
            
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

        Route::get('/admin/reports', [AdminReportController::class, 'index'])
            ->name('admin.reports.index');

        Route::get('/admin/reports/export-csv', [AdminReportController::class, 'exportCsv'])
            ->name('admin.reports.export-csv');

        Route::get('/admin/reports/print', [AdminReportController::class, 'printReport'])
            ->name('admin.reports.print');
    });

    Route::middleware('role:president')->group(function () {
        Route::get('/president/final-approvals', [FinalApprovalController::class, 'index'])
            ->name('president.final-approvals.index');

        Route::patch('/president/final-approvals/{clearanceRequest}/approve', [FinalApprovalController::class, 'approve'])
            ->name('president.final-approvals.approve');

        Route::patch('/president/final-approvals/approve-all', [FinalApprovalController::class, 'approveAll'])
            ->name('president.final-approvals.approve-all');
    });
});

require __DIR__ . '/settings.php';
