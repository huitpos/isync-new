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
        Schema::table('discounts', function (Blueprint $table) {
            //remove customer_address, customer_identification_number, customer_name
            $table->dropColumn('customer_address');
            $table->dropColumn('customer_identification_number');
            $table->dropColumn('customer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            //
        });
    }
};
