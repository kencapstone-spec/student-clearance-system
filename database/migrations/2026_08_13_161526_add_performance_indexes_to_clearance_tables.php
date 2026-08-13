<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clearance_requests', function (Blueprint $table) {
            $table->index(['semester', 'school_year']);
        });

        Schema::table('clearance_approvals', function (Blueprint $table) {
            $table->index(['office_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clearance_tables', function (Blueprint $table) {
            //
        });
    }
};
