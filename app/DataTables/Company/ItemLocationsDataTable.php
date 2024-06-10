<?php

namespace App\DataTables\Company;

use App\Models\ItemLocation as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ItemLocationsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
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
            });
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
                'region',
                'province',
                'city',
                'barangay'
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
            Column::make('name'),
            Column::make('unit_floor_number'),
            Column::make('street'),
            Column::make('region.name')->title('region'),
            Column::make('province.name')->title('province'),
            Column::make('city.name')->title('city'),
            Column::make('barangay.name')->title('barangay'),
            Column::make('status')->title('status'),
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