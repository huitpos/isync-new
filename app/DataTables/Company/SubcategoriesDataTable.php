<?php

namespace App\DataTables\Company;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\Subcategory;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class SubcategoriesDataTable extends DataTable
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
            ->addColumn('name', function (Subcategory $data) {
                if (in_array('Settings/Sub-Categories/View', $this->permissions)) {
                    return view('company.datatables._link', [
                        'url' => route('company.subcategories.show', ['companySlug' => $data->company->slug, 'subcategory' => $data->id]),
                        'text' => $data->name,
                    ]);
                } else {
                    return $data->name;
                }
            })
            ->addColumn('actions', function (Subcategory $data) {
                if (in_array('Settings/Sub-Categories/Edit', $this->permissions)) {
                    return view('company.datatables._actions', [
                        'param' => ['subcategory' => $data->id, 'companySlug' => $data->company->slug],
                        'route' => 'company.subcategories',
                    ]);
                } else {
                    return '';
                }
            })
            ->filterColumn('name', function($query, $keyword) {
                $query->where('name', 'like', "%$keyword%");
            }));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Subcategory $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('company_id', $this->company_id)
            ->with([
                'company',
                'category',
                'createdBy',
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
            Column::make('id')->visible(false)->searchable(false),
            Column::make('name')->title('Subcategory'),
            Column::make('description'),
            Column::make('category.name', 'category.name')->title('Category'),
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