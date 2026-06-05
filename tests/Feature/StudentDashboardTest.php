<?php

use App\Models\ClearanceApproval;
use App\Models\ClearanceRequest;
use App\Models\Course;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('student can view their dashboard', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/dashboard');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Dashboard'));
});

test('non-student roles are redirected from dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $president = User::factory()->create(['role' => 'president']);
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('admin.dashboard'));
    $this->actingAs($president)->get('/dashboard')->assertRedirect(route('president.final-approvals.index'));
    $this->actingAs($staff)->get('/dashboard')->assertRedirect(route('staff.pending-requests.index'));
});

test('student can submit a clearance request', function () {
    $course = Course::factory()->create();
    $office = Office::factory()->create(['is_final_approver' => false]);
    $course->offices()->attach($office->id);

    $student = User::factory()->create([
        'role' => 'student',
        'course_id' => $course->id,
    ]);

    $response = $this->actingAs($student)->post(route('student.clearance-requests.store'), [
        'office_ids' => [$office->id],
    ]);

    $response->assertRedirect()->assertSessionHas('success');
    expect(ClearanceRequest::where('user_id', $student->id)->exists())->toBeTrue();
});

test('student can request more offices', function () {
    $course = Course::factory()->create();
    $office1 = Office::factory()->create(['is_final_approver' => false]);
    $office2 = Office::factory()->create(['is_final_approver' => false]);
    $course->offices()->attach([$office1->id, $office2->id]);

    $student = User::factory()->create([
        'role' => 'student',
        'course_id' => $course->id,
    ]);

    $clearanceRequest = ClearanceRequest::factory()->create(['user_id' => $student->id]);
    ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $office1->id,
        'status' => 'pending',
    ]);
    ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $office2->id,
        'status' => 'not_requested',
    ]);

    $response = $this->actingAs($student)->patch(route('student.clearance-requests.request-more-offices'), [
        'office_ids' => [$office2->id],
    ]);

    $response->assertRedirect()->assertSessionHas('success');
    expect(ClearanceApproval::where('office_id', $office2->id)->first()->status)->toBe('pending');
});

test('student can mark rejected clearance as complied', function () {
    $student = User::factory()->create(['role' => 'student']);
    $clearanceRequest = ClearanceRequest::factory()->create(['user_id' => $student->id]);
    $office = Office::factory()->create();
    $approval = ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $office->id,
        'status' => 'rejected',
    ]);

    $response = $this->actingAs($student)->patch(route('student.clearance-approvals.mark-as-complied', $approval));

    $response->assertRedirect()->assertSessionHas('success');
    expect($approval->fresh()->status)->toBe('pending');
});
