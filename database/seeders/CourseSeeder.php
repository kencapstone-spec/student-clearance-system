<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'code' => 'BSIS',
                'name' => 'Bachelor of Science in Information System',
            ],
            [
                'code' => 'BAEL',
                'name' => 'Bachelor of Arts in English Language',
            ],
            [
                'code' => 'BAPS',
                'name' => 'Bachelor of Science in Political Science',
            ],
            [
                'code' => 'BSA',
                'name' => 'Bachelor of Science in Agriculture',
            ],
            [
                'code' => 'BSAIS',
                'name' => 'Bachelor of Science in Accounting Information System',
            ],
            [
                'code' => 'BECED',
                'name' => 'Bachelor of Early Childhood Education',
            ],
            [
                'code' => 'BSCRIM',
                'name' => 'Bachelor of Science in Criminology',
            ],
        ];

        foreach ($courses as $course) {
            Course::updateOrCreate(
                ['code' => $course['code']],
                ['name' => $course['name']]
            );
        }
    }
}