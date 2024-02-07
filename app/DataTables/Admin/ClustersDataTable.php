<?php

namespace App\DataTables\Admin;

use App\Models\Cluster;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ClustersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('name', function (Cluster $data) {
                return view('admin.datatables._link', [
                    'url' => route('admin.clusters.show', $data->id),
                    'text' => $data->name,
                ]);
            })
            ->addColumn('status', function (Cluster $data) {
                return view('admin.datatables._status-toggle', [
                    'id' => $data->id,
                    'route' => 'admin.clusters.update',
                    'param' => ['cluster' => $data->id],
                    'checked' => $data->status == 'active' ? 'checked' : '',
                ]);
            })
            ->addColumn('actions', function (Cluster $data) {
                return view('admin.datatables._actions', [
                    'param' => $data->id,
                    'route' => 'admin.clusters',
                ]);
            });
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(Cluster $model): QueryBuilder
    {
        return $model->newQuery()
            ->with([
                'company'
            ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('clusters-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>",)
            ->addTableClass('table align-middle table-striped table-row-bordered fs-6 gy-5 gs-7 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(0, 'asc')
            ->drawCallback("function() {" . file_get_contents(resource_path('views/pages/apps/user-management/users/columns/_draw-scripts.js')) . "}");
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id'),
            Column::make('company.company_name')->title('company name'),
            Column::make('name')->title('cluster name'),
            Column::make('status')->title('status'),
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
