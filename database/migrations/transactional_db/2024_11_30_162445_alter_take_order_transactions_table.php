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
        Schema::table('take_order_transactions', function (Blueprint $table) {
            //account_receivable_redeem_at string nullable
            $table->string('account_receivable_redeem_at')->nullable();

            //is_account_receivable_redeem
            $table->boolean('is_account_receivable_redeem')->default(false);

            //total_zero_rated_amount nullable
            $table->decimal('total_zero_rated_amount', 20, 4)->nullable();

            //remarks
            $table->text('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('take_order_transactions', function (Blueprint $table) {
            //
        });
    }
};
