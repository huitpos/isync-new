<x-default-layout>

    @section('title')
        Update table status
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('branch.table-statuses.update', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug, 'table_status' => $status->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' || $status->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' || $status->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Name</label>
                    <input value="{{ old('name') ?? $status->name }}" autocomplete="off" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Color</label>
                    <input value="{{ old('color') ?? $status->color }}" autocomplete="off" name="color" type="color" class="form-control @error('color') is-invalid @enderror" placeholder="Color" required/>

                    @error('color')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-6">
                    <div class="form-check">
                        <input {{ $status->is_blinking ? 'checked' : '' }} class="form-check-input" name="is_blinking" type="checkbox" value="1"/>
                        <label>
                            Blinking
                        </label>
                    </div>

                    @error('is_blinking')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary mt-5 disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
