<?php

namespace Tests\Unit;

use App\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficePrerequisitesSortTest extends TestCase
{
    use RefreshDatabase;

    public function test_sort_by_prerequisites_orders_offices_topologically(): void
    {
        $officeA = Office::factory()->create(['name' => 'Office A', 'sort_order' => 10]);
        $officeB = Office::factory()->create(['name' => 'Office B', 'sort_order' => 5]);
        $officeC = Office::factory()->create(['name' => 'Office C', 'sort_order' => 1]); // C requires B
        $officeD = Office::factory()->create(['name' => 'Office D', 'sort_order' => 2]); // D requires C
        $president = Office::factory()->create(['name' => 'President', 'is_final_approver' => true, 'sort_order' => 99]);

        $officeC->prerequisites()->attach($officeB->id);
        $officeD->prerequisites()->attach($officeC->id);

        $offices = Office::with('prerequisites')->whereIn('id', [
            $officeA->id,
            $officeB->id,
            $officeC->id,
            $officeD->id,
            $president->id,
        ])->get();

        $sorted = Office::sortByPrerequisites($offices);
        $sortedNames = $sorted->pluck('name')->toArray();

        // Check order
        $this->assertEquals(['Office B', 'Office A', 'Office C', 'Office D', 'President'], $sortedNames);
    }
}
