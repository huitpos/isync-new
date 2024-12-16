<x-default-layout>

    @section('title')
        Purchase Deliveries
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.purchaseDeliveries.index', $company) }}
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
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>

        <div class="card-body py-4">
            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    @push('scripts')
        {{-- {{ $dataTable->scripts() }} --}}
        <script>
            document.getElementById('searchBar').addEventListener('keyup', function () {
                window.LaravelDataTables['clusters-table'].search(this.value).draw();
            });

            var table = $('#clusters-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('company.purchase-deliveries.index', ['companySlug' => $company->slug]) !!}',
                    data: function (d) {
                        d.status = $('#status').val();
                        d.branch_id = $('#branch_id').val();
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
                        data: 'pr_number',
                        render: function(data, type, row) {
                            @if (in_array('Procurement/Purchase Deliveries/View', $permissions))
                                return '<a href="' + row.view_url + '">' + row.pd_number + '</a>';
                            @else
                                return row.pd_number;
                            @endif
                        }
                    },
                    { data: 'purchase_order.po_number' },
                    { data: 'sales_invoice_number' },
                    { data: 'delivery_number' },
                    { data: 'branch.name' },
                    { data: 'created_by.name' },
                    { data: 'status' },
                    {
                        data: 'created_at',
                        render: function(data, type, row) {
                            return moment(data).format("YYYY-MM-DD hh:mm A");
                        }
                    },
                ]
            });

            function reloadDataTable() {
                table.ajax.reload(); // This will trigger the DataTable to reload its data
            }

            $('#date_from, #branch_id, #status').on('change', function() {
                reloadDataTable(); // Reload DataTable when either date input changes
            });
        </script>
    @endpush

</x-default-layout>
