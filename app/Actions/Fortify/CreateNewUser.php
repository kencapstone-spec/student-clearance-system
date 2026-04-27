<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered student account.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'student_id' => ['required', 'string', 'max:255', 'unique:users,student_id'],
            'name' => ['required', 'string', 'max:255'],
            'course_id' => ['required', 'exists:courses,id'],
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'student_id' => $input['student_id'],
            'name' => $input['name'],
            'course_id' => $input['course_id'],
            'role' => 'student',
            'password' => $input['password'],
        ]);
    }
}