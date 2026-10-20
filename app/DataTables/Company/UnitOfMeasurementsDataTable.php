<?php

namespace App\DataTables\Company;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\UnitOfMeasurement;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class UnitOfMeasurementsDataTable extends DataTable
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
            ->addColumn('name', function (UnitOfMeasurement $data) {
                if (in_array('Settings/Unit of Measurements/View', $this->permissions)) {
                    return view('company.datatables._link', [
                        'url' => route('company.unit-of-measurements.show', ['companySlug' => $data->company->slug, 'unit_of_measurement' => $data->id]),
                        'text' => $data->name,
                    ]);
                } else {
                    return $data->name;
                }
            })
            ->addColumn('actions', function (UnitOfMeasurement $data) {
                if (in_array('Settings/Unit of Measurements/Edit', $this->permissions)) {
                    return view('company.datatables._actions', [
                        'param' => ['unit_of_measurement' => $data->id, 'companySlug' => $data->company->slug],
                        'route' => 'company.unit-of-measurements',
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
    public function query(UnitOfMeasurement $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('company_id', $this->company_id)
            ->with([
                'company',
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
            Column::make('name'),
            Column::make('description'),
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