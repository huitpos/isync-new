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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_branch_id_foreign');
            $table->dropForeign('orders_category_id_foreign');
            $table->dropForeign('orders_department_id_foreign');
            $table->dropForeign('orders_is_back_out_id_foreign');
            $table->dropForeign('orders_pos_machine_id_foreign');
            $table->dropForeign('orders_product_id_foreign');
            $table->dropForeign('orders_subcategory_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
