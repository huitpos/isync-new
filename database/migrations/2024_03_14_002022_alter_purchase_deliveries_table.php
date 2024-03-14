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
        Schema::table('purchase_deliveries', function (Blueprint $table) {
            //total_amount double 15,4
            $table->decimal('total_amount', 15, 4)->after('total_qty');
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
