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
        Schema::table('safekeeping_denominations', function (Blueprint $table) {
            $table->unsignedBigInteger('end_of_day_id')->nullable();
            $table->boolean('is_cut_off')->default(false);
            $table->boolean('is_sent_to_server')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('safekeeping_denominations', function (Blueprint $table) {
            //
        });
    }
};
