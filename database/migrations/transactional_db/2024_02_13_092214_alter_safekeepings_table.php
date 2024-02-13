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
            $table->string('shift_number')->nullable()->after('is_sent_to_server');
            $table->string('treg')->nullable()->after('shift_number');
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
