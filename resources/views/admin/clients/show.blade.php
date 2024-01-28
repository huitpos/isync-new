<x-default-layout>
    @section('title')
        Clients
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.clients.show', $company->company_name) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-stack fs-4 py-3">
                <div class="fw-bold">
                    {{ $company->company_name }}
                </div>
            </div>

            <div class="separator separator-dashed my-3"></div>

            <h3>Company Registered Name</h3>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Company Name</div>
                <div class="text-gray-600">{{ $company->company_name }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Trade Name</div>
                <div class="text-gray-600">{{ $company->trade_name }}</div>
            </div>

            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Company Logo</div>
                <div class="text-gray-600">{{ $company->logo }}</div>
            </div>

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

            <div class="mb-5 fs-6">
                <div class="form-check form-switch form-check-custom form-check-solid me-10 mt-5">
                    <input data-action="{{ route('admin.companies.update', ['company' => $company->id]) }}" data-csrf="{{ csrf_token() }}" class="form-check-input h-30px w-60px status-toggle" type="checkbox" {{ $company->status == 'active' ? 'checked' : '' }}/>
                    <label class="ms-sm-2">
                        Status
                    </label>
                </div>
            </div>

            <div class="pb-1 fs-6">
                <a href="{{ route('admin.clients.edit', ['client' => $company->id]) }}" class="btn btn-secondary">Edit</a>
            </div>
        </div>
    </div>
</x-default-layout>
