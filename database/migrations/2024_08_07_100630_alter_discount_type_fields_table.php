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
        Schema::table('discount_type_fields', function (Blueprint $table) {
            //drop `discount_type_fields_discount_type_id_foreign`
            $table->dropForeign('discount_type_fields_discount_type_id_foreign');
            $table->foreign('discount_type_id')
                ->references('id')
                ->on('discount_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discount_type_fields', function (Blueprint $table) {
            //
        });
    }
};
