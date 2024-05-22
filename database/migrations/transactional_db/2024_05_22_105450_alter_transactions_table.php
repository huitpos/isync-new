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
        Schema::table('transactions', function (Blueprint $table) {
            // is_return boolean default false
            $table->boolean('is_return')->default(false);
            //total_cash_amount decimal nullable
            $table->decimal('total_cash_amount', 15, 4)->nullable();
            //total_return_amount
            $table->decimal('total_return_amount', 15, 4)->nullable();
            //void_counter int
            $table->integer('void_counter')->default(0);
            //void_remarks text nullable
            $table->text('void_remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
};
