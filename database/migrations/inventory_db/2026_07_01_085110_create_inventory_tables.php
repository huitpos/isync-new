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
        Schema::connection('inventory')->create('branch_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('stock', 12, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['branch_id', 'product_id']);
            $table->index(['branch_id']);
            $table->index(['product_id']);
        });

        Schema::connection('inventory')->create('inventory_movement_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');
            $table->string('movement_type'); // purchase_deliveries, stock_transfer_deliveries, product_disposals, stock_transfer_orders, product_physical_counts, transactions
            $table->unsignedBigInteger('object_id');
            $table->decimal('previous_qty', 12, 2)->nullable();
            $table->decimal('new_qty', 12, 2);
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['branch_id']);
            $table->index(['product_id']);
            $table->index(['movement_type']);
            $table->index(['object_id']);
            $table->index(['processed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('inventory')->dropIfExists('inventory_movement_logs');
        Schema::connection('inventory')->dropIfExists('branch_product');
    }
};
