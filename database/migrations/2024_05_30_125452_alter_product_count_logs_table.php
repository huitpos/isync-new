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
        Schema::table('product_count_logs', function (Blueprint $table) {
            //add uom_id
            $table->unsignedBigInteger('uom_id')->after('product_id')->nullable();
            $table->foreign('uom_id')->references('id')->on('unit_of_measurements')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_count_logs', function (Blueprint $table) {
            //
        });
    }
};
