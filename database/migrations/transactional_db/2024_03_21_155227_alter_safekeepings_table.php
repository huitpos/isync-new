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
        Schema::table('safekeepings', function (Blueprint $table) {
            $table->unsignedBigInteger('end_of_day_id')->nullable();
            $table->boolean('is_auto')->default(false);
            $table->decimal('short_over', 15, 4)->nullable();

            $table->dropColumn('cut_off_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('safekeepings', function (Blueprint $table) {
            //
        });
    }
};
