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

            <h3 class="mt-5">Price</h3>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">SRP</div>
                <div class="text-gray-600">{{ $product->srp }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Cost</div>
                <div class="text-gray-600">{{ $product->cost }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Vat Exempt</div>
                <div class="text-gray-600">{{ $product->vat_exempt ? 'Yes' : 'No' }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Discount Exempt</div>
                <div class="text-gray-600">{{ $product->discount_exempt ? 'Yes' : 'No' }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Open Price</div>
                <div class="text-gray-600">{{ $product->open_price ? 'Yes' : 'No' }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">With Serial</div>
                <div class="text-gray-600">{{ $product->with_serial ? 'Yes' : 'No' }}</div>
            </div>

            <h3 class="mt-5 mb-2">Raw Materials</h3>

            <div class="pb-1 fs-6 mt-2">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr class="fw-bold fs-6 text-gray-800">
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit of Measurement</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->rawItems as $rawMaterial)
                                <tr>
                                    <td>{{ $rawMaterial->name }}</td>
                                    <td>{{ $rawMaterial->raw_item->quantity }}</td>
                                    <td>{{ $rawMaterial->uom->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <h3 class="mt-5 mb-3">Stock Level</h3>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Min. Stock Level</div>
                <div class="text-gray-600">{{ $product->minimum_stock_level }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Max. Stock Level</div>
                <div class="text-gray-600">{{ $product->maximum_stock_level }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Running Stock Level</div>
                <div class="text-gray-600">{{ $product->stock_on_hand }}</div>
            </div>

            <h3>Bundled Items</h3>

            <div class="pb-1 fs-6 mt-2">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr class="fw-bold fs-6 text-gray-800">
                                <th>Product</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->bundledItems as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->bundled_item->quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Item Type</div>
                <div class="text-gray-600">{{ $product->itemType->name }}</div>
            </div>

            <div class="pb-1 fs-6 mt-2">
                <div class="fw-bold">Mark Up Type</div>
                <div class="text-gray-600">{{ $product->markup_type }}</div>
            </div>
        </div>
    </div>
</x-default-layout>
