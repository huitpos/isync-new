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
        Schema::table('cut_off_departments', function (Blueprint $table) {
            //company_id
            $table->unsignedBigInteger('company_id');
            $table->index('company_id', 'company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cut_off_departments', function (Blueprint $table) {
            //
        });
    }
};
