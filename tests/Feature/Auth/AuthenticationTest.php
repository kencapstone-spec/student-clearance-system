<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('students can authenticate using student id', function () {
    $user = User::factory()->create([
        'student_id' => '2026-0001',
        'role' => 'student',
    ]);

    $response = $this->post(route('login.store'), [
        'student_id' => $user->student_id,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->create([
        'student_id' => '2026-0002',
        'role' => 'student',
    ]);

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $response = $this->post(route('login.store'), [
        'student_id' => $user->student_id,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $response->assertSessionHas('login.id', $user->id);
    $this->assertGuest();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create([
        'student_id' => '2026-0003',
        'role' => 'student',
    ]);

    $this->post(route('login.store'), [
        'student_id' => $user->student_id,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create([
        'student_id' => '2026-0004',
        'role' => 'student',
    ]);

    $response = $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('home'));
});

test('users are rate limited', function () {
    $user = User::factory()->create([
        'student_id' => '2026-0005',
        'role' => 'student',
    ]);

    RateLimiter::increment(md5('login'.implode('|', [$user->student_id, '127.0.0.1'])), amount: 5);

    $response = $this->post(route('login.store'), [
        'student_id' => $user->student_id,
        'password' => 'wrong-password',
    ]);

    $response->assertTooManyRequests();
});