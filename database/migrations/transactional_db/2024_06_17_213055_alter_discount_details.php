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
            $table->dropForeign('transactions_branch_id_foreign');
            $table->dropForeign('transactions_cashier_id_foreign');
            $table->dropForeign('transactions_charge_account_id_foreign');
            $table->dropForeign('transactions_is_back_out_id_foreign');
            $table->dropForeign('transactions_pos_machine_id_foreign');
            $table->dropForeign('transactions_take_order_id_foreign');
            $table->dropForeign('transactions_void_by_id_foreign');
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
