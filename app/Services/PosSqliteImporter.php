<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Discount;
use App\Models\DiscountDetail;
use App\Models\DiscountOtherInformation;
use App\Models\EndOfDay;
use App\Models\EndOfDayDepartment;
use App\Models\EndOfDayDiscount;
use App\Models\EndOfDayPayment;
use App\Models\EndOfDayProduct;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentOtherInformation;
use App\Models\Payout;
use App\Models\Safekeeping;
use App\Models\SafekeepingDenomination;
use App\Models\SpotAudit;
use App\Models\SpotAuditDenomination;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class PosSqliteImporter
{
    private const CHUNK_SIZE = 500;
    private const MAX_FAILURES = 200;
    private const MAX_SAMPLES = 20;

    // Ordered so parent rows exist before rows that reference them.
    // The first key column receives the SQLite `id`.
    public const TABLES = [
        'transactions' => ['model' => Transaction::class, 'keys' => ['transaction_id', 'pos_machine_id', 'branch_id'], 'preserve' => ['receipt_number']],
        'orders' => ['model' => Order::class, 'keys' => ['order_id', 'pos_machine_id', 'branch_id']],
        'payments' => ['model' => Payment::class, 'keys' => ['payment_id', 'pos_machine_id', 'branch_id']],
        'paymentOtherInformations' => ['model' => PaymentOtherInformation::class, 'keys' => ['payment_other_information_id', 'pos_machine_id', 'branch_id']],
        'discounts' => ['model' => Discount::class, 'keys' => ['discount_id', 'pos_machine_id', 'branch_id']],
        'discountDetails' => ['model' => DiscountDetail::class, 'keys' => ['discount_details_id', 'pos_machine_id', 'branch_id']],
        'discountOtherInformations' => ['model' => DiscountOtherInformation::class, 'keys' => ['discount_other_information_id', 'pos_machine_id', 'branch_id']],
        'payouts' => ['model' => Payout::class, 'keys' => ['payout_id', 'pos_machine_id', 'branch_id']],
        'safekeeping' => ['model' => Safekeeping::class, 'keys' => ['safekeeping_id', 'pos_machine_id', 'branch_id']],
        'safekeepingDenomination' => ['model' => SafekeepingDenomination::class, 'keys' => ['safekeeping_denomination_id', 'safekeeping_id', 'cash_denomination_id', 'pos_machine_id', 'branch_id']],
        'spotAudit' => ['model' => SpotAudit::class, 'keys' => ['spot_audit_id', 'pos_machine_id', 'branch_id']],
        'spotAuditDenomination' => ['model' => SpotAuditDenomination::class, 'keys' => ['spot_audit_denomination_id', 'pos_machine_id', 'branch_id']],
        'endOfDay' => ['model' => EndOfDay::class, 'keys' => ['end_of_day_id', 'pos_machine_id', 'branch_id']],
        'endOfDayDepartments' => ['model' => EndOfDayDepartment::class, 'keys' => ['end_of_day_department_id', 'pos_machine_id', 'branch_id'], 'treg_from_end_of_day' => true],
        'endOfDayDiscounts' => ['model' => EndOfDayDiscount::class, 'keys' => ['end_of_day_discount_id', 'pos_machine_id', 'branch_id'], 'treg_from_end_of_day' => true],
        'endOfDayPayments' => ['model' => EndOfDayPayment::class, 'keys' => ['end_of_day_payment_id', 'pos_machine_id', 'branch_id'], 'treg_from_end_of_day' => true],
        'endOffDayProducts' => ['model' => EndOfDayProduct::class, 'keys' => ['end_of_day_product_id', 'pos_machine_id', 'branch_id'], 'aliases' => ['endOfDayProducts'], 'treg_from_end_of_day' => true],
    ];

    // Applied only when the snake_case name of another column has not already filled the target.
    private const COLUMN_ALIASES = [
        'machineNumber' => 'pos_machine_id',
        'machineId' => 'pos_machine_id',
        'isBackout' => 'is_back_out',
        'isBackoutId' => 'is_back_out_id',
        'backoutBy' => 'back_out_by',
        'isCutoff' => 'is_cut_off',
        'cutoffId' => 'cut_off_id',
        'cutoffAt' => 'cut_off_at',
        'subCategoryId' => 'subcategory_id',
        'subCategoryName' => 'subcategory_name',
    ];

    private \PDO $sqlite;
    private array $overrides = [];
    private array $branchCompanies = [];
    private array $endOfDayTregs = [];
    private array $result = [];

    public static function isSqliteFile(string $path): bool
    {
        $handle = fopen($path, 'rb');
        $header = fread($handle, 16);
        fclose($handle);

        return $header === "SQLite format 3\0";
    }

    /**
     * @param  string[]  $tables  Keys of self::TABLES to import.
     * @param  array  $overrides  Optional branch_id / pos_machine_id applied to every row.
     * @param  int|null  $limit  Process at most this many rows per table, starting after SQLite rowid $afterRowId.
     *                           The table summary then has `next_row_id` (null when the table is finished).
     */
    public function run(string $path, array $tables, array $overrides, bool $dryRun, int $afterRowId = 0, ?int $limit = null): array
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers())) {
            throw new \RuntimeException('The pdo_sqlite PHP extension is not installed on this server.');
        }

        $this->sqlite = new \PDO('sqlite:' . $path, null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        $this->overrides = array_filter($overrides);
        $this->branchCompanies = [];
        $this->result = [
            'dry_run' => $dryRun,
            'file' => basename($path),
            'tables' => [],
            'failures' => [],
            'failure_count' => 0,
        ];

        $sourceTables = [];
        foreach ($this->sqlite->query("SELECT name FROM sqlite_master WHERE type = 'table'")->fetchAll(\PDO::FETCH_COLUMN) as $name) {
            $sourceTables[strtolower($name)] = $name;
        }

        $this->endOfDayTregs = isset($sourceTables['endofday'])
            ? $this->sqlite->query('SELECT id, treg FROM "' . $sourceTables['endofday'] . '"')->fetchAll(\PDO::FETCH_KEY_PAIR)
            : [];

        $connection = DB::connection('transactional_db');
        $connection->beginTransaction();

        try {
            foreach (self::TABLES as $name => $config) {
                if (!in_array($name, $tables, true)) {
                    continue;
                }

                $source = null;
                foreach (array_merge([$name], $config['aliases'] ?? []) as $candidate) {
                    $source ??= $sourceTables[strtolower($candidate)] ?? null;
                }

                $this->importTable($name, $source, $config, $afterRowId, $limit);
            }

            $dryRun ? $connection->rollBack() : $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();

            throw $e;
        }

        return $this->result;
    }

    private function importTable(string $name, ?string $source, array $config, int $afterRowId, ?int $limit): void
    {
        $summary = [
            'source' => $source,
            'source_total' => 0,
            'next_row_id' => null,
            'total' => 0,
            'inserted' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'failed' => 0,
            'ignored_columns' => [],
            'changed_columns' => [],
            'samples' => [],
        ];

        if (!$source) {
            $this->result['tables'][$name] = $summary;
            return;
        }

        $table = (new $config['model'])->getTable();
        $connection = DB::connection('transactional_db');

        // Column name => whether it accepts NULL.
        $targetColumns = collect($connection->select(
            'SELECT COLUMN_NAME, IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$connection->getDatabaseName(), $table]
        ))
            ->mapWithKeys(fn ($column) => [$column->COLUMN_NAME => $column->IS_NULLABLE === 'YES'])
            ->except(['id', 'created_at', 'updated_at'])
            ->all();

        $summary['source_total'] = (int) $this->sqlite->query('SELECT COUNT(*) FROM "' . $source . '"')->fetchColumn();

        $rows = $this->sqlite->prepare(
            'SELECT rowid AS "__rowid", * FROM "' . $source . '" WHERE rowid > ? ORDER BY rowid' . ($limit ? ' LIMIT ' . (int) $limit : '')
        );
        $rows->execute([$afterRowId]);
        $chunk = [];
        $lastRowId = null;

        while ($row = $rows->fetch(\PDO::FETCH_ASSOC)) {
            $lastRowId = (int) $row['__rowid'];
            unset($row['__rowid']);
            $summary['total']++;
            $data = $this->mapRow($row, $config['keys'][0], $targetColumns, $summary['ignored_columns']);

            // Some POS versions leave treg blank on end of day breakdown rows; the POS otherwise copies it from the end of day.
            if (!empty($config['treg_from_end_of_day']) && isset($targetColumns['treg']) && empty($data['treg'])) {
                $data['treg'] = $this->endOfDayTregs[$data['end_of_day_id'] ?? null] ?? $data['treg'] ?? null;
            }

            $chunk[] = [
                'source_id' => $row['id'] ?? null,
                'data' => $data,
            ];

            if (count($chunk) >= self::CHUNK_SIZE) {
                $this->processChunk($name, $table, $config, $chunk, $summary);
                $chunk = [];
            }
        }

        if ($chunk) {
            $this->processChunk($name, $table, $config, $chunk, $summary);
        }

        if ($limit && $summary['total'] === $limit) {
            $summary['next_row_id'] = $lastRowId;
        }

        $summary['ignored_columns'] = array_keys($summary['ignored_columns']);
        $this->result['tables'][$name] = $summary;
    }

    private function mapRow(array $row, string $idColumn, array $targetColumns, array &$ignored): array
    {
        $data = [];
        $deferred = [];

        foreach ($row as $column => $value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            if ($column === 'id') {
                $deferred[$idColumn] = $value;
                continue;
            }

            if (isset(self::COLUMN_ALIASES[$column])) {
                $deferred[self::COLUMN_ALIASES[$column]] = $value;
                continue;
            }

            $target = $this->toSnakeCase($column);

            if (isset($targetColumns[$target])) {
                $data[$target] = $value;
            } else {
                $ignored[$column] = true;
            }
        }

        foreach ($deferred as $target => $value) {
            if (isset($targetColumns[$target]) && !isset($data[$target])) {
                $data[$target] = $value;
            }
        }

        // Like the sync API's ConvertEmptyStringsToNull, except NOT NULL columns keep the empty string.
        foreach ($data as $column => $value) {
            if ($value === '' && $targetColumns[$column]) {
                $data[$column] = null;
            }
        }

        foreach ($this->overrides as $column => $value) {
            if (isset($targetColumns[$column])) {
                $data[$column] = $value;
            }
        }

        if (isset($targetColumns['company_id']) && empty($data['company_id']) && !empty($data['branch_id'])) {
            $this->branchCompanies[$data['branch_id']] ??= Branch::find($data['branch_id'])?->company_id;
            $data['company_id'] = $this->branchCompanies[$data['branch_id']];
        }

        return $data;
    }

    private function processChunk(string $name, string $table, array $config, array $chunk, array &$summary): void
    {
        $keys = $config['keys'];
        $preserve = $config['preserve'] ?? [];
        $query = DB::connection('transactional_db')->table($table);

        $valid = [];
        foreach ($chunk as $item) {
            $missing = array_filter($keys, fn ($key) => !isset($item['data'][$key]) || $item['data'][$key] === '');

            if ($missing) {
                $this->fail($name, $item['source_id'], 'Missing ' . implode(', ', $missing), $summary);
            } else {
                $valid[] = $item;
            }
        }

        if (!$valid) {
            return;
        }

        $lookup = clone $query;
        foreach ($keys as $key) {
            $lookup->whereIn($key, array_unique(array_column(array_column($valid, 'data'), $key)));
        }

        $existing = [];
        foreach ($lookup->get() as $record) {
            $existing[$this->compositeKey((array) $record, $keys)] ??= (array) $record;
        }

        $toInsert = [];
        $now = now();

        foreach ($valid as $item) {
            $data = $item['data'];
            $compositeKey = $this->compositeKey($data, $keys);
            $current = $existing[$compositeKey] ?? null;

            if ($current === null) {
                $toInsert[] = $item;
                $existing[$compositeKey] = $data;
                continue;
            }

            foreach ($preserve as $column) {
                if (!empty($current[$column])) {
                    unset($data[$column]);
                }
            }

            $changes = $this->changedColumns($current, $data);

            if (!$changes) {
                $summary['unchanged']++;
                continue;
            }

            try {
                (clone $query)->where(array_intersect_key($data, array_flip($keys)))
                    ->update($data + ['updated_at' => $now]);
                $existing[$compositeKey] = $data + $current;
                $summary['updated']++;

                foreach ($changes as $column => $change) {
                    $summary['changed_columns'][$column] = ($summary['changed_columns'][$column] ?? 0) + 1;
                }

                $this->sample('update', $item, $keys, $summary, $changes);
            } catch (\Throwable $e) {
                $this->fail($name, $item['source_id'], $e->getMessage(), $summary);
            }
        }

        if (!$toInsert) {
            return;
        }

        $timestamps = ['created_at' => $now, 'updated_at' => $now];

        try {
            (clone $query)->insert(array_map(fn ($item) => $item['data'] + $timestamps, $toInsert));
            $summary['inserted'] += count($toInsert);
            foreach ($toInsert as $item) {
                $this->sample('insert', $item, $keys, $summary);
            }
        } catch (\Throwable) {
            foreach ($toInsert as $item) {
                try {
                    (clone $query)->insert($item['data'] + $timestamps);
                    $summary['inserted']++;
                    $this->sample('insert', $item, $keys, $summary);
                } catch (\Throwable $e) {
                    $this->fail($name, $item['source_id'], $e->getMessage(), $summary);
                }
            }
        }
    }

    /**
     * @return array<string, array{old: mixed, new: mixed}>
     */
    private function changedColumns(array $current, array $data): array
    {
        $changes = [];

        foreach ($data as $column => $value) {
            $existing = $current[$column] ?? null;

            if ($value === null || $existing === null) {
                $same = $value === $existing;
            } elseif (is_numeric($value) && is_numeric($existing)) {
                // Some server columns store less precision (e.g. decimal(15,2), integer) than the POS.
                $decimals = strlen(substr(strrchr((string) $existing, '.') ?: '', 1));
                $same = abs(round((float) $value, $decimals) - (float) $existing) <= 0.00001;
            } else {
                $same = (string) $value === (string) $existing;
            }

            if (!$same) {
                $changes[$column] = ['old' => $existing, 'new' => $value];
            }
        }

        return $changes;
    }

    private function compositeKey(array $row, array $keys): string
    {
        return implode('|', array_map(fn ($key) => (string) ($row[$key] ?? ''), $keys));
    }

    private function sample(string $action, array $item, array $keys, array &$summary, array $changes = []): void
    {
        $count = count(array_filter($summary['samples'], fn ($sample) => $sample['action'] === $action));

        if ($count >= self::MAX_SAMPLES) {
            return;
        }

        $summary['samples'][] = [
            'action' => $action,
            'source_id' => $item['source_id'],
            'keys' => array_map(fn ($key) => $item['data'][$key], array_combine($keys, $keys)),
            'treg' => $item['data']['treg'] ?? null,
            'changes' => array_map(fn ($change) => array_map(fn ($value) => $this->preview($value), $change), $changes),
        ];
    }

    private function preview($value)
    {
        return is_string($value) && mb_strlen($value) > 200 ? mb_substr($value, 0, 200) . '…' : $value;
    }

    private function fail(string $table, $sourceId, string $error, array &$summary): void
    {
        $summary['failed']++;
        $this->result['failure_count']++;

        if (count($this->result['failures']) < self::MAX_FAILURES) {
            $this->result['failures'][] = [
                'table' => $table,
                'source_id' => $sourceId,
                'error' => $error,
            ];
        }
    }

    private function toSnakeCase(string $column): string
    {
        $column = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1_$2', $column);
        $column = preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $column);

        return strtolower($column);
    }
}
