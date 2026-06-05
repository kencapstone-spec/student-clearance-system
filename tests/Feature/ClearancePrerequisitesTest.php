<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\ClearanceApproval;
use App\Models\ClearanceRequest;
use App\Models\Course;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClearancePrerequisitesTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private Office $library;
    private Office $dean;

    protected function setUp(): void
    {
        parent::setUp();

        AppSetting::set('active_semester', '1st Semester');
        AppSetting::set('active_school_year', '2026-2027');

        $course = Course::factory()->create();

        $this->library = Office::factory()->create(['name' => 'Library']);
        $this->dean = Office::factory()->create(['name' => 'Dean']);

        // Dean requires Library
        $this->dean->prerequisites()->attach($this->library->id);

        $course->offices()->attach([$this->library->id, $this->dean->id]);

        $this->student = User::factory()->create([
            'role' => 'student',
            'course_id' => $course->id,
        ]);
    }

    public function test_student_cannot_request_office_with_unmet_prerequisites_on_initial_request(): void
    {
        $response = $this->actingAs($this->student)->post(route('student.clearance-requests.store'), [
            'office_ids' => [$this->library->id, $this->dean->id],
        ]);

        $response->assertSessionHas('error', "You must clear prerequisites before requesting {$this->dean->name}.");
        $this->assertDatabaseCount('clearance_requests', 0);
    }

    public function test_student_can_request_office_with_no_prerequisites(): void
    {
        $response = $this->actingAs($this->student)->post(route('student.clearance-requests.store'), [
            'office_ids' => [$this->library->id],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseCount('clearance_requests', 1);
    }

    public function test_student_cannot_request_more_offices_if_prerequisites_are_not_approved(): void
    {
        // Setup existing request
        $clearanceRequest = ClearanceRequest::factory()->create([
            'user_id' => $this->student->id,
            'semester' => '1st Semester',
            'school_year' => '2026-2027',
        ]);

        // Library is 'pending', not 'approved'
        ClearanceApproval::factory()->create([
            'clearance_request_id' => $clearanceRequest->id,
            'office_id' => $this->library->id,
            'status' => 'pending',
        ]);

        ClearanceApproval::factory()->create([
            'clearance_request_id' => $clearanceRequest->id,
            'office_id' => $this->dean->id,
            'status' => 'not_requested',
        ]);

        $response = $this->actingAs($this->student)->patch(route('student.clearance-requests.request-more-offices'), [
            'office_ids' => [$this->dean->id],
        ]);

        $response->assertSessionHas('error', "You must clear {$this->library->name} before requesting {$this->dean->name}.");
    }

    public function test_student_can_request_more_offices_if_prerequisites_are_approved(): void
    {
        // Setup existing request
        $clearanceRequest = ClearanceRequest::factory()->create([
            'user_id' => $this->student->id,
            'semester' => '1st Semester',
            'school_year' => '2026-2027',
        ]);

        // Library is 'approved'
        ClearanceApproval::factory()->create([
            'clearance_request_id' => $clearanceRequest->id,
            'office_id' => $this->library->id,
            'status' => 'approved',
        ]);

        ClearanceApproval::factory()->create([
            'clearance_request_id' => $clearanceRequest->id,
            'office_id' => $this->dean->id,
            'status' => 'not_requested',
        ]);

        $response = $this->actingAs($this->student)->patch(route('student.clearance-requests.request-more-offices'), [
            'office_ids' => [$this->dean->id],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('clearance_approvals', [
            'clearance_request_id' => $clearanceRequest->id,
            'office_id' => $this->dean->id,
            'status' => 'pending',
        ]);
    }
}
