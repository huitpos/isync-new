<?php

namespace App\DataTables\Company;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\DiscountType;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class DiscountTypesDataTable extends DataTable
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
            ->addColumn('name', function (DiscountType $data) {
                if (empty($data->company_id)) {
                    return $data->name;
                }

                if (in_array('Settings/Discount Types/View', $this->permissions)) {
                    return view('company.datatables._link', [
                        'url' => route('company.discount-types.show', ['companySlug' => $data->company->slug, 'discount_type' => $data->id]),
                        'text' => $data->name,
                    ]);
                } else {
                    return $data->name;
                }
            })
            ->addColumn('actions', function (DiscountType $data) {
                if (empty($data->company_id)) {
                    return '';
                }

                if (in_array('Settings/Discount Types/Edit', $this->permissions)) {
                    return view('company.datatables._actions', [
                        'param' => ['discount_type' => $data->id, 'companySlug' => $data->company->slug],
                        'route' => 'company.discount-types',
                    ]);
                } else {
                    return '';
                }
            }));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(DiscountType $model): QueryBuilder
    {
        return $model->newQuery()
            ->where(function ($query) {
                $query->where('company_id', $this->company_id)
                    ->orWhereNull('company_id');
            })
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
            Column::make('name')->title('Discount Type'),
            Column::make('description'),
            Column::make('type'),
            Column::make('discount'),
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