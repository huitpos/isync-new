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
        Schema::table('cut_offs', function (Blueprint $table) {
            // drop department_id
            $table->dropForeign('cut_offs_branch_id_foreign');
            $table->dropForeign('cut_offs_cashier_id_foreign');
            $table->dropForeign('cut_offs_pos_machine_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
