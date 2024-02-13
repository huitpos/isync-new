<x-default-layout>

    @section('title')
        Edit subcategory
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.subcategories.edit', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('company.subcategories.update', ['companySlug' => $company->slug, 'subcategory' => $subcategory->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' || $subcategory->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' || $subcategory->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Sub-Category Name</label>
                    <input value="{{ old('name') ?? $subcategory->name }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Sub-Category Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <input value="{{ old('description') ?? $subcategory->description }}" name="description" type="text" class="form-control @error('description') is-invalid @enderror" placeholder="Description" required/>

                    @error('description')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id" data-control="select2" data-close-on-select="true" data-placeholder="Select category">
                        <option></option>
                        @foreach ($company->categories as $category)
                            <option value="{{ $category->id }}" {{ $category->id == $subcategory->category_id ? 'selected' : '' }}>{{ $category->name }}</option>
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
