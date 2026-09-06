<?php

use Database\Seeders\AppSettingSeeder;
use Database\Seeders\CourseOfficeSeeder;
use Database\Seeders\CourseSeeder;
use Database\Seeders\OfficeSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (app()->runningUnitTests() || app()->environment('testing')) {
            return;
        }

        (new AppSettingSeeder)->run();
        (new CourseSeeder)->run();
        (new OfficeSeeder)->run();
        (new UserSeeder)->run();
        (new CourseOfficeSeeder)->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep data intact
    }
};
