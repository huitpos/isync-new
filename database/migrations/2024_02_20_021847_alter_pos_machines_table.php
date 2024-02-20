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
        Schema::table('pos_machines', function (Blueprint $table) {
            $table->unsignedInteger('or_counter')->default(0)->nullable()->after('type');
            $table->unsignedInteger('x_reading_counter')->default(0)->nullable()->after('or_counter');
            $table->unsignedInteger('z_reading_counter')->default(0)->nullable()->after('x_reading_counter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_machines', function (Blueprint $table) {
            //
        });
    }
};
