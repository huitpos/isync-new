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
        Schema::create('take_order_discount_details', function (Blueprint $table) {
            $table->id();
            //discount_details_id
            $table->unsignedBigInteger('discount_details_id');
            $table->index('discount_details_id');
            //discount_id
            $table->unsignedBigInteger('discount_id');
            $table->index('discount_id');
            //pos_machine_id
            $table->unsignedBigInteger('pos_machine_id');
            $table->index('pos_machine_id');
            //branch_id
            $table->unsignedBigInteger('branch_id');
            $table->index('branch_id');
            //custom_discount_id
            $table->unsignedBigInteger('custom_discount_id');
            $table->index('custom_discount_id');
            //transaction_id
            $table->unsignedBigInteger('transaction_id');
            $table->index('transaction_id');
            //order_id
            $table->unsignedBigInteger('order_id');
            $table->index('order_id');
            //discount_type_id
            $table->unsignedBigInteger('discount_type_id');
            $table->index('discount_type_id');
            //value
            $table->double('value', 15, 4);
            //discount_amount
            $table->double('discount_amount', 15, 4);
            //vat_exempt_amount
            $table->double('vat_exempt_amount', 15, 4);
            //type
            $table->string('type');
            //is_void
            $table->string('is_void')->default(0);
            //void_by_id
            $table->unsignedBigInteger('void_by_id');
            //void_by
            $table->string('void_by');
            //void_at
            $table->timestamp('void_at')->nullable();
            //is_sent_to_server boolean
            $table->boolean('is_sent_to_server')->default(0);
            //is_cut_off
            $table->boolean('is_cut_off')->default(0);
            //cut_off_id
            $table->unsignedBigInteger('cut_off_id');
            $table->index('cut_off_id');
            //is_vat_exempt
            $table->boolean('is_vat_exempt')->default(0);
            //shift_number string
            $table->string('shift_number');
            //treg
            $table->string('treg');
            //vat_expense
            $table->double('vat_expense', 15, 4);
            //is_zero_rated
            $table->boolean('is_zero_rated')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('take_order_discount_details');
    }
};
