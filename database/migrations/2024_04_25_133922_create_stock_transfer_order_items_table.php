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
        Schema::create('stock_transfer_order_items', function (Blueprint $table) {
            $table->id();
            //purchase_order_id
            $table->unsignedBigInteger('stock_transfer_order_id');
            $table->foreign('stock_transfer_order_id')->references('id')->on('stock_transfer_orders');
            //product_id
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            //uom_id
            $table->unsignedBigInteger('uom_id');
            $table->foreign('uom_id')->references('id')->on('unit_of_measurements');

            //quantity
            $table->double('quantity', 15, 4);

            //pr_remarks
            $table->text('str_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_order_items');
    }
};
