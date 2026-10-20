<?php

namespace App\DataTables\Company;


use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\SupplierInvoice as Model;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SupplierInvoicesDataTable extends DataTable
{
    use UniformAjaxTable;

    
    protected function defaultOrder(): array
    {
        return [0, 'desc'];
    }
public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $companySlug = $this->company_slug;

        return $this->withRelationSearch((new EloquentDataTable($query))
            ->editColumn('created_at', function (Model $data) {
                return $data->created_at;
            })
            ->addColumn('view_url', function (Model $data) use ($companySlug) {
                return route('company.supplier-invoices.show', [
                    'companySlug' => $companySlug,
                    'supplier_invoice' => $data->id,
                ]);
            }));
    }

    public function query(Model $model): QueryBuilder
    {
        $companyId = $this->company_id;
        $query = $model->newQuery()
            ->with(['createdBy', 'purchaseOrder', 'branch', 'supplier'])
            ->whereHas('branch', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->branch_id) {
            $query->where('branch_id', $this->branch_id);
        }

        $search = $this->search;
        if (is_string($search) && $search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('invoice_number', 'like', '%' . $search . '%')
                    ->orWhereHas('purchaseOrder', function ($query) use ($search) {
                        $query->where('po_number', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('supplier', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        return $query;
    }

    

    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::make('invoice_number')->title('Invoice Number'),
            Column::make('purchase_order.po_number')->title('PO Number'),
            Column::make('supplier.name')->title('Supplier'),
            Column::make('branch.name')->title('Branch'),
            Column::make('invoice_date')->title('Invoice Date'),
            Column::make('total_amount')->title('Amount'),
            Column::make('status'),
            Column::make('created_by.name')->title('Created By'),
            Column::make('created_at'),
        ];
    }

    protected function filename(): string
    {
        return 'SupplierInvoices_' . date('YmdHis');
    }
}
