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
        Schema::create('clearance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('semester');
            $table->string('school_year');
            $table->enum('status', ['pending', 'cleared'])->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('cleared_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'semester', 'school_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clearance_requests');
    }
};
