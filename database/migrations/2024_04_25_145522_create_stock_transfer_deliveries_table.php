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
        Schema::create('stock_transfer_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_transfer_order_id');
            $table->foreign('stock_transfer_order_id')->references('id')->on('stock_transfer_orders');
            $table->string('std_number');
            $table->string('delivery_number');
            $table->enum('status', ['pending', 'for_review', 'approved', 'rejected']);
            $table->unsignedBigInteger('action_by');
            $table->foreign('action_by')->references('id')->on('users');
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_deliveries');
    }
};
