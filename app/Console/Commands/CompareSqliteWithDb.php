<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use PDO;

use function PHPSTORM_META\map;

class CompareSqliteWithDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //php artisan db:compare-sqlite
    protected $signature = 'db:compare-sqlite {--sqlite-path= : Path to SQLite database file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare SQLite database tables with transactional_db, converting to snake_case';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sqlitePath = $this->option('sqlite-path') ?? database_path('pos.sqlite3');

        if (!file_exists($sqlitePath)) {
            $this->error("SQLite database not found at: {$sqlitePath}");
            return 1;
        }

        $this->info("Reading SQLite database from: {$sqlitePath}");
        $this->newLine();

        // Connect to SQLite
        try {
            $sqlitePdo = new PDO("sqlite:{$sqlitePath}");
            $sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            $this->error("Failed to connect to SQLite: " . $e->getMessage());
            return 1;
        }

        // Get tables from SQLite
        $sqliteQuery = "SELECT name FROM sqlite_master WHERE type='table' ORDER BY name";
        $sqliteTables = $sqlitePdo->query($sqliteQuery)->fetchAll(PDO::FETCH_COLUMN);

        if (empty($sqliteTables)) {
            $this->warn("No tables found in SQLite database");
            return 0;
        }

        $this->info("Found " . count($sqliteTables) . " tables in SQLite");
        $this->newLine();

        // Get tables from transactional_db
        $transactionalTables = DB::connection('transactional_db')
            ->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()");
        $transactionalTableNames = array_map(function ($table) {
            return $table->TABLE_NAME;
        }, $transactionalTables);

        // Compare tables
        $missingTables = [];
        $existingTables = [];
        $tableComparison = [];

        $excludedTables = [
            'advance_orders',
            'android_metadata',
            'application_settings',
            'authenticated_machine_user',
            'branch',
            'cash_denomination',
            'categories',
            'charge_account',
            'company',
            'departments',
            'device_details',
            'devices',
            'discount_type_departments',
            'discount_type_field_options',
            'discount_type_fields',
            'discount_types',
            'logs',
            'machine_details',
            'payment_type_field_options',
            'payment_type_fields',
            'payment_types',
            'permissions',
            'price_change_reason',
            'printer_setup',
            'printer_setup_devices',
            'product_discounts',
            'product_locations',
            'products',
            'products_bundle_item',
            'roles',
            'room_master_table',
            'sqlite_sequence',
            'sub_categories',
            'sync',
            'upload',
            'users'
        ];

        $toPluralTables = [
            'cut_off',
            'cash_fund',
            'cash_fund_denomination',
            'end_of_day',
            'official_receipt_information',
            'safekeeping',
            'safekeeping_denomination',
            'spot_audit',
            'spot_audit_denomination'
        ];

        foreach ($sqliteTables as $sqliteTable) {
            $snakeCaseTable = Str::snake($sqliteTable);
            if (in_array($snakeCaseTable, $excludedTables)) {
                continue;
            }

            if (in_array($snakeCaseTable, $toPluralTables)) {
                $snakeCaseTable = Str::plural($snakeCaseTable);
            }

            if ($snakeCaseTable == 'end_off_day_products') {
                $snakeCaseTable = 'end_of_day_products';
            }

            if (in_array($snakeCaseTable, $transactionalTableNames)) {
                $existingTables[] = $snakeCaseTable;
                $tableComparison[$snakeCaseTable] = [
                    'original' => $sqliteTable,
                    'status' => '✓ EXISTS',
                    'fields' => $this->compareFields($sqlitePdo, $sqliteTable, $snakeCaseTable)
                ];
            } else {
                $missingTables[] = $snakeCaseTable;
                $tableComparison[$snakeCaseTable] = [
                    'original' => $sqliteTable,
                    'status' => '✗ MISSING',
                    'fields' => []
                ];
            }
        }

        // Display results
        $this->displayResults($tableComparison, $existingTables, $missingTables);

        return 0;
    }

    /**
     * Compare fields between SQLite and transactional_db
     */
    private function compareFields($sqlitePdo, $sqliteTable, $snakeCaseTable)
    {
        // Get fields from SQLite
        $sqliteFields = $sqlitePdo->query("PRAGMA table_info({$sqliteTable})")
            ->fetchAll(PDO::FETCH_ASSOC);

        if (empty($sqliteFields)) {
            return [];
        }

        // Get fields from transactional_db
        try {
            $transactionalFields = DB::connection('transactional_db')
                ->select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$snakeCaseTable]);
            $transactionalFieldNames = array_map(function ($field) {
                return $field->COLUMN_NAME;
            }, $transactionalFields);
        } catch (\Exception $e) {
            return [];
        }

        $replaceFields = [
            'machine_number' => 'pos_machine_id',
            'beginning_o_r' => 'beginning_or',
            'ending_o_r' => 'ending_or',
            'beginning_g_t_counter' => 'beginning_gt_counter',
            'ending_g_t_counter' => 'ending_gt_counter',
            'total_s_k' => 'total_sk',
            'machine_id' => 'pos_machine_id',
            'sub_category_id' => 'subcategory_id',
            'sub_category_name' => 'subcategory_name',
            'is_backout' => 'is_back_out',
            'is_backout_id' => 'is_back_out_id',
            'backout_by' => 'back_out_by',
            'is_cutoff' => 'is_cut_off',
            'cutoff_id' => 'cut_off_id',
            'cutoff_by' => 'cut_off_by',
            'cutoff_at' => 'cut_off_at',
        ];

        // Compare fields
        $fieldComparison = [];
        foreach ($sqliteFields as $field) {
            $fieldName = $field['name'];
            $snakeCaseField = Str::snake($fieldName);

            if (array_key_exists($snakeCaseField, $replaceFields)) {
                $snakeCaseField = $replaceFields[$snakeCaseField];
            }

            $exists = in_array($snakeCaseField, $transactionalFieldNames);
            $fieldComparison[] = [
                'original' => $fieldName,
                'snake_case' => $snakeCaseField,
                'type' => $field['type'] ?? 'unknown',
                'exists' => $exists
            ];
        }

        return $fieldComparison;
    }

    /**
     * Display comparison results
     */
    private function displayResults($tableComparison, $existingTables, $missingTables)
    {
        $this->line("<fg=cyan>═══════════════════════════════════════════════════════════════</>");
        $this->line("<fg=cyan>                    DATABASE COMPARISON RESULTS                 </>");
        $this->line("<fg=cyan>═══════════════════════════════════════════════════════════════</>");
        $this->newLine();

        // Summary
        $this->line("<fg=yellow>SUMMARY</>");
        $this->line("Total SQLite Tables: " . count($tableComparison));
        $this->line("<fg=green>Existing in transactional_db: " . count($existingTables) . "</>");
        $this->line("<fg=red>Missing from transactional_db: " . count($missingTables) . "</>");
        $this->newLine();

        // Detailed results
        foreach ($tableComparison as $snakeCaseTable => $comparison) {
            $status = $comparison['status'];
            $original = $comparison['original'];
            
            if (strpos($status, '✓') !== false) {
                $this->line("<fg=green>{$status}</> <fg=cyan>{$snakeCaseTable}</> <fg=gray>(from: {$original})</>");
            } else {
                $this->line("<fg=red>{$status}</> <fg=cyan>{$snakeCaseTable}</> <fg=gray>(from: {$original})</>");
            }

            // Show field comparison for existing tables
            if (!empty($comparison['fields'])) {
                $this->displayFieldComparison($comparison['fields']);
            }

            $this->newLine();
        }

        // Missing tables summary
        if (!empty($missingTables)) {
            $this->line("<fg=red>═══════════════════════════════════════════════════════════════</>");
            $this->line("<fg=red>MISSING TABLES (Not found in transactional_db):</>");
            $this->line("<fg=red>═══════════════════════════════════════════════════════════════</>");
            foreach ($missingTables as $table) {
                $this->line("<fg=red>  • {$table}</>");
            }
            $this->newLine();
        }
    }

    /**
     * Display field comparison
     */
    private function displayFieldComparison($fields)
    {
        $missingFields = [];
        
        foreach ($fields as $field) {
            $symbol = $field['exists'] ? '✓' : '✗';
            $color = $field['exists'] ? 'green' : 'red';
            
            if (!$field['exists']) {
                $this->line("  <fg={$color}>{$symbol}</> {$field['snake_case']} <fg=gray>({$field['type']})</>");
            }
        }
    }
}
