<?php

namespace App\DataTables\Company;

use App\DataTables\Concerns\UniformAjaxTable;
use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class JournalsDataTable extends DataTable
{
    use UniformAjaxTable;

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('number', function (JournalEntry $row) {
                $url = route('company.journals.show', [
                    'companySlug' => $this->company_slug,
                    'id' => $row->id,
                ]);

                return '<a href="'.e($url).'">'.e($row->number).'</a>';
            })
            ->editColumn('entry_date', fn (JournalEntry $row) => $row->entry_date->format('Y-m-d'))
            ->editColumn('source_type', fn (JournalEntry $row) => str_replace('_', ' ', $row->source_type))
            ->editColumn('status', fn (JournalEntry $row) => ucfirst($row->status))
            ->rawColumns(['number']);
    }

    public function query(JournalEntry $model): QueryBuilder
    {
        return $model->newQuery()->where('company_id', $this->company_id);
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::make('entry_date')->title('Date'),
            Column::make('number'),
            Column::make('source_type')->title('Source'),
            Column::make('status'),
            Column::make('description'),
        ];
    }

    protected function defaultOrder(): array
    {
        return [1, 'desc'];
    }

    protected function filename(): string
    {
        return 'Journals_'.date('YmdHis');
    }
}
