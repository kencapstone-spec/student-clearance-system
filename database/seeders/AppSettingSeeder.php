<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AppSetting::set('active_semester', '2nd Semester');
        AppSetting::set('active_school_year', '2026-2027');
    }
}
