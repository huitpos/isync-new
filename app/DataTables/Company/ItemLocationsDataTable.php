<?php

namespace App\DataTables\Company;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\ItemLocation as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ItemLocationsDataTable extends DataTable
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
            ->addColumn('name', function (Model $data) {
                // if (in_array('Settings/Suppliers/View', $this->permissions)) {
                    return view('company.datatables._link', [
                        // 'url' => route('company.item-locations.show', ['companySlug' => $data->company->slug, 'item_location' => $data->id]),
                        'url' => '#',
                        'text' => $data->name,
                    ]);
                // } else {
                //     return $data->name;
                // }
            })
            ->addColumn('actions', function (Model $data) {
                // if (in_array('Settings/Suppliers/Edit', $this->permissions)) {
                    return view('company.datatables._actions', [
                        'param' => ['item_location' => $data->id, 'companySlug' => $data->company->slug],
                        'route' => 'company.item-locations',
                    ]);
                // } else {
                //     return '';
                // }
            })
            ->editColumn('created_at', function (Model $data) {
                return $data->created_at;
            }));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Model $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('company_id', $this->company_id)
            ->with([
                'company',
                'createdBy'
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
            Column::make('name'),
            Column::make('created_by.name', 'createdBy.name')->title('created by'),
            Column::make('status')->title('status'),
            Column::make('created_at'),
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
