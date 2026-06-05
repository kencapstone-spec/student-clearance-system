<?php

namespace Database\Factories;

use App\Models\ClearanceApproval;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClearanceApproval>
 */
class ClearanceApprovalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'clearance_request_id' => \App\Models\ClearanceRequest::factory(),
            'office_id' => \App\Models\Office::factory(),
            'status' => 'pending',
            'remarks' => null,
            'approved_by' => null,
            'acted_at' => null,
        ];
    }
}
