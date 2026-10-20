<?php

namespace App\DataTables\Admin;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\PosDevice;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class MachineDevicesDataTable extends DataTable
{
    use UniformAjaxTable;

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return $this->withRelationSearch((new EloquentDataTable($query))
            ->addColumn('actions', function (PosDevice $data) {
                return view('admin.datatables._actions', [
                    'param' => $data->id,
                    'hideEdit' => true,
                    'showDelete' => true,
                    'route' => 'admin.devices',
                    'deleteParam' => [
                        'device' => $data->id,
                        'branchId' => $data->machine->branch_id,
                    ]
                ]);
            }));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(PosDevice $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('pos_machine_id', $this->machine_id)
            ->with(['machine']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('device id'),
            Column::make('serial'),
            Column::make('model'),
            Column::make('android_id'),
            Column::make('manufacturer'),
            Column::make('board'),
            Column::computed('actions')
                ->exportable(false)
                ->printable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}
