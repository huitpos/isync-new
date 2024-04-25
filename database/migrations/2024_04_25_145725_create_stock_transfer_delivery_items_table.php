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
        Schema::create('stock_transfer_delivery_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_transfer_delivery_id');
            $table->foreign('stock_transfer_delivery_id')->references('id')->on('stock_transfer_deliveries');
            $table->unsignedBigInteger('stock_transfer_order_item_id');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedBigInteger('uom_id');
            $table->foreign('uom_id')->references('id')->on('unit_of_measurements');
            $table->double('qty', 15, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_delivery_items');
    }
};
