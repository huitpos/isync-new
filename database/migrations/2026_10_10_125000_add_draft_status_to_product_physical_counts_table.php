<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_physical_counts', function (Blueprint $table) {
            $newEnumValues = "'pending', 'approved', 'rejected', 'draft'";

            DB::statement("ALTER TABLE product_physical_counts MODIFY COLUMN status ENUM($newEnumValues) NOT NULL DEFAULT 'pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('product_physical_counts')->where('status', 'draft')->update(['status' => 'pending']);

        Schema::table('product_physical_counts', function (Blueprint $table) {
            $newEnumValues = "'pending', 'approved', 'rejected'";

            DB::statement("ALTER TABLE product_physical_counts MODIFY COLUMN status ENUM($newEnumValues) NOT NULL DEFAULT 'pending'");
        });
    }
};
