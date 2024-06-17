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
        Schema::table('discount_details', function (Blueprint $table) {
            $table->dropForeign('discount_details_branch_id_foreign');
            $table->dropForeign('discount_details_pos_machine_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discount_details', function (Blueprint $table) {
            //
        });
    }
};
