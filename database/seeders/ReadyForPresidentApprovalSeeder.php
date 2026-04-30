<?php

namespace Database\Seeders;

use App\Models\ClearanceApproval;
use App\Models\ClearanceRequest;
use App\Models\Course;
use App\Models\Office;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ReadyForPresidentApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course = Course::firstOrCreate(
            ['code' => 'BSIS'],
            ['name' => 'Bachelor of Science in Information System']
        );

        $nextNumber = User::where('student_id', 'like', 'TESTFINAL%')->count() + 1;

        do {
            $studentId = 'TESTFINAL' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (User::where('student_id', $studentId)->exists());

        $student = User::create([
            'student_id' => $studentId,
            'name' => 'Final Approval Test Student ' . $studentId,
            'email' => null,
            'course_id' => $course->id,
            'office_id' => null,
            'role' => 'student',
            'is_active' => true,
            'deactivated_at' => null,
            'password' => Hash::make('password'),
        ]);

        $clearanceRequest = ClearanceRequest::create([
            'user_id' => $student->id,
            'semester' => '1st Semester',
            'school_year' => '2026-2027',
            'status' => 'pending',
            'submitted_at' => now(),
            'cleared_at' => null,
            'receipt_number' => null,
            'verification_code' => null,
        ]);

        $offices = Office::orderBy('sort_order')->get();

        foreach ($offices as $office) {
            $staffApprover = User::where('role', 'staff')
                ->where('office_id', $office->id)
                ->first();

            ClearanceApproval::create([
                'clearance_request_id' => $clearanceRequest->id,
                'office_id' => $office->id,
                'status' => $office->is_final_approver ? 'pending' : 'approved',
                'remarks' => null,
                'approved_by' => $office->is_final_approver ? null : $staffApprover?->id,
                'acted_at' => $office->is_final_approver ? null : now(),
            ]);
        }

        $presidents = User::where('role', 'president')
            ->where('is_active', true)
            ->get();

        foreach ($presidents as $president) {
            NotificationService::send(
                user: $president,
                title: 'Clearance Ready for Final Approval',
                message: $student->name . ' has completed all regular office approvals and is ready for final approval.',
                link: '/president/final-approvals'
            );
        }

        $this->command->info('Ready-for-President test student created.');
        $this->command->info('Student ID: ' . $studentId);
        $this->command->info('Password: password');

        if ($presidents->isNotEmpty()) {
            $this->command->info('President notification sent.');
        } else {
            $this->command->warn('No active President account found. Notification was not sent.');
        }
    }
}