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
        Schema::table('purchase_delivery_items', function (Blueprint $table) {
            // rename unit_cost to unit_price
            $table->renameColumn('unit_cost', 'unit_price');

            //add po_unit_price
            $table->decimal('po_unit_price', 15, 4);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_deliveries', function (Blueprint $table) {
            //
        });
    }
};
