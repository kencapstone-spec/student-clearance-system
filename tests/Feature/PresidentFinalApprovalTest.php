<?php

use App\Models\User;
use App\Models\Office;
use App\Models\ClearanceRequest;
use App\Models\ClearanceApproval;
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
        'status' => 'approved'
    ]);
    
    $presidentApproval = ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $presidentOffice->id,
        'status' => 'pending'
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
        'status' => 'approved'
    ]);
    
    ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $presidentOffice->id,
        'status' => 'pending'
    ]);

    $response = $this->actingAs($president)->patch(route('president.final-approvals.approve-all'));

    $response->assertRedirect()->assertSessionHas('success');
    expect($clearanceRequest->fresh()->status)->toBe('cleared');
});
