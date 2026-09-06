<?php

use App\Models\ClearanceApproval;
use App\Models\ClearanceRequest;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

test('cron approve-all route rejects unauthorized secret', function () {
    Config::set('app.cron_secret', 'correct-secret-123');

    $response = $this->get('/cron/approve-all/wrong-secret');

    $response->assertStatus(403);
});

test('cron approve-all route executes and approves pending clearance requests', function () {
    Config::set('app.cron_secret', 'test-secret-456');

    $regularOffice = Office::factory()->create(['is_final_approver' => false]);
    $staff = User::factory()->create(['role' => 'staff', 'office_id' => $regularOffice->id]);
    $clearanceRequest = ClearanceRequest::factory()->create();

    $approval = ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $regularOffice->id,
        'status' => 'pending',
    ]);

    $response = $this->get('/cron/approve-all/test-secret-456');

    $response->assertStatus(200);
    expect($approval->fresh()->status)->toBe('approved');
});

test('cron reject-all route executes and rejects pending clearance requests', function () {
    Config::set('app.cron_secret', 'test-secret-456');

    $regularOffice = Office::factory()->create(['is_final_approver' => false]);
    $clearanceRequest = ClearanceRequest::factory()->create();

    $approval = ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $regularOffice->id,
        'status' => 'pending',
    ]);

    $response = $this->get('/cron/reject-all/test-secret-456');

    $response->assertStatus(200);
    expect($approval->fresh()->status)->toBe('rejected');
});

test('cron approve-all works with demo key or without secret', function () {
    $regularOffice = Office::factory()->create(['is_final_approver' => false]);
    $clearanceRequest = ClearanceRequest::factory()->create();

    $approval = ClearanceApproval::factory()->create([
        'clearance_request_id' => $clearanceRequest->id,
        'office_id' => $regularOffice->id,
        'status' => 'pending',
    ]);

    $response = $this->get('/cron/approve-all/demo');
    $response->assertStatus(200);
    expect($approval->fresh()->status)->toBe('approved');
});
