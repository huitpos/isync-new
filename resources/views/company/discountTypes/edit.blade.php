<x-default-layout>

    @section('title')
        Edit discount type
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.discountTypes.edit', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('company.discount-types.update', ['companySlug' => $company->slug, 'discount_type' => $discountType->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' || $discountType->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' || $discountType->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Discount Name</label>
                    <input value="{{ old('name') ?? $discountType->name }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Discount Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <input value="{{ old('description') ?? $discountType->description }}" name="description" type="text" class="form-control @error('description') is-invalid @enderror" placeholder="Description" required/>

                    @error('description')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Department</label>
                    <select class="form-select" name="department_id" data-control="select2" data-close-on-select="true" data-placeholder="Select department">
                        <option></option>
                        @foreach ($company->departments as $department)
                            <option {{ $department->id == $discountType->department_id ? 'selected' : '' }} value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>

                    @error('department_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Type</label>
                    <select class="form-select @error('type') is-invalid @enderror" name="type">
                        <option {{ $discountType->type == 'amount' ? 'selected' : '' }} value="amount">Amount</option>
                        <option {{ $discountType->type == 'percentage' ? 'selected' : '' }} value="percentage">Percentage</option>
                    </select>

                    @error('type')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Discount</label>
                    <input value="{{ old('discount') ?? $discountType->discount }}" name="discount" type="text" class="form-control @error('discount') is-invalid @enderror" placeholder="Discount" required/>

                    @error('discount')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-5">
                    <input {{ $discountType->is_vat_exempt ? 'checked' : '' }} value="1" name="is_vat_exempt" class="form-check-input" type="checkbox" id="is_vat_exempt">
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
