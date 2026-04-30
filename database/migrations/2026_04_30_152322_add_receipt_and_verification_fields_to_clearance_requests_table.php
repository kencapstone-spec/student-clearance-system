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
            $table->string('receipt_number')->nullable()->unique()->after('cleared_at');
            $table->string('verification_code')->nullable()->unique()->after('receipt_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clearance_requests', function (Blueprint $table) {
            $table->dropUnique(['receipt_number']);
            $table->dropUnique(['verification_code']);

            $table->dropColumn([
                'receipt_number',
                'verification_code',
            ]);
        });
    }
};