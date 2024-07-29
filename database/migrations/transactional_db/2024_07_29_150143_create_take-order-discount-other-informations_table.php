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
        Schema::create('take_order_discount_other_informations', function (Blueprint $table) {
            $table->id();
            //discount_other_information_id
            $table->unsignedBigInteger('discount_other_information_id');
            $table->index('discount_other_information_id', 'discount_other_information_id');
            //pos_machine_id
            $table->unsignedBigInteger('pos_machine_id');
            $table->index('pos_machine_id', 'pos_machine_id');
            //branch_id
            $table->unsignedBigInteger('branch_id');
            $table->index('branch_id', 'branch_id');
            //transaction_id
            $table->unsignedBigInteger('transaction_id');
            $table->index('transaction_id', 'transaction_id');
            //discount_id
            $table->unsignedBigInteger('discount_id');
            $table->index('discount_id', 'discount_id');
            //name
            $table->string('name');
            //value
            $table->double('value', 15, 4);
            //is_cut_off
            $table->boolean('is_cut_off')->default(0);
            //cut_off_id
            $table->unsignedBigInteger('cut_off_id');
            //is_void
            $table->boolean('is_void')->default(0);
            //is_sent_to_server
            $table->boolean('is_sent_to_server')->default(0);
            //treg
            $table->string('treg');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
