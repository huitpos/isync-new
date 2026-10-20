<?php

namespace App\DataTables\Concerns;

use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\QueryDataTable;

trait UniformAjaxTable
{
    public function html(): HtmlBuilder
    {
        [$column, $direction] = $this->defaultOrder();

        return $this->builder()
            ->setTableId($this->tableId())
            ->columns($this->getColumns())
            ->minifiedAjax('', $this->ajaxDataScript())
            ->parameters([
                'initComplete' => 'function () { var api = this.api(); if (window.initColumnFilters) { window.initColumnFilters(api); } if (window.bindRowLinks) { window.bindRowLinks(api); } }',
                'createdRow' => 'function (row, data) { if (data && data.view_url) { row.classList.add("cursor-pointer"); } }',
            ])
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>i>")
            ->addTableClass('table align-middle table-striped table-row-bordered fs-6 gy-5')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy($column, $direction)
            ->drawCallback('function() {' . file_get_contents(resource_path('views/datatables/_common-scripts.js')) . '}');
    }

    protected function tableId(): string
    {
        return str_replace('\\', '-', strtolower(class_basename(static::class)));
    }

    protected function defaultOrder(): array
    {
        return [0, 'asc'];
    }

    protected function ajaxDataScript(): string
    {
        return <<<'JS'
            if (window.jQuery) {
                window.jQuery('[data-dt-filter]').each(function () {
                    var key = window.jQuery(this).attr('data-dt-filter');
                    if (key) {
                        data[key] = window.jQuery(this).val();
                    }
                });
            }
JS;
    }

    protected function withRelationSearch(EloquentDataTable|QueryDataTable $dataTable): EloquentDataTable|QueryDataTable
    {
        foreach ($this->getColumns() as $column) {
            $name = $column->name ?? '';
            $searchable = $column->searchable ?? true;

            if ($searchable === false || !is_string($name) || !str_contains($name, '.')) {
                continue;
            }

            [$relation, $field] = explode('.', $name, 2);
            $dataTable->filterColumn($name, function ($query, $keyword) use ($relation, $field) {
                $query->whereHas($relation, function ($relationQuery) use ($field, $keyword) {
                    $relationQuery->where($field, 'like', '%' . $keyword . '%');
                });
            });
        }

        return $dataTable;
    }
}
