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
        Schema::table('cut_offs', function (Blueprint $table) {
            //total_return
            $table->decimal('total_return', 20, 4)->nullable();

            //is_complete  boolean false
            $table->boolean('is_complete')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cut_offs', function (Blueprint $table) {
            //
        });
    }
};
