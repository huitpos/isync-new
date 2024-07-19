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
        Schema::table('end_of_days', function (Blueprint $table) {
            //beg_reading_number string nullable new
            $table->string('beg_reading_number')->nullable();

            //end_reading_number
            $table->string('end_reading_number')->nullable();

            //total_zero_rated_amount
            $table->double('total_zero_rated_amount', 15, 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('end_of_days', function (Blueprint $table) {
            //
        });
    }
};
