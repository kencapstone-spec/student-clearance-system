<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AppSettingSeeder::class,
            CourseSeeder::class,
            OfficeSeeder::class,
            UserSeeder::class,
            CourseOfficeSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call([
                ReadyForPresidentApprovalSeeder::class,
            ]);
        }
    }
}
