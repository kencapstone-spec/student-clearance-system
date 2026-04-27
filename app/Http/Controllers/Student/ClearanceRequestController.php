<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClearanceApproval;
use App\Models\ClearanceRequest;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClearanceRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $semester = '1st Semester';
        $schoolYear = '2026-2027';

        $existingRequest = ClearanceRequest::where('user_id', $user->id)
            ->where('semester', $semester)
            ->where('school_year', $schoolYear)
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'You already submitted a clearance request for this semester.');
        }

        DB::transaction(function () use ($user, $semester, $schoolYear) {
            $clearanceRequest = ClearanceRequest::create([
                'user_id' => $user->id,
                'semester' => $semester,
                'school_year' => $schoolYear,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            $offices = Office::orderBy('sort_order')->get();

            foreach ($offices as $office) {
                ClearanceApproval::create([
                    'clearance_request_id' => $clearanceRequest->id,
                    'office_id' => $office->id,
                    'status' => 'pending',
                ]);
            }
        });

        return back()->with('success', 'Clearance request submitted successfully.');
    }
}