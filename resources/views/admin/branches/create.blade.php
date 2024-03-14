<x-default-layout>

    @section('title')
        Create a new branch
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.branches.create') }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('admin.branches.store') }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Company Name</label>
                    <select id="company_id" name="company_id" data-control="select2" data-placeholder="Select a company" class="form-select @error('company_id') is-invalid @enderror company-cluster-selector" required>
                        <option value=""></option>
                        @foreach ($companies as $company)`
                            <option value="{{ $company->id }}" {{ $company->id == old('company_id') ? 'selected' : '' }}>{{ $company->company_name }}</option>
                        @endforeach
                    </select>

                    @error('company_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="separator separator-dashed my-3"></div>

                <h3>Branch Information</h3>

                <div class="mb-4 mt-4">
                    <label class="form-label">Branch Name</label>
                    <input value="{{ old('name') }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Branch Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Branch Code</label>
                    <input value="{{ old('code') }}" name="code" type="text" class="form-control @error('code') is-invalid @enderror" placeholder="Branch Code" required/>

                    @error('code')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Branch Cluster</label>
                    <select id="cluster_id" name="cluster_id" data-control="select2" data-placeholder="Select a cluster" class="form-select @error('cluster_id') is-invalid @enderror" required>
                        <option value=""></option>
                        @if (!empty($company->clusters))
                            @foreach ($company->clusters as $cluster)`
                                <option value="{{ $cluster->id }}" {{ $cluster->id == old('cluster_id') ? 'selected' : '' }}>{{ $cluster->name }}</option>
                            @endforeach
                        @endif
                    </select>

                    @error('cluster_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="separator separator-dashed my-3"></div>

                <h3>Branch Address</h3>

                <div class="mb-4 mt-4">
                    <label class="form-label">Unit No./Floor Bldg.</label>
                    <input value="{{ old('unit_floor_number') }}" name="unit_floor_number" type="text" class="form-control @error('unit_floor_number') is-invalid @enderror" placeholder="Unit No./Floor Bldg." required/>

                    @error('unit_floor_number')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-4">
                    <label class="form-label">Street</label>
                    <input value="{{ old('street') }}" name="street" type="text" class="form-control @error('street') is-invalid @enderror" placeholder="Street" required/>

                    @error('street')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Region</label>
                    <select id="region_id" name="region_id" data-control="select2" data-placeholder="Select a region" class="form-control @error('region_id') is-invalid @enderror" required>
                        <option value=""></option>
                        @foreach ($regions as $region)
                            <option value="{{ $region->id }}" {{ $region->id == old('region_id') ? 'selected' : '' }}>{{ $region->name }}</option>
                        @endforeach
                    </select>

                    @error('region_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Province</label>
                    <select id="province_id" name="province_id" data-control="select2" data-placeholder="Select a province" class="form-control @error('province_id') is-invalid @enderror" required>
                        <option value=""></option>
                        @if (!empty($provinces))
                            @foreach ($provinces as $province)
                                <option value="{{ $province->id }}" {{ $province->id == old('province_id') ? 'selected' : '' }}>{{ $province->name }}</option>
                            @endforeach
                        @endif
                    </select>

                    @error('province_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">City</label>
                    <select id="city_id" name="city_id" data-control="select2" data-placeholder="Select a city" class="form-control @error('city_id') is-invalid @enderror" required>
                        <option value=""></option>
                        @if (!empty($cities))
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" {{ $city->id == old('city_id') ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        @endif
                    </select>

                    @error('city_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Barangay</label>
                    <select id="barangay_id" name="barangay_id" data-control="select2" data-placeholder="Select a barangay" class="form-control @error('barangay_id') is-invalid @enderror" required>
                        <option value=""></option>
                        @if (!empty($barangays))
                            @foreach ($barangays as $barangay)
                                <option value="{{ $barangay->id }}" {{ $barangay->id == old('barangay_id') ? 'selected' : '' }}>{{ $barangay->name }}</option>
                            @endforeach
                        @endif
                    </select>

                    @error('barangay_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-4">
                    <label class="form-label">Phone Number</label>
                    <input value="{{ old('name') }}" name="phone_number" type="text" class="form-control @error('phone_number') is-invalid @enderror" placeholder="Phone Number" required/>

                    @error('phone_number')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
