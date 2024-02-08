<x-default-layout>
    @section('title')
        {{ $branch->name }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.branches.show', $branch) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Company Name</div>
                <div class="text-gray-600">{{ $branch->company->company_name }}</div>
            </div>

            <div class="separator separator-dashed my-3"></div>

            <h3>Branch Information</h3>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Branch Name</div>
                <div class="text-gray-600">{{ $branch->name }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Owner's Name</div>
                <div class="text-gray-600">{{ $branch->company->client->name }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Branch Code</div>
                <div class="text-gray-600">{{ $branch->code }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Branch Cluster</div>
                <div class="text-gray-600">{{ $branch->cluster->name }}</div>
            </div>

            <div class="separator separator-dashed my-3"></div>

            <h3>Branch Address</h3>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Unit No./Floor Bldg.</div>
                <div class="text-gray-600">{{ $branch->unit_floor_number }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Street</div>
                <div class="text-gray-600">{{ $branch->street }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Region</div>
                <div class="text-gray-600">{{ $branch->region->name }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Province</div>
                <div class="text-gray-600">{{ $branch->province->name }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">City</div>
                <div class="text-gray-600">{{ $branch->city->name }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Barangay</div>
                <div class="text-gray-600">{{ $branch->barangay->name }}</div>
            </div>

            <div class="separator separator-dashed my-3"></div>

            <h3>Account Registration</h3>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Branch Link</div>
                <div class="text-gray-600">{{ route('branch.dashboard', ['companySlug' => $branch->company->slug, 'branchSlug' => $branch->slug]) }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Receipt Header</div>
                <div class="text-gray-600">{{ $branch->receipt_header }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Receipt Bottom Text</div>
                <div class="text-gray-600">{{ $branch->receipt_bottom_text }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <h2>Machine Details</h2>
            </div>
            <!--end::Card title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <div class="d-flex align-items-center position-relative my-1">
                    {!! getIcon('magnifier', 'fs-3 position-absolute ms-5') !!}
                    <input type="text" data-kt-user-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Search machine" id="searchBar"/>
                </div>

                <div class="d-flex justify-content-end m-3" data-kt-user-table-toolbar="base">
                    <a href="{{ route('admin.machines.create', ['branchId' => $branch->id]) }}" class="btn btn-primary">
                        {!! getIcon('plus', 'fs-2', '', 'i') !!}
                        Add Machine
                    </a>
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
                window.LaravelDataTables['branch-machines-table'].search(this.value).draw();
            });
        </script>
    @endpush
</x-default-layout>
