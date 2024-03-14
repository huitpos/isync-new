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
        Schema::create('purchase_deliveries', function (Blueprint $table) {
            $table->id();
            //purchase_order_id
            $table->unsignedBigInteger('purchase_order_id');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
            //pd_number
            $table->string('pd_number');
            //sales_invoice_number
            $table->string('sales_invoice_number');
            //delivery_number
            $table->string('delivery_number');
            //total_qty double
            $table->double('total_qty', 15, 4);
            //status enum pending approved rejected
            $table->enum('status', ['pending', 'approved', 'rejected']);
            //action_by
            $table->unsignedBigInteger('action_by');
            $table->foreign('action_by')->references('id')->on('users');
            //created_by
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            //updated_by
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_deliveries');
    }
};
