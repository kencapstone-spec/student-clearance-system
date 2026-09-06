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
            'student_id' => [
                'required',
                'string',
                'regex:/^[0-9]{9}$/',
                'unique:users,student_id',
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'year_level' => ['required', 'string', 'max:50'],
            'course_id' => ['required', 'exists:courses,id'],
            'password' => $this->passwordRules(),
        ], [
            'student_id.regex' => 'The student ID must be exactly 9 digits and contain numbers only.',
            'last_name.required' => 'The last name field is required.',
            'first_name.required' => 'The first name field is required.',
            'year_level.required' => 'Please select your year level.',
        ])->validate();

        $fullName = trim($input['first_name'].' '.$input['last_name']);

        return User::create([
            'student_id' => $input['student_id'],
            'email' => $input['email'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'name' => $fullName,
            'year_level' => $input['year_level'],
            'course_id' => $input['course_id'],
            'role' => 'student',
            'password' => $input['password'],
        ]);
    }
}
