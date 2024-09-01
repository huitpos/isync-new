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
        Schema::table('end_of_day_discounts', function (Blueprint $table) {
            //is_zero_rated
            $table->boolean('is_zero_rated')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('end_of_day_discounts', function (Blueprint $table) {
            //
        });
    }
};
