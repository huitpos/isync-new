<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'transactional_db';

    private const INDEX_NAME = 'end_of_days_unique_key';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->deleteDuplicateEndOfDays();

        if (!$this->indexExists('end_of_days', self::INDEX_NAME)) {
            Schema::table('end_of_days', function (Blueprint $table) {
                $table->unique(
                    ['end_of_day_id', 'pos_machine_id', 'branch_id'],
                    self::INDEX_NAME
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->indexExists('end_of_days', self::INDEX_NAME)) {
            Schema::table('end_of_days', function (Blueprint $table) {
                $table->dropUnique(self::INDEX_NAME);
            });
        }
    }

    /**
     * Keep the earliest row per key and remove later duplicates.
     */
    private function deleteDuplicateEndOfDays(): void
    {
        // Keep the lowest id per key; delete later duplicate rows.
        DB::connection('transactional_db')->statement('
            DELETE duplicate_row
            FROM end_of_days duplicate_row
            INNER JOIN end_of_days kept_row
                ON duplicate_row.end_of_day_id = kept_row.end_of_day_id
                AND duplicate_row.pos_machine_id = kept_row.pos_machine_id
                AND duplicate_row.branch_id = kept_row.branch_id
                AND duplicate_row.id > kept_row.id
        ');
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
