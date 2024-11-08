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
        Schema::table('pos_machines', function (Blueprint $table) {
            //or_reset_counter int
            $table->integer('or_reset_counter')->default(0)->nullable();

           //gt_counter int nullable
            $table->integer('gt_counter')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_machines', function (Blueprint $table) {
            //
        });
    }
};
