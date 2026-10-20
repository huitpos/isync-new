<?php

namespace App\DataTables\Branch;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\DeliveryLocation as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class DeliveryLocationsDataTable extends DataTable
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
            ->addColumn('actions', function (Model $data) use ($branchSlug, $companySlug) {
                return view('branch.datatables._actions', [
                    'param' => ['delivery_location' => $data->id, 'companySlug' => $companySlug, 'branchSlug' => $branchSlug],
                    'route' => 'branch.delivery-locations',
                ]);
            }));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Model $model): QueryBuilder
    {
        $branchId = $this->branch_id;
        return $model->newQuery()
            ->with([
                'region',
                'province',
                'city',
                'barangay'
            ])
            ->whereHas('branch', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });
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