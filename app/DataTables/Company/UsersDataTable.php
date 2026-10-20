<?php

namespace App\DataTables\Company;


use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\User as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class UsersDataTable extends DataTable
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
            ->addColumn('roles', function (Model $data) {
                return str_replace('_', ' ', $data->getRoleNames()->implode(', '));
            })
            ->addColumn('status', function (Model $data) {
                return $data->is_active ? 'Active' : 'Inactive';
            })
            ->editColumn('created_at', function (Model $data) {
                return \Carbon\Carbon::parse($data->created_at)->format('d/m/Y H:i:s');;
            })
            ->addColumn('actions', function (Model $data) {
                return view('company.datatables._actions', [
                    'param' => ['user' => $data->id, 'companySlug' => $data->company->slug],
                    'route' => 'company.users',
                ]);
            }));
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(Model $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['createdBy'])
            ->where('company_id', $this->company_id);
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
            Column::make('name'),
            Column::make('email'),
            Column::make('roles'),
            Column::make('created_by.name', 'createdBy.name')->title('created by'),
            Column::make('created_at')->title('created at'),
            Column::make('status'),
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