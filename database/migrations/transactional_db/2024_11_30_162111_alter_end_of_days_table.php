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
            //beginning_cut_off_counter nullable
            $table->integer('beginning_cut_off_counter')->nullable();

            //ending_cut_off_counter nullable
            $table->integer('ending_cut_off_counter')->nullable();

            //total_return
            $table->decimal('total_return', 20, 2)->default(0);

            //is_complete
            $table->boolean('is_complete')->default(false);
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
