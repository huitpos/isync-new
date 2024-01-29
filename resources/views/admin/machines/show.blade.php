<x-default-layout>
    @section('title')
        {{ $machine->name }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.clusters.show', $machine->name) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-stack fs-4 py-3">
                <div class="fw-bold">
                    {{ $machine->name }}
                </div>
            </div>

            <div class="separator separator-dashed my-3"></div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Device Name</div>
                <div class="text-gray-600">{{ $machine->name }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Product Key</div>
                <div class="text-gray-600">{{ $machine->product_key }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Machine No.</div>
                <div class="text-gray-600">{{ $machine->id }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Serial No.</div>
                <div class="text-gray-600">{{ $machine->serial_number }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Machine Identification Number</div>
                <div class="text-gray-600">{{ $machine->min }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Receipt Header</div>
                <div class="text-gray-600">{{ $machine->receipt_header }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Receipt Buttom Text</div>
                <div class="text-gray-600">{{ $machine->receipt_bottom_text }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Permit No.</div>
                <div class="text-gray-600">{{ $machine->permit_number }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Accreditation No.</div>
                <div class="text-gray-600">{{ $machine->accreditation_number }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Valid From</div>
                <div class="text-gray-600">{{ $machine->valid_from }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Valid Until</div>
                <div class="text-gray-600">{{ $machine->valid_to }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">TIN</div>
                <div class="text-gray-600">{{ $machine->tin }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Cash Limit</div>
                <div class="text-gray-600">{{ $machine->limit_amount }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">VAT</div>
                <div class="text-gray-600">{{ $machine->vat }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Machine Type</div>
                <div class="text-gray-600">{{ $machine->type }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <h2>Device Logs</h2>
            </div>
            <!--end::Card title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <div class="d-flex align-items-center position-relative my-1">
                    {!! getIcon('magnifier', 'fs-3 position-absolute ms-5') !!}
                    <input type="text" data-kt-user-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Search branch" id="searchBar"/>
                </div>
            </div>
        </div>

        <div class="card-body py-4">
            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts() }}
        <script>
            document.getElementById('searchBar').addEventListener('keyup', function () {
                window.LaravelDataTables['machine-devices-table'].search(this.value).draw();
            });
        </script>
    @endpush
</x-default-layout>
