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
        Schema::table('cut_offs', function (Blueprint $table) {
            //beginning_counter_amount string
            $table->string('beginning_counter_amount')->nullable();

            //ending_counter_amount
            $table->string('ending_counter_amount')->nullable();

            //total_cash_fund
            $table->string('total_cash_fund')->nullable();

            //beginning_gt_counter
            $table->string('beginning_gt_counter')->nullable();      
            
            //ending_gt_counter
            $table->string('ending_gt_counter')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cut_offs', function (Blueprint $table) {
            //
        });
    }
};
