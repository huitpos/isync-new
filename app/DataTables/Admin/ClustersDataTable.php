<?php

namespace App\DataTables\Admin;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\Cluster;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ClustersDataTable extends DataTable
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
            }));
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
    

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
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
