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
        Schema::table('product_disposals', function (Blueprint $table) {
            $table->unsignedBigInteger('product_disposal_reason_id')->nullable();
            $table->foreign('product_disposal_reason_id')->references('id')->on('product_disposal_reasons');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_disposals', function (Blueprint $table) {
            //
        });
    }
};
