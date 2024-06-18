<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if ($this->foreignKeyExists('discount_types', 'discount_types_department_id_foreign')) {
            Schema::table('discount_types', function (Blueprint $table) {
                $table->dropForeign('discount_types_department_id_foreign');
            });
        }
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

    private function foreignKeyExists($table, $foreignKey)
    {
        $query = "
            SELECT COUNT(*) AS count
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND TABLE_NAME = ?
            AND CONSTRAINT_NAME = ?
            AND TABLE_SCHEMA = DATABASE()
        ";

        $result = DB::select($query, [$table, $foreignKey]);

        return !empty($result) && $result[0]->count > 0;
    }
};
