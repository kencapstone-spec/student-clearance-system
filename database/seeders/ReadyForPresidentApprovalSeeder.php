<?php

namespace Database\Seeders;

use App\Models\ClearanceApproval;
use App\Models\ClearanceRequest;
use App\Models\Course;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ReadyForPresidentApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course = Course::where('code', 'BSIS')->first();

        $student = User::updateOrCreate(
            ['student_id' => 'TESTFINAL001'],
            [
                'name' => 'Final Approval Test Student',
                'email' => null,
                'course_id' => $course?->id,
                'office_id' => null,
                'role' => 'student',
                'password' => Hash::make('password'),
            ]
        );

        $clearanceRequest = ClearanceRequest::updateOrCreate(
            [
                'user_id' => $student->id,
                'semester' => '1st Semester',
                'school_year' => '2026-2027',
            ],
            [
                'status' => 'pending',
            ]
        );

        $offices = Office::orderBy('sort_order')->get();

        foreach ($offices as $office) {
            ClearanceApproval::updateOrCreate(
                [
                    'clearance_request_id' => $clearanceRequest->id,
                    'office_id' => $office->id,
                ],
                [
                    'status' => $office->is_final_approver ? 'pending' : 'approved',
                    'remarks' => null,
                    'approved_by' => null,
                    'acted_at' => $office->is_final_approver ? null : now(),
                ]
            );
        }
    }
}