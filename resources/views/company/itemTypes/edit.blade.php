<x-default-layout>

    @section('title')
        Create a new item type
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.itemTypes.edit', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('company.item-types.update', ['companySlug' => $company->slug, 'item_type' => $itemType->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' || $itemType->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' || $itemType->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Item Type Name</label>
                    <input value="{{ old('name') ?? $itemType->name }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Item Type Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <input value="{{ old('description') ?? $itemType->description }}" name="description" type="text" class="form-control @error('description') is-invalid @enderror" placeholder="Description" required/>

                    @error('description')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-6">
                    <div class="form-check">
                        <input {{ $itemType->show_in_cashier ? 'checked' : '' }} class="form-check-input" name="show_in_cashier" type="checkbox" value="1"/>
                        <label class="form-check-label">
                            Show in cashier?
                        </label>
                    </div>

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
