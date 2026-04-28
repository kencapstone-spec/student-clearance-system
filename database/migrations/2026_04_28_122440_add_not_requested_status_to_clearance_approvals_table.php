<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE clearance_approvals
            MODIFY status ENUM('not_requested', 'pending', 'approved', 'rejected')
            NOT NULL DEFAULT 'not_requested'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::table('clearance_approvals')
            ->where('status', 'not_requested')
            ->update(['status' => 'pending']);

        DB::statement("
            ALTER TABLE clearance_approvals
            MODIFY status ENUM('pending', 'approved', 'rejected')
            NOT NULL DEFAULT 'pending'
        ");
    }
};