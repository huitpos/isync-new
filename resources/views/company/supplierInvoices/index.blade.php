<x-default-layout>

    @section('title')
        Supplier Invoices
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.supplierInvoices.index', $company) }}
    @endsection

    <div class="card">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" id="searchBar" class="form-control form-control-solid w-250px ps-12" placeholder="Search" />
                </div>
            </div>

            <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                <select id="branch_id" class="form-control form-control-solid w-100 mw-250px">
                    <option value="">Branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>

                <select id="status" class="form-control form-control-solid w-100 mw-250px">
                    <option value="">Status</option>
                    <option value="matched">Matched</option>
                    <option value="variance">Variance</option>
                    <option value="accepted">Accepted</option>
                    <option value="void">Void</option>
                </select>

                <a href="{{ route('company.supplier-invoices.create', ['companySlug' => $company->slug]) }}" class="btn btn-primary">Add Supplier Invoice</a>
            </div>
        </div>

        <div class="card-body py-4">
            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            var table = $('#supplier-invoices-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('company.supplier-invoices.index', ['companySlug' => $company->slug]) !!}',
                    data: function (d) {
                        d.status = $('#status').val();
                        d.branch_id = $('#branch_id').val();
                        d.search = $('#searchBar').val();
                    }
                },
                columnDefs: [
                    {
                        target: 0,
                        visible: false,
                        searchable: false
                    }
                ],
                columns: [
                    { data: 'id' },
                    {
                        data: 'invoice_number',
                        render: function(data, type, row) {
                            return '<a href="' + row.view_url + '">' + row.invoice_number + '</a>';
                        }
                    },
                    { data: 'purchase_order.po_number' },
                    { data: 'supplier.name' },
                    { data: 'branch.name' },
                    { data: 'invoice_date' },
                    {
                        data: 'total_amount',
                        className: 'text-end',
                        render: function(data) {
                            return parseFloat(data).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    },
                    { data: 'status' },
                    { data: 'created_by.name' },
                    {
                        data: 'created_at',
                        render: function(data) {
                            return moment(data).format("YYYY-MM-DD hh:mm A");
                        }
                    },
                ]
            });

            function reloadDataTable() {
                table.ajax.reload();
            }

            $('#branch_id, #status').on('change', function() {
                reloadDataTable();
            });

            $('#searchBar').on('keyup', function() {
                reloadDataTable();
            });
        </script>
    @endpush
</x-default-layout>
