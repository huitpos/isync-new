<?php

namespace App\DataTables\Admin;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\Branch;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class BranchesDataTable extends DataTable
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
            }));
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
    

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
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
