<x-default-layout>

    @section('title')
        Edit item location
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('company.item-locations.update', ['companySlug' => $company->slug, 'item_location' => $location->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' || $location->status = 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' || $location->status = 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Name</label>
                    <input value="{{ old('name') ?? $location->name }}" autocomplete="off" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Unit / Floor Number</label>
                    <input value="{{ old('unit_floor_number') ?? $location->unit_floor_number }}" autocomplete="off" name="unit_floor_number" type="text" class="form-control @error('unit_floor_number') is-invalid @enderror" placeholder="Unit / Floor Number" required/>

                    @error('unit_floor_number')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Street</label>
                    <input value="{{ old('street') ?? $location->street }}" autocomplete="off" name="street" type="text" class="form-control @error('street') is-invalid @enderror" placeholder="Street" required/>

                    @error('street')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Region</label>
                    <select id="region_id" name="region_id" data-control="select2" data-placeholder="Select a region" class="form-control @error('region_id') is-invalid @enderror" required>
                        <option value=""></option>
                        @foreach ($regions as $region)
                            <option value="{{ $region->id }}" {{ $region->id == old('region_id') || $region->id == $location->region_id ? 'selected' : '' }}>{{ $region->name }}</option>
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
                                <option value="{{ $province->id }}" {{ $province->id == old('province_id') || $province->id == $location->province_id ? 'selected' : '' }}>{{ $province->name }}</option>
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
                                <option value="{{ $city->id }}" {{ $city->id == old('city_id') || $city->id == $location->city_id ? 'selected' : '' }}>{{ $city->name }}</option>
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
                                <option value="{{ $barangay->id }}" {{ $barangay->id == old('barangay_id') || $barangay->id == $location->barangay_id ? 'selected' : '' }}>{{ $barangay->name }}</option>
                            @endforeach
                        @endif
                    </select>

                    @error('barangay_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary mt-5 disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
