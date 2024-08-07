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
        Schema::table('payment_type_fields', function (Blueprint $table) {
            //change foreign payment_type_fields_payment_type_id_foreign. delete on delete
            $table->dropForeign('payment_type_fields_payment_type_id_foreign');
            $table->foreign('payment_type_id')
                ->references('id')
                ->on('payment_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_type_fields', function (Blueprint $table) {
            //
        });
    }
};
