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

    public function test_offices_are_sorted_according_to_prerequisites_on_student_dashboard(): void
    {
        $officeA = Office::factory()->create(['name' => 'Independent Office A', 'sort_order' => 10]);
        $officeB = Office::factory()->create(['name' => 'Independent Office B', 'sort_order' => 5]);
        $officeC = Office::factory()->create(['name' => 'Dependent Office C', 'sort_order' => 1]); // lower sort_order but depends on B
        $officeD = Office::factory()->create(['name' => 'Sub-dependent Office D', 'sort_order' => 2]); // depends on C
        $president = Office::factory()->create(['name' => 'Office of the College President', 'is_final_approver' => true, 'sort_order' => 99]);

        $officeC->prerequisites()->attach($officeB->id);
        $officeD->prerequisites()->attach($officeC->id);

        $course = $this->student->course;
        $course->offices()->sync([$officeA->id, $officeB->id, $officeC->id, $officeD->id]);
        $this->student->unsetRelation('course');

        $response = $this->actingAs($this->student)->get(route('dashboard'));
        $response->assertOk();

        $offices = $response->viewData('page')['props']['offices'];
        $officeIds = collect($offices)->pluck('id')->values()->all();

        // Independent offices (B and A) should come before dependent C, which comes before sub-dependent D, and President is last
        $indexOfA = array_search($officeA->id, $officeIds);
        $indexOfB = array_search($officeB->id, $officeIds);
        $indexOfC = array_search($officeC->id, $officeIds);
        $indexOfD = array_search($officeD->id, $officeIds);
        $indexOfPres = array_search($president->id, $officeIds);

        $this->assertLessThan($indexOfC, $indexOfB, 'Office B (prereq of C) must come before Office C');
        $this->assertLessThan($indexOfD, $indexOfC, 'Office C (prereq of D) must come before Office D');
        $this->assertLessThan($indexOfPres, $indexOfD, 'Office D must come before President');
    }
}
