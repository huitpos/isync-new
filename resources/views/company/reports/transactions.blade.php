<x-default-layout>

    @section('title')
        Suppliers
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.suppliers.index', $company) }}
    @endsection

    <div class="card">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" id="searchBar" class="form-control form-control-solid w-250px ps-12" placeholder="Search" />
                </div>
                <!--end::Search-->
                <!--begin::Export buttons-->
                <div id="kt_ecommerce_report_returns_export" class="d-none"></div>
                <!--end::Export buttons-->
            </div>
            <!--end::Card title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                <!--begin::Daterangepicker-->
                <input class="form-control form-control-solid w-100 mw-250px" placeholder="Pick date range" id="customFilter" />
                <!--end::Daterangepicker-->
                <!--begin::Export dropdown-->
                <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                <i class="ki-duotone ki-exit-up fs-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>Export Report</button>
                <!--begin::Menu-->
                <div id="kt_ecommerce_report_returns_export_menu" class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4" data-kt-menu="true">
                    <!--begin::Menu item-->
                    <div class="menu-item px-3">
                        <a href="#" class="menu-link px-3" data-kt-ecommerce-export="copy">Copy to clipboard</a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-3">
                        <a href="#" class="menu-link px-3" data-kt-ecommerce-export="excel">Export as Excel</a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-3">
                        <a href="#" class="menu-link px-3" data-kt-ecommerce-export="csv">Export as CSV</a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-3">
                        <a href="#" class="menu-link px-3" data-kt-ecommerce-export="pdf">Export as PDF</a>
                    </div>
                    <!--end::Menu item-->
                </div>
                <!--end::Menu-->
                <!--end::Export dropdown-->
            </div>
            <!--end::Card toolbar-->
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
                    url: '{!! route('company.reports.transactions', ['companySlug' => $company->slug]) !!}',
                    data: function (d) {
                        d.filter_value = $('#filter-field').val(); // Additional filter field value
                    }
                },
                columns: [
                    {
                        data: 'created_at',
                        render: function(data, type, row) {
                            return moment(data).format("YYYY-MM-DD hh:mm A");
                        }
                    },
                    { data: 'pos_machine_id' },
                    { data: 'receipt_number' },
                    { data: 'cashier_name' },
                    { data: 'shift_number' },
                    { data: 'gross_sales' },
                    { data: 'net_sales' },
                    { data: 'vatable_sales' },
                    { data: 'vat_amount' },
                    { data: 'vat_exempt_sales' },
                    { data: 'discount_amount' },
                    { data: 'payment_types' },
                    { data: 'is_void' },
                    { data: 'items_count' },
                    { data: 'tender_amount' },
                    { data: 'service_charge' },
                    { data: 'type' },
                    { data: 'change' },
                    { data: 'approver' }
                ]
            });

            document.getElementById('searchBar').addEventListener('keyup', function () {
                window.LaravelDataTables['clusters-table'].search(this.value).draw();
            });
        </script>
    @endpush

</x-default-layout>
