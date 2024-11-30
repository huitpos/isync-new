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
            //is_completed
            $table->boolean('is_completed')->default(false);

            //completed_at
            $table->string('completed_at')->nullable();
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
