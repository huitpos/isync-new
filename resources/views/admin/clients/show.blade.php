<x-default-layout>
    @section('title')
        Clients
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.clients.show', $company->company_name) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <h3>Company Registered Name</h3>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Company Name</div>
                <div class="text-gray-600">{{ $company->company_name }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Trade Name</div>
                <div class="text-gray-600">{{ $company->trade_name }}</div>
            </div>

            @if($company->logo)
            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Company Logo</div>
                <div class="text-gray-600">
                    <div class="image-input-wrapper w-125px h-125px" style="border: 1px dashed #92A0B3; background-size:contain; background-image: url('{{ Storage::disk('s3')->url($company->logo) }}');"></div>
                </div>
            </div>
            @endif

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Owner Name</div>
                <div class="text-gray-600">{{ $company->client->name }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Country</div>
                <div class="text-gray-600">{{ $company->country }}</div>
            </div>

            <div class="pb-5 fs-6">
                <div class="fw-bold mt-5">Contact No.</div>
                <div class="text-gray-600">{{ $company->phone_number }}</div>
            </div>

            <div class="separator separator-dashed my-3"></div>

            <h3>Address</h3>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Unit No./Floor Bldg.</div>
                <div class="text-gray-600">{{ $company->unit_floor_number }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Street</div>
                <div class="text-gray-600">{{ $company->street }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Region</div>
                <div class="text-gray-600">{{ $company->region->name }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Province</div>
                <div class="text-gray-600">{{ $company->province->name }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">City</div>
                <div class="text-gray-600">{{ $company->city->name }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Barangay</div>
                <div class="text-gray-600">{{ $company->barangay->name }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Type of POS</div>
                <div class="text-gray-600 text-capitalize">{{ $company->pos_type }}</div>
            </div>

            <div class="separator separator-dashed my-3"></div>

            <h3>Account Registration</h3>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Email Address</div>
                <div class="text-gray-600">{{ $company->client->user->email }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Account ID</div>
                <div class="text-gray-600">{{ $company->id }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Account Link</div>
                <div class="text-gray-600">{{ route('company.dashboard', ['companySlug' => $company->slug]) }}</div>
            </div>

            {{-- <div class="mb-5 fs-6">
                <div class="form-check form-switch form-check-custom form-check-solid me-10 mt-5">
                    <input data-action="{{ route('admin.companies.update', ['company' => $company->id]) }}" data-csrf="{{ csrf_token() }}" class="form-check-input h-30px w-60px status-toggle" type="checkbox" {{ $company->status == 'active' ? 'checked' : '' }}/>
                    <label class="ms-sm-2">
                        Status
                    </label>
                </div>
            </div>

            <div class="pb-1 fs-6">
                <a href="{{ route('admin.clients.edit', ['client' => $company->id]) }}" class="btn btn-secondary">Edit</a>
            </div> --}}
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <h2>Branches</h2>
            </div>
            <!--end::Card title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <div class="d-flex align-items-center position-relative my-1">
                    {!! getIcon('magnifier', 'fs-3 position-absolute ms-5') !!}
                    <input type="text" data-kt-user-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Search" id="searchBar"/>
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
                window.LaravelDataTables['company-branch-table'].search(this.value).draw();
            });
        </script>
    @endpush
</x-default-layout>
