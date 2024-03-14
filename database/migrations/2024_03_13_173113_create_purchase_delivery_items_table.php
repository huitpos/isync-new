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
        Schema::create('purchase_delivery_items', function (Blueprint $table) {
            $table->id();
            //purchase_delivery_id
            $table->unsignedBigInteger('purchase_delivery_id');
            $table->foreign('purchase_delivery_id')->references('id')->on('purchase_deliveries');
            //purchase_order_item_id
            $table->unsignedBigInteger('purchase_order_item_id');
            $table->foreign('purchase_order_item_id')->references('id')->on('purchase_order_items');
            //product_id
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            //uom_id
            $table->unsignedBigInteger('uom_id');
            $table->foreign('uom_id')->references('id')->on('unit_of_measurements');
            //qty
            $table->double('qty', 15, 4);
            //unit_cost
            $table->double('unit_cost', 15, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_delivery_items');
    }
};
