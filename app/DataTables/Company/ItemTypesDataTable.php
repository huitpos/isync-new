<?php

namespace App\DataTables\Company;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\ItemType;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ItemTypesDataTable extends DataTable
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
            ->addColumn('name', function (ItemType $data) {
                if (in_array('Settings/Item Types/View', $this->permissions)) {
                    return view('company.datatables._link', [
                        'url' => route('company.item-types.show', ['companySlug' => $data->company->slug, 'item_type' => $data->id]),
                        'text' => $data->name,
                    ]);
                } else {
                    return $data->name;
                }
            })
            ->addColumn('actions', function (ItemType $data) {
                if (in_array('Settings/Item Types/Edit', $this->permissions)) {
                    return view('company.datatables._actions', [
                        'param' => ['item_type' => $data->id, 'companySlug' => $data->company->slug],
                        'route' => 'company.item-types',
                    ]);
                } else {
                    return '';
                }
            }));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ItemType $model): QueryBuilder
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
            Column::make('created_by.name', 'createdBy.name')->title('Created By'),
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