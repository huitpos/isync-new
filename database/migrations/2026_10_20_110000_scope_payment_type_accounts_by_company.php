<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_type_accounts', function (Blueprint $table) {
            $table->index('payment_type_id', 'payment_type_accounts_payment_type_id_index');
        });

        Schema::table('payment_type_accounts', function (Blueprint $table) {
            $table->dropUnique(['payment_type_id']);
        });

        Schema::table('payment_type_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->foreign('company_id')->references('id')->on('companies');
        });

        DB::statement('
            UPDATE payment_type_accounts
            INNER JOIN accounts ON accounts.id = payment_type_accounts.account_id
            SET payment_type_accounts.company_id = accounts.company_id
        ');

        DB::statement('DELETE FROM payment_type_accounts WHERE company_id IS NULL');
        DB::statement('ALTER TABLE payment_type_accounts MODIFY company_id BIGINT UNSIGNED NOT NULL');

        Schema::table('payment_type_accounts', function (Blueprint $table) {
            $table->unique(['company_id', 'payment_type_id']);
        });
    }

    public function down(): void
    {
        Schema::table('payment_type_accounts', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'payment_type_id']);
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
            $table->unique('payment_type_id');
        });

        Schema::table('payment_type_accounts', function (Blueprint $table) {
            $table->dropIndex('payment_type_accounts_payment_type_id_index');
        });
    }
};
