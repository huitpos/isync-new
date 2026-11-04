<x-default-layout>

    @section('title')
        Stock Master List
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('inventory-tracking.master-list', $company) }}
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2>Stock Master List</h2>
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
                <div class="col-md-4">
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
                    <label class="form-label" for="product_name">Product Name</label>
                    <input type="text" id="product_name" class="form-control form-control-solid" placeholder="Search product name...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="clearFilters" class="btn btn-light w-100">
                        {!! getIcon('arrows-circle', 'fs-2', '', 'i') !!}
                        Clear Filters
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="exportMasterList" class="btn btn-primary w-100">
                        {!! getIcon('file-down', 'fs-2', '', 'i') !!}
                        Export to Excel
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
            var masterListTable = $('#branch-product-master-list-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('inventory-tracking.master-list') !!}',
                    data: function (d) {
                        d.branch_id = $('#branch_id').val();
                        d.product_name = $('#product_name').val();
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
                    { data: 'product_display', orderable: false },
                    { data: 'branch_name', orderable: false },
                    { data: 'stock' },
                    { data: 'cost' },
                    { data: 'price' },
                ],
                order: [[3, 'asc']]
            });

            var productNameTimer = null;

            $('#branch_id').on('change', function () {
                masterListTable.ajax.reload();
            });

            $('#product_name').on('keyup', function () {
                clearTimeout(productNameTimer);
                productNameTimer = setTimeout(function () {
                    masterListTable.ajax.reload();
                }, 300);
            });

            $('#clearFilters').on('click', function () {
                $('#product_name').val('');
                $('#branch_id').val('').trigger('change');
            });

            $('#exportMasterList').on('click', function () {
                var params = new URLSearchParams();
                var branchId = $('#branch_id').val();
                var productName = $('#product_name').val();

                if (branchId) {
                    params.set('branch_id', branchId);
                }

                if (productName) {
                    params.set('product_name', productName);
                }

                var exportUrl = '{!! route('inventory-tracking.master-list.export') !!}';
                window.location.href = params.toString() ? exportUrl + '?' + params.toString() : exportUrl;
            });
        });
    </script>
    @endpush

</x-default-layout>
