<?php

namespace App\DataTables\Company;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\Account;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AccountsDataTable extends DataTable
{
    use UniformAjaxTable;

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return $this->withRelationSearch((new EloquentDataTable($query))
            ->editColumn('code', function (Account $row) {
                $url = route('company.accounts.edit', [
                    'companySlug' => $this->company_slug,
                    'id' => $row->id,
                ]);

                return '<a href="'.e($url).'">'.e($row->code).'</a>';
            })
            ->editColumn('type', fn (Account $row) => ucfirst($row->type))
            ->editColumn('is_postable', fn (Account $row) => $row->is_postable ? 'Yes' : 'No')
            ->editColumn('is_active', fn (Account $row) => $row->is_active ? 'Active' : 'Inactive')
            ->rawColumns(['code']));
    }

    public function query(Account $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('company_id', $this->company_id)
            ->with('parent');
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::make('code'),
            Column::make('name'),
            Column::make('type'),
            Column::make('parent.name', 'parent.name')->title('Parent'),
            Column::make('is_postable')->title('Postable'),
            Column::make('is_active')->title('Status'),
        ];
    }

    protected function defaultOrder(): array
    {
        return [1, 'asc'];
    }

    protected function filename(): string
    {
        return 'Accounts_'.date('YmdHis');
    }
}
