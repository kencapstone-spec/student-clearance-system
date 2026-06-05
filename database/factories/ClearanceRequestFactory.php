<?php

namespace Database\Factories;

use App\Models\ClearanceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClearanceRequest>
 */
class ClearanceRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'semester' => '1st Semester',
            'school_year' => '2026-2027',
            'status' => 'pending',
            'submitted_at' => now(),
            'cleared_at' => null,
            'receipt_number' => null,
            'verification_code' => null,
        ];
    }
}
