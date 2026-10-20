<?php

namespace App\DataTables\Company;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\PaymentType as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class PaymentTypesDataTable extends DataTable
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
            ->addColumn('name', function (Model $data) {
                if (in_array('Settings/Payment Types/View', $this->permissions)) {
                    if ($data->id != 1) {
                        return view('company.datatables._link', [
                            'url' => route('company.payment-types.show', ['companySlug' => $data->company->slug, 'payment_type' => $data->id]),
                            'text' => $data->name,
                        ]);
                    } else {
                        return $data->name;
                    }
                } else {
                    return $data->name;
                }
            })
            ->addColumn('actions', function (Model $data) {
                if (in_array('Settings/Payment Types/Edit', $this->permissions)) {
                    if ($data->id != 1) {
                        return view('company.datatables._actions', [
                            'param' => ['payment_type' => $data->id, 'companySlug' => $data->company->slug],
                            'route' => 'company.payment-types',
                        ]);
                    } else {
                        return '';
                    }
                } else {
                    return '';
                }
            }));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Model $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('company_id', $this->company_id)
            ->orWhere('company_id', null)
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
            Column::make('id')->visible(false),
            Column::make('name')->title('Payment Type'),
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
