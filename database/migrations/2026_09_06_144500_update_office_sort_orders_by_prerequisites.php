<?php

use App\Models\Office;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Office::where('name', 'PE/Sports')->update(['sort_order' => 7]);
        Office::where('name', 'Canteen')->update(['sort_order' => 8]);
        Office::where('name', 'Registrar')->update(['sort_order' => 9]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Office::where('name', 'Registrar')->update(['sort_order' => 7]);
        Office::where('name', 'PE/Sports')->update(['sort_order' => 8]);
        Office::where('name', 'Canteen')->update(['sort_order' => 9]);
    }
};
