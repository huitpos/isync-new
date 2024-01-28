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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->unsignedBigInteger('subcategory_id');
            $table->foreign('subcategory_id')->references('id')->on('subcategories');
            $table->unsignedBigInteger('uom_id');
            $table->foreign('uom_id')->references('id')->on('unit_of_measurements');
            $table->unsignedBigInteger('item_type_id');
            $table->foreign('item_type_id')->references('id')->on('item_types');

            $table->string('image')->nullable();
            $table->string('code');
            $table->string('barcode');
            $table->string('name');
            $table->string('description');
            $table->string('abbreviation');
            $table->double('srp', 15, 4);
            $table->double('cost', 15, 4);
            $table->double('markup', 15, 4);

            $table->boolean('with_serial')->default(false);
            $table->boolean('vatable')->default(false);
            $table->boolean('discount_exempt')->default(false);
            $table->boolean('open_price')->default(false);

            $table->enum('status', ['active', 'inactive']);

            $table->double('minimum_stock_level', 15, 4);
            $table->double('maximum_stock_level', 15, 4);
            $table->double('stock_on_hand', 15, 4);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
