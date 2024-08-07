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
        Schema::table('subcategory_supplier', function (Blueprint $table) {
            //drop subcategories_category_id_foreign
            $table->dropForeign('subcategory_supplier_subcategory_id_foreign');
            $table->foreign('subcategory_id')
                ->references('id')
                ->on('subcategories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subcategory_supplier', function (Blueprint $table) {
            //
        });
    }
};
