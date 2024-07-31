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
        Schema::create('cash_funds', function (Blueprint $table) {
            $table->id();
            //cash_fund_id unsignedBigInteger
            $table->unsignedBigInteger('cash_fund_id');
            //index
            $table->index('cash_fund_id');
            $table->unsignedBigInteger('pos_machine_id');
            //branch_id
            $table->unsignedBigInteger('branch_id');
            //amount double 15,4
            $table->double('amount', 15, 4);
            //cashier_id
            $table->unsignedBigInteger('cashier_id');
            //cashier_name
            $table->string('cashier_name')->nullable();
            //is_cut_off boolean
            $table->boolean('is_cut_off')->default(false);
            //cut_off_id
            $table->unsignedBigInteger('cut_off_id');
            //end_of_day_id
            $table->unsignedBigInteger('end_of_day_id');
            //is_sent_to_server boolean false
            $table->boolean('is_sent_to_server')->default(false);
            //shift_number
            $table->integer('shift_number');
            //treg
            $table->string('treg')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_funds');
    }
};
