<?php

namespace App\DataTables\Company;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\ProductCountLog as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;

class ProductCountHistoryDataTable extends DataTable
{
    use UniformAjaxTable;

    
    protected function defaultOrder(): array
    {
        return [4, 'desc'];
    }
/**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return $this->withRelationSearch((new EloquentDataTable($query))
            ->editColumn('object_type', function (Model $data) {
                return Str::title(str_replace('_', ' ', $data['object_type']));
            })
            ->editColumn('created_at', function (Model $data) {
                return $data->created_at ? $data->created_at->format('Y-m-d') : '';
            }));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Model $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('branch_id', $this->branch_id)
            ->where('product_id', $this->product_id)
            ->with([
                'product'
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
            Column::make('object_type'),
            Column::make('old_quantity'),
            Column::make('new_quantity'),
            Column::make('created_at')->title('Date'),
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