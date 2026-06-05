<?php

use App\Models\ClearanceApproval;
use App\Models\ClearanceRequest;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('staff can view their pending requests', function () {
    $office = Office::factory()->create();
    $staff = User::factory()->create(['role' => 'staff', 'office_id' => $office->id]);

    $response = $this->actingAs($staff)->get('/staff/pending-requests');

    $response->assertStatus(200)->assertInertia(fn ($page) => $page->component('staff/PendingRequests'));
});

test('staff can approve a pending request', function () {
    $office = Office::factory()->create();
    $staff = User::factory()->create(['role' => 'staff', 'office_id' => $office->id]);

    $clearanceRequest = ClearanceRequest::factory()->create();
    $approval = ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $office->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($staff)->patch(route('staff.clearance-approvals.approve', $approval));

    $response->assertRedirect()->assertSessionHas('success');
    expect($approval->fresh()->status)->toBe('approved');
});

test('staff can reject a pending request', function () {
    $office = Office::factory()->create();
    $staff = User::factory()->create(['role' => 'staff', 'office_id' => $office->id]);

    $clearanceRequest = ClearanceRequest::factory()->create();
    $approval = ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $office->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($staff)->patch(route('staff.clearance-approvals.reject', $approval), [
        'remarks' => 'Missing documents',
    ]);

    $response->assertRedirect()->assertSessionHas('success');
    expect($approval->fresh()->status)->toBe('rejected')
        ->and($approval->fresh()->remarks)->toBe('Missing documents');
});

test('staff can approve all pending requests', function () {
    $office = Office::factory()->create();
    $staff = User::factory()->create(['role' => 'staff', 'office_id' => $office->id]);

    $approval1 = ClearanceApproval::factory()->create([
        'office_id' => $office->id,
        'status' => 'pending',
    ]);

    $approval2 = ClearanceApproval::factory()->create([
        'office_id' => $office->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($staff)->patch(route('staff.clearance-approvals.approve-all'));

    $response->assertRedirect()->assertSessionHas('success');
    expect($approval1->fresh()->status)->toBe('approved')
        ->and($approval2->fresh()->status)->toBe('approved');
});
