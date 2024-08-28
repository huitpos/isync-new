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
        Schema::table('cut_off_products', function (Blueprint $table) {
            //end_of_day_id nullable change
            $table->unsignedBigInteger('end_of_day_id')->nullable()->change();

            //cut_off_at
            $table->timestamp('cut_off_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cut_off_products', function (Blueprint $table) {
            //
        });
    }
};
