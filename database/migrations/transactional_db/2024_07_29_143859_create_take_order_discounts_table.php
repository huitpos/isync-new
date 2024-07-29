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
        Schema::create('take_order_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('discount_id');
            $table->index('discount_id');
            //pos_machine_id
            $table->unsignedBigInteger('pos_machine_id');
            $table->index('pos_machine_id');
            //branch_id
            $table->unsignedBigInteger('branch_id');
            $table->index('branch_id');
            //transaction_id
            $table->unsignedBigInteger('transaction_id');
            $table->index('transaction_id');
            //custom_discount_id
            $table->unsignedBigInteger('custom_discount_id');
            $table->index('custom_discount_id');
            //discount_type_id
            $table->unsignedBigInteger('discount_type_id');
            $table->index('discount_type_id');
            //discount_name
            $table->string('discount_name');
            //value
            $table->double('value', 15, 4);
            //discount_amount
            $table->double('discount_amount', 15, 4);
            //vat_exempt_amount
            $table->double('vat_exempt_amount', 15, 4);
            //type
            $table->string('type');
            //cashier_id
            $table->unsignedBigInteger('cashier_id');
            $table->index('cashier_id');
            //cashier_name
            $table->string('cashier_name');
            //authorize_id
            $table->unsignedBigInteger('authorize_id');
            //authorize_name
            $table->string('authorize_name');
            //is_void default no
            $table->string('is_void')->default(0);
            //void_by_id
            $table->unsignedBigInteger('void_by_id');
            //void_by
            $table->string('void_by');
            //void_at
            $table->timestamp('void_at')->nullable();
            //is_sent_to_server
            $table->string('is_sent_to_server')->default(0);
            //is_cut_off
            $table->string('is_cut_off')->default(0);
            //cut_off_id
            $table->unsignedBigInteger('cut_off_id');
            //shift_number
            $table->string('shift_number');
            //treg
            $table->string('treg');
            //vat_expense
            $table->double('vat_expense', 15, 4);
            //is_zero_rated
            $table->string('is_zero_rated')->default(0);
            //created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('take_order_discounts', function (Blueprint $table) {
            //
        });
    }
};
