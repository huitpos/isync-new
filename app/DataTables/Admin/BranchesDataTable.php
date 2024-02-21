<?php

namespace App\DataTables\Admin;

use App\Models\Branch;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class BranchesDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('name', function (Branch $data) {
                return view('admin.datatables._link', [
                    'url' => route('admin.branches.show', $data->id),
                    'text' => $data->name,
                ]);
            })
            ->addColumn('actions', function (Branch $data) {
                return view('admin.datatables._actions', [
                    'param' => $data->id,
                    'route' => 'admin.branches',
                ]);
            });
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(Branch $model): QueryBuilder
    {
        return $model->newQuery()
            ->with([
                'createdBy',
                'company.client.user',
                'cluster',

            ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('branches-table')
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
            Column::make('company.company_name')->title('company name'),
            Column::make('name')->title('branch name'),
            Column::make('code')->title('branch code'),
            Column::make('cluster.name')->title('branch cluster'),
            Column::make('company.client.name')->title("owner's name"),
            Column::make('company.client.user.email')->title("email address"),
            Column::make('created_by.name', 'createdBy.name')->title('created by'),
            Column::computed('status')->title('status'),
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
