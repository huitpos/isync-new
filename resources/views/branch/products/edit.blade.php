<x-default-layout>
    @section('title')
        {{ $product->name }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.products.show', $company, $product) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <h2>{{ $product->name }}</h2>

            @if($product->image)
            <div class="pb-1 fs-6">
                <div class="fw-bold mt-5">Image</div>
                <div class="text-gray-600">
                    <div class="image-input-wrapper w-125px h-125px" style="border: 1px dashed #92A0B3; background-size:contain; background-repeat: no-repeat; background-image: url('{{ Storage::disk('s3')->url($product->image) }}');"></div>
                </div>
            </div>
            @endif

            <div class="pb-1 fs-6 mt-7">
                <div class="fw-bold">Description</div>
                <div class="text-gray-600">{{ $product->description }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Item Code</div>
                <div class="text-gray-600">{{ $product->code }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">SKU</div>
                <div class="text-gray-600">{{ $product->sku }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Item Abbreviation</div>
                <div class="text-gray-600">{{ $product->abbreviation }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">UOM</div>
                <div class="text-gray-600">{{ $product->uom->name }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Barcode</div>
                <div class="text-gray-600">{{ $product->barcode }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Deparment.</div>
                <div class="text-gray-600">{{ $product->department->name }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Category</div>
                <div class="text-gray-600">{{ $product->category->name }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Sub-Category</div>
                <div class="text-gray-600">{{ $product->subcategory->name }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Stock</div>
                <div class="text-gray-600">{{ $pivotData->stock }}</div>
            </div>

            <form class="mt-3" action="{{ route('branch.products.update', ['companySlug' => $company->slug, 'product' => $product->id, 'branchSlug' => $branch->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">SRP</label>
                    <input value="{{ old('name') ?? $pivotData->price }}" name="price" type="text" class="form-control @error('price') is-invalid @enderror" placeholder="SRP" required/>

                    @error('price')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary mt-5 disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>

            </form>
        </div>
    </div>
</x-default-layout>
