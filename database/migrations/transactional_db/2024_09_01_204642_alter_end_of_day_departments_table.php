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
        Schema::table('end_of_day_departments', function (Blueprint $table) {
            //drop discount_type_id no foreign key
            $table->dropColumn('discount_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('end_of_day_departments', function (Blueprint $table) {
            //
        });
    }
};
