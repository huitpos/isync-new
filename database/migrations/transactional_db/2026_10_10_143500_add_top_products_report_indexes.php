<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'transactional_db';

    public function up(): void
    {
        if (!$this->indexExists('transactions', 'transactions_branch_treg_index')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index(
                    ['branch_id', 'is_complete', 'is_void', 'is_back_out', 'treg'],
                    'transactions_branch_treg_index'
                );
            });
        }

        if (!$this->indexExists('orders', 'orders_branch_txn_lookup_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index(
                    ['branch_id', 'transaction_id', 'pos_machine_id'],
                    'orders_branch_txn_lookup_index'
                );
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('transactions', 'transactions_branch_treg_index')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('transactions_branch_treg_index');
            });
        }

        if ($this->indexExists('orders', 'orders_branch_txn_lookup_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_branch_txn_lookup_index');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $row = DB::connection('transactional_db')->selectOne(
            'SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?',
            [$index]
        );

        return $row !== null;
    }
};
