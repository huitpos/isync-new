<?php

namespace App\DataTables\Branch;


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
        $branchSlug = $this->branch_slug;
        $companySlug = $this->company_slug;

        return $this->withRelationSearch((new EloquentDataTable($query))
            ->editColumn('sto_number', function (Model $data) use($companySlug, $branchSlug) {
                return '<a href="' . route('branch.product-physical-counts.show', [
                    'companySlug' => $companySlug,
                    'branchSlug' => $branchSlug,
                    'product_physical_count' => $data->id
                ]) . '">' . $data->pcount_number . '</a>';
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
        $branchId = $this->branch_id;

        $query = $model->newQuery()
            ->with([
                'createdBy',
                'branch'
            ])
            ->whereHas('branch', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });

        if ($this->status) {
            $query->where('status', $this->status);
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
            Column::make('branch.name')->title('Destination Branch'),
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