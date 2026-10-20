<?php

namespace App\DataTables\Company;


use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\ProductPhysicalCount as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ProductPhysicalCountsDataTable extends DataTable
{
    use UniformAjaxTable;

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $companySlug = $this->company_slug;

        return $this->withRelationSearch((new EloquentDataTable($query))
            ->editColumn('sto_number', function (Model $data) use($companySlug) {
                if (in_array('Inventory/Product Physical Count/View', $this->permissions)) {
                    return '<a href="' . route('company.product-physical-counts.show', [
                        'companySlug' => $companySlug,
                        'product_physical_count' => $data->id
                    ]) . '">' . $data->pcount_number . '</a>';
                } else {
                    return $data->id;
                }
            })
            ->editColumn('created_at', function (Model $data) {
                return $data->created_at;
            })
            ->rawColumns(['sto_number']));
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(Model $model): QueryBuilder
    {
        $companyId = $this->company_id;
        $query =  $model->newQuery()
            ->with([
                'createdBy',
                'branch'
            ])
            ->whereHas('branch.company', function ($query) use ($companyId) {
                $query->where('companies.id', $companyId);
            });

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->branch_id) {
            $query->where('branch_id', $this->branch_id);
        }

        return $query;
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
            Column::make('pcount_number')->title('pcount number'),
            Column::make('branch.name')->title('Branch'),
            Column::make('created_by.name', 'createdBy.name')->title('created by'),
            Column::make('status'),
            Column::make('created_at'),
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