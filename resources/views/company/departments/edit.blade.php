<x-default-layout>

    @section('title')
        Create a new department
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.departments.edit', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('company.departments.update', ['companySlug' => $company->slug, 'department' => $department->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' || $department->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' || $department->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Department Name</label>
                    <input value="{{ old('name') ?? $department->name }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Department Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <input value="{{ old('description') ?? $department->description }}" name="description" type="text" class="form-control @error('description') is-invalid @enderror" placeholder="Description" required/>

                    @error('description')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Suppliers</label>
                    @php
                        $selectedSuppliers = [];
                        foreach ($department->suppliers as $supplier) {
                            $selectedSuppliers[] = $supplier->id;
                        }
                    @endphp
                    <select class="form-select" name="suppliers[]" data-control="select2" data-close-on-select="false" data-placeholder="Select supplier" data-allow-clear="true" multiple="multiple">
                        @foreach ($company->suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ in_array($supplier->id, $selectedSuppliers) ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>

                    @error('description')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary mt-5 disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
