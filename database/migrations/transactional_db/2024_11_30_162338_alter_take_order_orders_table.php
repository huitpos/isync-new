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
        Schema::table('take_order_orders', function (Blueprint $table) {
            //price_change_reason_id
            $table->unsignedBigInteger('price_change_reason_id')->nullable();

            //zero_rated_amount nullable
            $table->decimal('zero_rated_amount', 20, 2)->nullable();

            //is_free
            $table->boolean('is_free')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('take_order_orders', function (Blueprint $table) {
            //
        });
    }
};
