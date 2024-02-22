<?php

namespace App\DataTables\Admin;

use App\Models\PosMachine;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class BranchMachinesDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('product_key', function (PosMachine $data) {
                return view('admin.datatables._link', [
                    'url' => route('admin.machines.show', ['machine' => $data->id, 'branchId' => $data->branch_id]),
                    'text' => $data->product_key,
                ]);
            })
            ->addColumn('status', function (PosMachine $data) {
                return view('admin.datatables._status-toggle', [
                    'id' => $data->id,
                    'route' => 'admin.machines.update',
                    'param' => ['branchId' => $data->branch_id, 'machine' => $data->id],
                    'checked' => $data->status == 'active' ? 'checked' : '',
                ]);
            })
            ->addColumn('actions', function (PosMachine $data) {
                return view('admin.datatables._actions', [
                    'param' => ['branchId' => $data->branch_id, 'machine' => $data->id],
                    'route' => 'admin.machines',
                ]);
            });
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(PosMachine $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('pos_machines.branch_id', $this->branch_id)
            ->with([
                'branch',
                'createdBy',
            ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('branch-machines-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>",)
            ->addTableClass('table align-middle table-striped table-row-bordered fs-6 gy-5 gs-7 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(0, 'asc')
            ->drawCallback("function() {" . file_get_contents(resource_path('views/datatables/_common-scripts.js')) . "}");
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::make('machine_number')->title('Machine No.'),
            Column::make('name')->title('Device Name'),
            Column::make('product_key'),
            Column::make('serial_number'),
            Column::make('permit_number'),
            Column::make('valid_from'),
            Column::make('valid_to')->title('Valid Until'),
            Column::make('type')->title('Machine Type'),
            Column::make('created_by.name', 'createdBy.name')->title('created by'),
            Column::computed('status')
                ->exportable(false)
                ->printable(false),
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