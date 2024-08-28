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
        Schema::create('cut_off_products', function (Blueprint $table) {
            $table->id();
            //cut_off_product_id
            $table->unsignedBigInteger('cut_off_product_id');
            $table->index('cut_off_product_id', 'cut_off_product_id');

            //pos_machine_id
            $table->unsignedBigInteger('pos_machine_id');
            $table->index('pos_machine_id', 'pos_machine_id');

            //branch_id
            $table->unsignedBigInteger('branch_id');
            $table->index('branch_id', 'branch_id');

            //company_id
            $table->unsignedBigInteger('company_id');
            $table->index('company_id', 'company_id');
            
            //cut_off_id
            $table->unsignedBigInteger('cut_off_id');
            $table->index('cut_off_id', 'cut_off_id');

            //product_id
            $table->unsignedBigInteger('product_id');
            $table->index('product_id', 'product_id');

            //qty
            $table->double('qty', 15, 4);

            //is_cut_off
            $table->boolean('is_cut_off')->default(false);

            //cut_off_at
            $table->dateTime('cut_off_at');

            //end_of_day_id
            $table->unsignedBigInteger('end_of_day_id');

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
        Schema::dropIfExists('cut_off_products');
    }
};
