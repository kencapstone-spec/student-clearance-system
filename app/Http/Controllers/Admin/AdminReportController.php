<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClearanceRequest;
use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $this->getFilters($request);

        $courses = Course::query()
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $baseQuery = $this->baseReportQuery($filters);

        $totalRequests = (clone $baseQuery)->count();

        $clearedRequests = (clone $baseQuery)
            ->where('status', 'cleared')
            ->count();

        $pendingRequests = (clone $baseQuery)
            ->where('status', 'pending')
            ->count();

        $needsAttentionRequests = (clone $baseQuery)
            ->whereHas('approvals', function ($query) {
                $query->where('status', 'rejected');
            })
            ->count();

        $requests = $this->filteredReportQuery($filters)
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn(ClearanceRequest $clearanceRequest) => $this->formatReportRequest($clearanceRequest));

        return Inertia::render('Admin/Reports/Index', [
            'courses' => $courses,
            'filters' => $filters,
            'summary' => [
                'totalRequests' => $totalRequests,
                'clearedRequests' => $clearedRequests,
                'pendingRequests' => $pendingRequests,
                'needsAttentionRequests' => $needsAttentionRequests,
            ],
            'requests' => $requests,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->getFilters($request);

        $fileName = 'clearance-report-' . now()->format('Y-m-d-His') . '.csv';

        $requests = $this->filteredReportQuery($filters)
            ->latest()
            ->get()
            ->map(fn(ClearanceRequest $clearanceRequest) => $this->formatReportRequest($clearanceRequest));

        return response()->streamDownload(function () use ($requests) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Student Name',
                'Student ID',
                'Course',
                'Semester',
                'School Year',
                'Progress',
                'Status',
                'Cleared At',
            ]);

            foreach ($requests as $request) {
                fputcsv($handle, [
                    $request['student_name'],
                    $request['student_id'],
                    $request['course_code'],
                    $request['semester'],
                    $request['school_year'],
                    $request['approved_regular_approvals'] . ' / ' . $request['total_regular_approvals'],
                    $request['status_label'],
                    $request['cleared_at'] ?? 'Not cleared yet',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function printReport(Request $request): Response
    {
        $filters = $this->getFilters($request);

        $requests = $this->filteredReportQuery($filters)
            ->latest()
            ->get()
            ->map(fn(ClearanceRequest $clearanceRequest) => $this->formatReportRequest($clearanceRequest));

        $summary = [
            'totalRequests' => $requests->count(),
            'clearedRequests' => $requests->where('status_label', 'Cleared')->count(),
            'pendingRequests' => $requests->where('status_label', 'Pending')->count(),
            'needsAttentionRequests' => $requests->where('status_label', 'Needs Attention')->count(),
        ];

        return Inertia::render('Admin/Reports/Print', [
            'filters' => $filters,
            'summary' => $summary,
            'requests' => $requests,
            'generatedAt' => now()->format('M d, Y h:i A'),
        ]);
    }

    private function getFilters(Request $request): array
    {
        return [
            'status' => $request->input('status', 'all'),
            'course_id' => $request->input('course_id'),
            'semester' => $request->input('semester'),
            'school_year' => $request->input('school_year'),
        ];
    }

    private function baseReportQuery(array $filters): Builder
    {
        return ClearanceRequest::query()
            ->with(['user.course', 'approvals.office'])
            ->when($filters['course_id'], function ($query, $courseId) {
                $query->whereHas('user', function ($userQuery) use ($courseId) {
                    $userQuery->where('course_id', $courseId);
                });
            })
            ->when($filters['semester'], function ($query, $semester) {
                $query->where('semester', $semester);
            })
            ->when($filters['school_year'], function ($query, $schoolYear) {
                $query->where('school_year', $schoolYear);
            });
    }

    private function filteredReportQuery(array $filters): Builder
    {
        $query = $this->baseReportQuery($filters);

        if ($filters['status'] === 'cleared') {
            $query->where('status', 'cleared');
        }

        if ($filters['status'] === 'pending') {
            $query->where('status', 'pending');
        }

        if ($filters['status'] === 'needs_attention') {
            $query->whereHas('approvals', function ($approvalQuery) {
                $approvalQuery->where('status', 'rejected');
            });
        }

        return $query;
    }

    private function formatReportRequest(ClearanceRequest $clearanceRequest): array
    {
        $regularApprovals = $clearanceRequest->approvals
            ->filter(function ($approval) {
                return $approval->office && ! $approval->office->is_final_approver;
            });

        $approvedRegularApprovals = $regularApprovals
            ->where('status', 'approved')
            ->count();

        $totalRegularApprovals = $regularApprovals->count();

        $hasRejectedApproval = $clearanceRequest->approvals
            ->where('status', 'rejected')
            ->isNotEmpty();

        $statusLabel = 'Pending';

        if ($hasRejectedApproval) {
            $statusLabel = 'Needs Attention';
        } elseif ($clearanceRequest->status === 'cleared') {
            $statusLabel = 'Cleared';
        }

        return [
            'id' => $clearanceRequest->id,
            'student_name' => $clearanceRequest->user?->name ?? 'Unknown Student',
            'student_id' => $clearanceRequest->user?->student_id ?? 'N/A',
            'course_code' => $clearanceRequest->user?->course?->code ?? 'N/A',
            'semester' => $clearanceRequest->semester,
            'school_year' => $clearanceRequest->school_year,
            'status' => $clearanceRequest->status,
            'status_label' => $statusLabel,
            'cleared_at' => $clearanceRequest->cleared_at?->format('M d, Y h:i A'),
            'approved_regular_approvals' => $approvedRegularApprovals,
            'total_regular_approvals' => $totalRegularApprovals,
            'has_rejected_approval' => $hasRejectedApproval,
        ];
    }
}
