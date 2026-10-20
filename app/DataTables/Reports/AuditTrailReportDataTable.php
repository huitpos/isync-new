<?php

namespace App\DataTables\Reports;

use App\DataTables\Concerns\UniformAjaxTable;
use App\DataTables\Reports\Concerns\ReadsReportFilters;
use App\Models\AuditTrail;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AuditTrailReportDataTable extends DataTable
{
    use ReadsReportFilters;
    use UniformAjaxTable;

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query));
    }

    public function query(AuditTrail $model): QueryBuilder
    {
        [$start, $end] = $this->reportDates();
        $machineId = (int) (request()->input('machine_id') ?: $this->machine_id);

        return $model->newQuery()
            ->where('pos_machine_id', $machineId)
            ->whereBetween('treg', [$start, $end]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('treg')->title('Created At'),
            Column::make('action')->title('Action'),
            Column::make('description')->title('Description'),
            Column::make('authorize_name')->title('Authorized By'),
        ];
    }
}
