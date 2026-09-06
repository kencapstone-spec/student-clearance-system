<?php

use App\Models\ClearanceApproval;
use App\Models\ClearanceRequest;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('president can view final approvals dashboard', function () {
    $president = User::factory()->create(['role' => 'president']);

    $response = $this->actingAs($president)->get(route('president.final-approvals.index'));

    $response->assertStatus(200)->assertInertia(fn ($page) => $page->component('President/FinalApprovals'));
});

test('president can approve a final clearance request', function () {
    $president = User::factory()->create(['role' => 'president']);
    $regularOffice = Office::factory()->create(['is_final_approver' => false]);
    $presidentOffice = Office::factory()->create(['is_final_approver' => true]);

    $clearanceRequest = ClearanceRequest::factory()->create(['status' => 'pending']);

    ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $regularOffice->id,
        'status' => 'approved',
    ]);

    $presidentApproval = ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $presidentOffice->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($president)->patch(route('president.final-approvals.approve', $clearanceRequest));

    $response->assertRedirect()->assertSessionHas('success');

    $clearanceRequest->refresh();
    expect($presidentApproval->fresh()->status)->toBe('approved')
        ->and($clearanceRequest->status)->toBe('cleared')
        ->and($clearanceRequest->receipt_number)->not->toBeNull()
        ->and($clearanceRequest->verification_code)->not->toBeNull();
});

test('president can approve all ready clearance requests', function () {
    $president = User::factory()->create(['role' => 'president']);
    $regularOffice = Office::factory()->create(['is_final_approver' => false]);
    $presidentOffice = Office::factory()->create(['is_final_approver' => true]);

    $clearanceRequest = ClearanceRequest::factory()->create(['status' => 'pending']);

    ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $regularOffice->id,
        'status' => 'approved',
    ]);

    ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $presidentOffice->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($president)->patch(route('president.final-approvals.approve-all'));

    $response->assertRedirect()->assertSessionHas('success');
    expect($clearanceRequest->fresh()->status)->toBe('cleared');
});

test('president can reject a final clearance request with remarks', function () {
    $president = User::factory()->create(['role' => 'president']);
    $student = User::factory()->create(['role' => 'student']);
    $regularOffice = Office::factory()->create(['is_final_approver' => false]);
    $presidentOffice = Office::factory()->create(['is_final_approver' => true]);

    $clearanceRequest = ClearanceRequest::factory()->create([
        'user_id' => $student->id,
        'status' => 'pending',
    ]);

    ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $regularOffice->id,
        'status' => 'approved',
    ]);

    $presidentApproval = ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $presidentOffice->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($president)->patch(
        route('president.final-approvals.reject', $clearanceRequest),
        ['remarks' => 'Please settle your graduation fee at the cashier first.']
    );

    $response->assertRedirect()->assertSessionHas('success');

    $clearanceRequest->refresh();
    expect($presidentApproval->fresh()->status)->toBe('rejected')
        ->and($presidentApproval->fresh()->remarks)->toBe('Please settle your graduation fee at the cashier first.')
        ->and($presidentApproval->fresh()->approved_by)->toBe($president->id)
        ->and($clearanceRequest->status)->toBe('pending');

    $this->assertDatabaseHas('notifications', [
        'user_id' => $student->id,
        'title' => 'Clearance Rejected by College President',
    ]);
});

test('president cannot reject a final clearance request without remarks', function () {
    $president = User::factory()->create(['role' => 'president']);
    $presidentOffice = Office::factory()->create(['is_final_approver' => true]);

    $clearanceRequest = ClearanceRequest::factory()->create(['status' => 'pending']);

    ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $presidentOffice->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($president)->patch(
        route('president.final-approvals.reject', $clearanceRequest),
        ['remarks' => '']
    );

    $response->assertSessionHasErrors('remarks');
});

test('president cannot reject a clearance request that is already cleared', function () {
    $president = User::factory()->create(['role' => 'president']);
    $presidentOffice = Office::factory()->create(['is_final_approver' => true]);

    $clearanceRequest = ClearanceRequest::factory()->create(['status' => 'cleared']);

    ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $presidentOffice->id,
        'status' => 'approved',
    ]);

    $response = $this->actingAs($president)->patch(
        route('president.final-approvals.reject', $clearanceRequest),
        ['remarks' => 'Some remarks']
    );

    $response->assertRedirect()->assertSessionHas('error');
});

test('non-president cannot reject a final clearance request', function () {
    $student = User::factory()->create(['role' => 'student']);
    $clearanceRequest = ClearanceRequest::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($student)->patch(
        route('president.final-approvals.reject', $clearanceRequest),
        ['remarks' => 'Unauthorized rejection']
    );

    $response->assertRedirect(route('dashboard'));
});

test('president dashboard returns pending, approved, and rejected requests', function () {
    $president = User::factory()->create(['role' => 'president']);
    $regularOffice = Office::factory()->create(['is_final_approver' => false]);
    $presidentOffice = Office::factory()->create(['is_final_approver' => true]);

    // 1. Ready Pending request
    $pendingRequest = ClearanceRequest::factory()->create(['status' => 'pending']);
    ClearanceApproval::factory()->create([
        'clearance_request_id' => $pendingRequest->id,
        'office_id' => $regularOffice->id,
        'status' => 'approved',
    ]);
    ClearanceApproval::factory()->create([
        'clearance_request_id' => $pendingRequest->id,
        'office_id' => $presidentOffice->id,
        'status' => 'pending',
    ]);

    // 2. Cleared/Approved request
    $approvedRequest = ClearanceRequest::factory()->create(['status' => 'cleared']);
    ClearanceApproval::factory()->create([
        'clearance_request_id' => $approvedRequest->id,
        'office_id' => $presidentOffice->id,
        'status' => 'approved',
    ]);

    // 3. Rejected request
    $rejectedRequest = ClearanceRequest::factory()->create(['status' => 'pending']);
    ClearanceApproval::factory()->create([
        'clearance_request_id' => $rejectedRequest->id,
        'office_id' => $presidentOffice->id,
        'status' => 'rejected',
        'remarks' => 'Institutional obligation pending',
    ]);

    $response = $this->actingAs($president)->get(route('president.final-approvals.index'));

    $response->assertStatus(200)->assertInertia(fn ($page) => $page
        ->component('President/FinalApprovals')
        ->has('clearanceRequests', 3)
        ->where('readyCount', 1)
    );
});
