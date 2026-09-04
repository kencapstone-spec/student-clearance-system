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
        'student_id' => '202610001',
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'year_level' => '3rd Year',
        'course_id' => $course->id,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'student_id' => '202610001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'name' => 'John Doe',
        'year_level' => '3rd Year',
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
        'first_name' => 'John',
        'last_name' => 'Doe',
        'year_level' => '1st Year',
        'course_id' => $course->id,
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('student_id');

    $this->assertGuest();
});

test('registration fails with student id not exactly 9 digits', function () {
    $course = Course::create([
        'code' => 'BSIS',
        'name' => 'Bachelor of Science in Information System',
    ]);

    // 8 digits (too short)
    $this->post(route('register.store'), [
        'student_id' => '20261000',
        'email' => 'test3@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'year_level' => '1st Year',
        'course_id' => $course->id,
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('student_id');

    // 10 digits (too long)
    $this->post(route('register.store'), [
        'student_id' => '2026100001',
        'email' => 'test3b@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'year_level' => '1st Year',
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
        'student_id' => '202610001',
        'email' => 'test4@example.com',
        'first_name' => 'First',
        'last_name' => 'Student',
        'year_level' => '2nd Year',
        'course_id' => $course->id,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    $this->post(route('logout'));

    $this->post(route('register.store'), [
        'student_id' => '202610001', // same student_id
        'email' => 'test5@example.com',
        'first_name' => 'Second',
        'last_name' => 'Student',
        'year_level' => '2nd Year',
        'course_id' => $course->id,
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('student_id');
});

test('registration fails with invalid course', function () {
    $this->post(route('register.store'), [
        'student_id' => '202610001',
        'email' => 'test6@example.com',
        'first_name' => 'Test',
        'last_name' => 'Student',
        'year_level' => '1st Year',
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
        'student_id' => '202610001',
        'email' => 'test7@example.com',
        'first_name' => 'Test',
        'last_name' => 'Student',
        'year_level' => '1st Year',
        'course_id' => $course->id,
        'password' => 'password',
        'password_confirmation' => 'different_password',
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});
