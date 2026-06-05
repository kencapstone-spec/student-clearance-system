<?php

use App\Models\Course;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new students can register with student id and course', function () {
    $course = Course::create([
        'code' => 'BSIS',
        'name' => 'Bachelor of Science in Information System',
    ]);

    $response = $this->post(route('register.store'), [
        'student_id' => '20260001',
        'email' => 'test@example.com',
        'name' => 'Test Student',
        'course_id' => $course->id,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'student_id' => '20260001',
        'name' => 'Test Student',
        'course_id' => $course->id,
        'role' => 'student',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration fails with invalid student id format', function () {
    $course = Course::create([
        'code' => 'BSIS',
        'name' => 'Bachelor of Science in Information System',
    ]);

    $this->post(route('register.store'), [
        'student_id' => '2026-0001', // dashes not allowed
        'email' => 'test2@example.com',
        'name' => 'Test Student',
        'course_id' => $course->id,
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('student_id');

    $this->assertGuest();
});

test('registration fails with student id shorter than 8 digits', function () {
    $course = Course::create([
        'code' => 'BSIS',
        'name' => 'Bachelor of Science in Information System',
    ]);

    $this->post(route('register.store'), [
        'student_id' => '2026001', // only 7 digits
        'email' => 'test3@example.com',
        'name' => 'Test Student',
        'course_id' => $course->id,
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('student_id');

    $this->assertGuest();
});

test('registration fails with duplicate student id', function () {
    $course = Course::create([
        'code' => 'BSIS',
        'name' => 'Bachelor of Science in Information System',
    ]);

    $this->post(route('register.store'), [
        'student_id' => '20260001',
        'email' => 'test4@example.com',
        'name' => 'First Student',
        'course_id' => $course->id,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    $this->post(route('logout'));

    $this->post(route('register.store'), [
        'student_id' => '20260001', // same student_id
        'email' => 'test5@example.com',
        'name' => 'Second Student',
        'course_id' => $course->id,
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('student_id');
});

test('registration fails with invalid course', function () {
    $this->post(route('register.store'), [
        'student_id' => '20260001',
        'email' => 'test6@example.com',
        'name' => 'Test Student',
        'course_id' => 9999, // non-existent course
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('course_id');

    $this->assertGuest();
});

test('registration fails when passwords do not match', function () {
    $course = Course::create([
        'code' => 'BSIS',
        'name' => 'Bachelor of Science in Information System',
    ]);

    $this->post(route('register.store'), [
        'student_id' => '20260001',
        'email' => 'test7@example.com',
        'name' => 'Test Student',
        'course_id' => $course->id,
        'password' => 'password',
        'password_confirmation' => 'different_password',
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});
