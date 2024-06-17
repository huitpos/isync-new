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
        Schema::table('safekeeping_denominations', function (Blueprint $table) {
            $table->dropForeign('safekeeping_denominations_branch_id_foreign');
            $table->dropForeign('safekeeping_denominations_cash_denomination_id_foreign');
            $table->dropForeign('safekeeping_denominations_pos_machine_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('safekeeping_denominations', function (Blueprint $table) {
            //
        });
    }
};
