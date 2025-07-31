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
        Schema::table('table_statuses', function (Blueprint $table) {
            //is_blinking boolean default to 0
            $table->boolean('is_blinking')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('table_statuses', function (Blueprint $table) {
            //
        });
    }
};
