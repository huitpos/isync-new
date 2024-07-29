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
        Schema::table('take_order_discount_details', function (Blueprint $table) {
            //change void_by nullable
            $table->string('void_by')->nullable()->change();
            $table->string('void_by_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('take_order_discount_details', function (Blueprint $table) {
            //
        });
    }
};
