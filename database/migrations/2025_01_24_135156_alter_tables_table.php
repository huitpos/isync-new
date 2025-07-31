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
        Schema::table('tables', function (Blueprint $table) {
            //add table_status_id default value 0 nullable
            $table->unsignedBigInteger('table_status_id')->default(0)->nullable();

            //dine_in_time string nullable
            $table->string('dine_in_time')->nullable();

            //transaction_id default value 0 nullable
            $table->unsignedBigInteger('transaction_id')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
