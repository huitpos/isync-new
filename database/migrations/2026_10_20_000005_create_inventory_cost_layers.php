<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_cost_layers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');
            $table->string('source_type');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->decimal('qty_received', 15, 4);
            $table->decimal('qty_remaining', 15, 4);
            $table->decimal('unit_cost', 15, 4);
            $table->timestamp('received_at');
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('product_id')->references('id')->on('products');
            $table->index(['branch_id', 'product_id', 'qty_remaining']);
        });

        Schema::create('inventory_cost_consumptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_cost_layer_id');
            $table->decimal('qty', 15, 4);
            $table->decimal('unit_cost', 15, 4);
            $table->string('movement_type');
            $table->unsignedBigInteger('movement_id')->nullable();
            $table->date('business_date');
            $table->timestamps();

            $table->foreign('inventory_cost_layer_id')->references('id')->on('inventory_cost_layers');
            $table->index(['movement_type', 'movement_id']);
            $table->index('business_date');
        });

        DB::statement("
            INSERT INTO inventory_cost_layers (
                branch_id, product_id, source_type, source_id,
                qty_received, qty_remaining, unit_cost, received_at, created_at, updated_at
            )
            SELECT
                branch_product.branch_id,
                branch_product.product_id,
                'opening',
                branch_product.id,
                branch_product.stock,
                branch_product.stock,
                COALESCE(NULLIF(branch_product.cost, 0), NULLIF(products.cost, 0), 0),
                NOW(),
                NOW(),
                NOW()
            FROM branch_product
            INNER JOIN products ON products.id = branch_product.product_id
            WHERE branch_product.stock > 0
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_cost_consumptions');
        Schema::dropIfExists('inventory_cost_layers');
    }
};
