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
        Schema::table('stock_transfer_deliveries', function (Blueprint $table) {
            $table->unsignedBigInteger('source_branch_id');
            $table->foreign('source_branch_id')->references('id')->on('branches');
            $table->unsignedBigInteger('destination_branch_id');
            $table->foreign('destination_branch_id')->references('id')->on('branches');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transfer_deliveries', function (Blueprint $table) {
            //
        });
    }
};
