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
        'student_id' => '2026-0001',
        'name' => 'Test Student',
        'course_id' => $course->id,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'student_id' => '2026-0001',
        'name' => 'Test Student',
        'course_id' => $course->id,
        'role' => 'student',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
});