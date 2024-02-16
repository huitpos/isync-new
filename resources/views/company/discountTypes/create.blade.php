<x-default-layout>

    @section('title')
        Create a new discount type
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.discountTypes.create', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('company.discount-types.store', ['companySlug' => $company->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
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
                    <label class="form-label">Discount Name</label>
                    <input value="{{ old('name') }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Discount Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <input value="{{ old('description') }}" name="description" type="text" class="form-control @error('description') is-invalid @enderror" placeholder="Description" required/>

                    @error('description')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Department</label>
                    <select class="form-select" name="department_id" data-control="select2" data-close-on-select="true" data-placeholder="Select department">
                        <option></option>
                        @foreach ($company->departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>

                    @error('department_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Type</label>
                    <select class="form-select @error('type') is-invalid @enderror" name="type">
                        <option value="amount">Amount</option>
                        <option value="percentage">Percentage</option>
                    </select>

                    @error('type')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Discount</label>
                    <input value="{{ old('discount') }}" name="discount" type="text" class="form-control @error('discount') is-invalid @enderror" placeholder="Discount" required/>

                    @error('discount')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-5">
                    <input value="1" name="is_vat_exempt" class="form-check-input" type="checkbox" id="is_vat_exempt">
                    <label class="form-check-label" for="is_vat_exempt">
                        Is Vat Exempt
                    </label>
                </div>


                <button type="submit" class="btn btn-primary mt-5 disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
