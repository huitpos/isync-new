<x-default-layout>

    @section('title')
        Inventory Processing History
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('inventory-tracking.history', $company) }}
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2>History</h2>
            </div>

            <div class="card-toolbar">
                <a href="{{ route('inventory-tracking.index') }}" class="btn btn-light">
                    {!! getIcon('arrow-left', 'fs-2', '', 'i') !!}
                    Back to Tracking
                </a>
            </div>
        </div>

        <div class="card-body py-4">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label" for="movement_type">Movement Type</label>
                    <select id="movement_type" class="form-select form-select-solid">
                        <option value="">All Types</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="branch_id">Branch</label>
                    <select id="branch_id" class="form-select form-select-solid">
                        @if ($branches->count() > 1)
                            <option value="">All Branches</option>
                        @endif
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="product_id">Product</label>
                    <select
                        id="product_id"
                        data-control="select2"
                        data-ajax-url="/ajax/get-products?company_id={{ auth()->user()->company_id }}"
                        data-placeholder="All Products"
                        class="form-control form-control-solid select2-ajax"
                        data-minimum-input="3"
                    ></select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="clearFilters" class="btn btn-light w-100">
                        {!! getIcon('arrows-circle', 'fs-2', '', 'i') !!}
                        Clear Filters
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function () {
            var $productSelect = $('#product_id');

            if ($productSelect.length && !$productSelect.data('select2')) {
                $productSelect.select2({
                    placeholder: $productSelect.data('placeholder') || 'All Products',
                    allowClear: true,
                    ajax: {
                        url: $productSelect.data('ajax-url'),
                        dataType: 'json',
                        delay: 250,
                        cache: true,
                        data: function (params) {
                            return { term: params.term };
                        }
                    }
                });
            }

            var historyTable = $('#inventory-history-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('inventory-tracking.history') !!}',
                    data: function (d) {
                        d.movement_type = $('#movement_type').val();
                        d.branch_id = $('#branch_id').val();
                        d.product_id = $('#product_id').val();
                    }
                },
                columnDefs: [
                    {
                        targets: 0,
                        visible: false,
                        searchable: false
                    }
                ],
                columns: [
                    { data: 'id' },
                    { data: 'product_display', orderable: false, searchable: false },
                    { data: 'movement_type', orderable: false, searchable: false },
                    { data: 'branch.name', orderable: false, searchable: false },
                    { data: 'previous_qty', orderable: false, searchable: false },
                    { data: 'new_qty', orderable: false, searchable: false },
                    { data: 'processed_by_name', orderable: false, searchable: false },
                    { data: 'processed_at', orderable: false, searchable: false },
                ],
                order: [[7, 'desc']]
            });

            function reloadHistoryTable() {
                historyTable.ajax.reload();
            }

            $('#movement_type, #branch_id').on('change', reloadHistoryTable);
            $productSelect.on('change', reloadHistoryTable);

            $('#clearFilters').on('click', function () {
                $('#movement_type').val('').trigger('change');
                $('#branch_id').val('').trigger('change');
                $productSelect.val(null).trigger('change');
            });
        });
    </script>
    @endpush

</x-default-layout>
