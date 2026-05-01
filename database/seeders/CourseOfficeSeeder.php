<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Office;
use Illuminate\Database\Seeder;

class CourseOfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();

        $regularOffices = Office::query()
            ->where('is_final_approver', false)
            ->get();

        foreach ($courses as $course) {
            $course->offices()->syncWithoutDetaching(
                $regularOffices->pluck('id')->toArray()
            );
        }
    }
}