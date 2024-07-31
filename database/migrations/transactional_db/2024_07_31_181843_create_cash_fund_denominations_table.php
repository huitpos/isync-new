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
        Schema::create('cash_fund_denominations', function (Blueprint $table) {
            $table->id();
            //cash_fund_denomination_id unsignedBigInteger
            $table->unsignedBigInteger('cash_fund_denomination_id');
            //index
            $table->index('cash_fund_denomination_id');
            $table->unsignedBigInteger('pos_machine_id');
            //branch_id
            $table->unsignedBigInteger('branch_id');
            //cash_fund_id
            $table->unsignedBigInteger('cash_fund_id');
            //cash_denomination_id
            $table->unsignedBigInteger('cash_denomination_id');
            //name
            $table->string('name')->nullable();
            //amount
            $table->double('amount', 15, 4);
            //qty
            $table->integer('qty');
            //total
            $table->double('total', 15, 4);
            //is_cut_off boolean false
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
        Schema::dropIfExists('cash_fund_denominations');
    }
};
