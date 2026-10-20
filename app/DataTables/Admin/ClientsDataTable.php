<?php

namespace App\DataTables\Admin;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\Company;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ClientsDataTable extends DataTable
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
            ->addColumn('company_name', function (Company $data) {
                return view('admin.datatables._link', [
                    'url' => route('admin.clients.show', $data->id),
                    'text' => $data->company_name,
                ]);
            })
            ->addColumn('actions', function (Company $data) {
                return view('admin.datatables._actions', [
                    'param' => $data->id,
                    'route' => 'admin.clients',
                ]);
            }));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Company $model): QueryBuilder
    {
        return $model->newQuery()
            ->with([
                'createdBy',
                'client.user',
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
            Column::make('company_name')->title('company name'),
            Column::make('client.name')->title('owner name'),
            Column::make('phone_number')->title('contact no,'),
            Column::make('client.user.email')->title('email'),
            Column::make('pos_type')->title('type of pos'),
            Column::make('created_by.name', 'createdBy.name')->title('created by'),
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
