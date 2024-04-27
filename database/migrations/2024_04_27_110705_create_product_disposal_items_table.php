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
        Schema::create('product_disposal_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_disposal_id');
            $table->foreign('product_disposal_id')->references('id')->on('product_disposals');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedBigInteger('uom_id');
            $table->foreign('uom_id')->references('id')->on('unit_of_measurements');
            $table->double('quantity', 15, 4);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_disposal_items');
    }
};
