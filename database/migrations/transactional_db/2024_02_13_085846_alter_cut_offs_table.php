<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'transactional_db';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cut_offs', function (Blueprint $table) {
            $table->renameColumn('total_charge', 'total_change');
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
