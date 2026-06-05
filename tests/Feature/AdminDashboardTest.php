<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can view the admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($admin)->get(route('admin.dashboard'));
    
    $response->assertStatus(200)->assertInertia(fn ($page) => $page->component('Admin/Dashboard'));
});

test('non-admin cannot access admin dashboard', function () {
    $student = User::factory()->create(['role' => 'student']);
    $staff = User::factory()->create(['role' => 'staff']);
    $president = User::factory()->create(['role' => 'president']);

    $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($staff)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($president)->get(route('admin.dashboard'))->assertForbidden();
});

test('admin can view users list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($admin)->get(route('admin.users.index'));
    
    $response->assertStatus(200)->assertInertia(fn ($page) => $page->component('Admin/Users/Index'));
});

test('admin can view clearance requests list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($admin)->get(route('admin.clearance-requests.index'));
    
    $response->assertStatus(200)->assertInertia(fn ($page) => $page->component('Admin/ClearanceRequests/Index'));
});

test('admin can view reports', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($admin)->get(route('admin.reports.index'));
    
    $response->assertStatus(200)->assertInertia(fn ($page) => $page->component('Admin/Reports/Index'));
});
