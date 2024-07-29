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
            //change authorize_name to nullable
            $table->string('authorize_name')->nullable()->change();
            $table->string('void_by_id')->nullable()->change();
            $table->string('void_by')->nullable()->change();
            $table->string('void_at')->nullable()->change();
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
