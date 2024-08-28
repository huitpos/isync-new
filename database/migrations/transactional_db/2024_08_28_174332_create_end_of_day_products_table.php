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
        Schema::create('end_of_day_products', function (Blueprint $table) {
            $table->id();
            //end_of_day_product_id
            $table->unsignedBigInteger('end_of_day_product_id');
            $table->index('end_of_day_product_id', 'end_of_day_product_id');

            //pos_machine_id
            $table->unsignedBigInteger('pos_machine_id');
            $table->index('pos_machine_id', 'pos_machine_id');

            //branch_id
            $table->unsignedBigInteger('branch_id');
            $table->index('branch_id', 'branch_id');

            //company_id
            $table->unsignedBigInteger('company_id');
            $table->index('company_id', 'company_id');

            //end_of_day_id
            $table->unsignedBigInteger('end_of_day_id');
            $table->index('end_of_day_id', 'end_of_day_id');

            //product_id
            $table->unsignedBigInteger('product_id');
            $table->index('product_id', 'product_id');

            //qty
            $table->double('qty', 15, 4);

            //is_sent_to_server
            $table->boolean('is_sent_to_server')->default(false);

            //treg
            $table->string('treg')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('end_of_day_products');
    }
};
