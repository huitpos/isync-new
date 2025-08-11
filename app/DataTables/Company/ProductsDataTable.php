<?php

namespace App\DataTables\Company;

use App\Models\Product;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ProductsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('name', function (Product $data) {
                if (in_array('Settings/Products/View', $this->permissions)) {
                    return view('company.datatables._link', [
                        'url' => route('company.products.show', ['companySlug' => $data->company->slug, 'product' => $data->id]),
                        'text' => $data->name,
                    ]);
                } else {
                    return $data->name;
                }
            })
            ->editColumn('cost', function (Product $data) {
                return number_format($data->cost, 2);
            })
            ->editColumn('srp', function (Product $data) {
                return number_format($data->srp, 2);
            })
            ->filterColumn('name', function($query, $keyword) {
                return $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->addColumn('actions', function (Product $data) {
                if (in_array('Settings/Products/Edit', $this->permissions)) {
                    return view('company.datatables._actions', [
                        'param' => ['product' => $data->id, 'companySlug' => $data->company->slug],
                        'route' => 'company.products',
                    ]);
                } else {
                    return '';
                }
            });
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(Product $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('company_id', $this->company_id)
            ->with([
                'itemType',
                'uom',
                'deliveryUom',
                'createdBy'
            ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('clusters-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>i>",)
            ->addTableClass('table align-middle table-striped table-row-bordered fs-6 gy-5 gs-7 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(0, 'asc')
            ->drawCallback("function() {" . file_get_contents(resource_path('views/datatables/_common-scripts.js')) . "}");
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::make('name')->title('Product Name'),
            Column::make('description')->searchable(false),
            Column::make('item_type.name', 'itemType.name')->title('Item Type')->searchable(false),
            Column::make('uom.name')->title('UOM')->searchable(false),
            Column::make('delivery_uom.name', 'deliveryUom.name')->title('Delivery UOM')->searchable(false),
            Column::make('code')->title('Item Code')->searchable(false),
            Column::make('cost')->title('cost')->searchable(false),
            Column::make('srp')->title('srp')->searchable(false),
            Column::make('created_by.name', 'createdBy.name')->title('created by')->searchable(false),
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