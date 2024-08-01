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
        Schema::table('take_order_discounts', function (Blueprint $table) {
            //gross_amount double 15, 4
            $table->decimal('gross_amount', 15, 4)->nullable();
            //net_amount double 15, 4
            $table->decimal('net_amount', 15, 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('take_order_discounts', function (Blueprint $table) {
            //
        });
    }
};
