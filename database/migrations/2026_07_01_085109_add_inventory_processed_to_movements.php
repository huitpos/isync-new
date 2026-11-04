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
        $tables = [
            'purchase_deliveries',
            'stock_transfer_deliveries',
            'stock_transfer_orders',
            'product_disposals',
            'product_physical_counts',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (!Schema::hasColumn($table->getTable(), 'inventory_processed')) {
                        $table->boolean('inventory_processed')->default(false)->after('status')->index();
                    }
                });
            }
        }

        // Add to transactions table in transactional_db
        if (Schema::connection('transactional_db')->hasTable('transactions')) {
            Schema::connection('transactional_db')->table('transactions', function (Blueprint $table) {
                if (!Schema::connection('transactional_db')->hasColumn('transactions', 'inventory_processed')) {
                    $table->boolean('inventory_processed')->default(false)->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'purchase_deliveries',
            'stock_transfer_deliveries',
            'stock_transfer_orders',
            'product_disposals',
            'product_physical_counts',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'inventory_processed')) {
                        $table->dropColumn('inventory_processed');
                    }
                });
            }
        }

        // Remove from transactions table in transactional_db
        if (Schema::connection('transactional_db')->hasTable('transactions')) {
            Schema::connection('transactional_db')->table('transactions', function (Blueprint $table) {
                if (Schema::connection('transactional_db')->hasColumn('transactions', 'inventory_processed')) {
                    $table->dropColumn('inventory_processed');
                }
            });
        }
    }
};
