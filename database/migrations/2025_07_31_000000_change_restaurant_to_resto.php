<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify the enum definition
        DB::statement("ALTER TABLE companies MODIFY COLUMN pos_type ENUM('retail', 'resto', 'hospitality') DEFAULT 'retail'");
        
        // Then update any existing 'restaurant' values to 'resto'
        DB::table('companies')
            ->where('pos_type', 'restaurant')
            ->update(['pos_type' => 'resto']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, update any 'resto' values back to 'restaurant'
        DB::table('companies')
            ->where('pos_type', 'resto')
            ->update(['pos_type' => 'restaurant']);
            
        // Then revert the enum definition
        DB::statement("ALTER TABLE companies MODIFY COLUMN pos_type ENUM('retail', 'restaurant', 'hospitality') DEFAULT 'retail'");
    }
};
