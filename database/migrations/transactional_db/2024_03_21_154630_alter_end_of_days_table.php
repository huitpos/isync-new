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
        Schema::table('end_of_days', function (Blueprint $table) {
            $table->integer('reading_number')->nullable();
            $table->integer('void_qty')->nullable();
            $table->decimal('total_short_over', 15, 4)->nullable();

            $table->dropColumn([
                'others_amount',
                'others_count',
                'others_json',
                'pwd_amount',
                'pwd_count',
                'senior_amount',
                'senior_count',
                'total_ar_card_redeemed_amount',
                'total_ar_cash_redeemed_amount',
                'total_ar_payments',
                'total_card_payments',
                'total_cash_payments',
                'total_mobile_payments',
                'total_online_payments',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('end_of_days', function (Blueprint $table) {
            //
        });
    }
};
