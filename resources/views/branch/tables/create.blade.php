<x-default-layout>

    @section('title')
        Create a new table location
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('branch.tables.store', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Name</label>
                    <input value="{{ old('name') }}" autocomplete="off" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Capacity</label>
                    <input step="1" value="{{ old('capacity') }}" autocomplete="off" name="capacity" type="number" class="form-control @error('capacity') is-invalid @enderror" placeholder="capacity" required/>

                    @error('capacity')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Table Location</label>
                    <select name="table_location_id" class="form-select @error('table_location_id') is-invalid @enderror" required>
                        <option value="">Select a table location</option>
                        @foreach ($tableLocations as $tableLocation)
                            <option value="{{ $tableLocation->id }}" {{ old('table_location_id') == $tableLocation->id ? 'selected' : '' }}>{{ $tableLocation->name }}</option>
                        @endforeach
                    </select>
                </div>
                    

                <button type="submit" class="btn btn-primary mt-5 disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
