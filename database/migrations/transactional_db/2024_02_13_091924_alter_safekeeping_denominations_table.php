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
            $table->string('shift_number')->nullable()->after('total');
            $table->unsignedBigInteger('cut_off_id')->nullable()->after('shift_number');
            $table->string('treg')->nullable()->after('cut_off_id');
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
