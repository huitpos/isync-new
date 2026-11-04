<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('inventory')->table('inventory_movement_logs', function (Blueprint $table) {
            if (!Schema::connection('inventory')->hasColumn('inventory_movement_logs', 'reverted_at')) {
                $table->timestamp('reverted_at')->nullable()->after('processed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('inventory')->table('inventory_movement_logs', function (Blueprint $table) {
            if (Schema::connection('inventory')->hasColumn('inventory_movement_logs', 'reverted_at')) {
                $table->dropColumn('reverted_at');
            }
        });
    }
};
