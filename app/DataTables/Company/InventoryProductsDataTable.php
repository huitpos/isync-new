<?php

namespace App\DataTables\Company;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\Product;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class InventoryProductsDataTable extends DataTable
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
            ->addColumn('name', function (Product $data) {
                if (in_array('Inventory/Products/View', $this->permissions)) {
                    return view('company.datatables._link', [
                        'url' => route('company.branch-inventory.show', [
                            'companySlug' => $data->company->slug,
                            'productId' => $data->id,
                            'branchId' => $this->branch_id
                        ]),
                        'text' => $data->name,
                    ]);
                } else {
                    return $data->name;
                }
            })
            ->addColumn('stock', function (Product $data) {
                return number_format($data['branches'][0]['pivot']['stock'] ?? 0, 2);
            }));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Product $model): QueryBuilder
    {
        $branchId = $this->branch_id;

        return $model->newQuery()
            ->where('company_id', $this->company_id)
            ->with([
                'itemType',
                'uom',
                'createdBy',
                'branches' => function ($query) use ($branchId) {
                    $query->where('branches.id', $branchId);
                }
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
            Column::make('name')->title('Product Name'),
            Column::make('description'),
            Column::make('code')->title('Item Code'),
            Column::make('stock'),
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