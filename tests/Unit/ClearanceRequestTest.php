<?php

use App\Models\ClearanceRequest;
use App\Models\User;
use App\Models\ClearanceApproval;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('it belongs to a user', function () {
    $user = User::factory()->create();
    $clearanceRequest = ClearanceRequest::factory()->create(['user_id' => $user->id]);

    expect($clearanceRequest->user->id)->toBe($user->id);
});

test('it has many approvals', function () {
    $clearanceRequest = ClearanceRequest::factory()->create();
    $approval = ClearanceApproval::factory()->create(['clearance_request_id' => $clearanceRequest->id]);

    expect($clearanceRequest->approvals)->toHaveCount(1)
        ->and($clearanceRequest->approvals->first()->id)->toBe($approval->id);
});

test('it casts dates correctly', function () {
    $clearanceRequest = ClearanceRequest::factory()->create([
        'submitted_at' => now(),
        'cleared_at' => now(),
    ]);

    expect($clearanceRequest->submitted_at)->toBeInstanceOf(\Carbon\CarbonInterface::class)
        ->and($clearanceRequest->cleared_at)->toBeInstanceOf(\Carbon\CarbonInterface::class);
});
