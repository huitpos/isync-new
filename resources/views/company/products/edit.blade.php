<x-default-layout>

    @section('title')
        Create a new supplier
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.suppliers.create', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('company.products.update', ['companySlug' => $company->slug, 'product' => $product->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' || $product->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' || $product->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Product Name</label>
                    <input value="{{ old('name') ?? $product->name }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Product Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <input value="{{ old('description') ?? $product->description }}" name="description" type="text" class="form-control @error('description') is-invalid @enderror" placeholder="Description" required/>

                    @error('description')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Item Code</label>
                    <input value="{{ old('code') ?? $product->code }}" name="code" type="text" class="form-control @error('code') is-invalid @enderror" placeholder="Item Code" required/>

                    @error('code')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Item Abbreviation</label>
                    <input value="{{ old('abbreviation') ?? $product->abbreviation }}" name="abbreviation" type="text" class="form-control @error('abbreviation') is-invalid @enderror" placeholder="Item Abbreviation" required/>

                    @error('abbreviation')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Uom</label>
                    <select id="uom_id" name="uom_id" data-control="select2" data-placeholder="select OUM" class="form-select @error('uom_id') is-invalid @enderror" required>
                        <option value=""></option>
                        @foreach ($company->unitOfMeasurements as $uom)
                            <option value="{{ $uom->id }}" {{ $uom->id == old('uom_id') || $uom->id == $product->uom_id ? 'selected' : '' }}>{{ $uom->name }}</option>
                        @endforeach
                    </select>

                    @error('uom_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Barcode</label>
                    <input value="{{ old('barcode') ?? $product->barcode }}" name="barcode" type="text" class="form-control @error('barcode') is-invalid @enderror" placeholder="Barcode" required/>

                    @error('barcode')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Serial No.</label>
                    <input value="{{ old('serial_number') ?? $product->serial_number }}" name="serial_number" type="text" class="form-control @error('serial_number') is-invalid @enderror" placeholder="Serial No." required/>

                    @error('serial_number')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Department</label>
                    <select id="department_id" name="department_id" data-control="select2" data-placeholder="Select Department" class="form-select @error('department_id') is-invalid @enderror" required>
                        <option value=""></option>
                        @foreach ($company->departments as $department)
                            <option value="{{ $department->id }}" {{ $department->id == old('department_id') || $department->id == $product->department_id ? 'selected' : '' }}>{{ $department->name }}</option>
                        @endforeach
                    </select>

                    @error('department_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Category</label>
                    <select id="category_id" name="category_id" data-control="select2" data-placeholder="Select Category" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">Select a category</option>
                        @foreach ($company->categories as $category)
                            <option value="{{ $category->id }}" {{ $category->id == old('category_id') || $category->id == $product->category_id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>

                    @error('category_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Sub-category</label>
                    <select id="subcategory_id" name="subcategory_id" data-control="select2" data-placeholder="Select Subcategory" class="form-select @error('subcategory_id') is-invalid @enderror" required>
                        <option value="">Select a category</option>
                        @foreach ($company->subcategories as $subcategory)
                            <option value="{{ $subcategory->id }}" {{ $subcategory->id == old('subcategory_id') || $subcategory->id == $product->subcategory_id ? 'selected' : '' }}>{{ $subcategory->name }}</option>
                        @endforeach
                    </select>

                    @error('subcategory_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">SRP</label>
                    <input value="{{ old('srp') ?? $product->srp }}" name="srp" type="text" class="form-control @error('srp') is-invalid @enderror" placeholder="SRP" required/>

                    @error('srp')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Cost</label>
                    <input value="{{ old('cost') ?? $product->cost }}" name="cost" type="text" class="form-control @error('cost') is-invalid @enderror" placeholder="Cost" required/>

                    @error('cost')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-6">
                    <div class="form-check">
                        <input {{ $product->vatable ? 'checked' : '' }} class="form-check-input" name="vatable" type="checkbox" value="1"/>
                        <label>
                            Vatable
                        </label>
                    </div>

                    @error('vatable')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-6">
                    <div class="form-check">
                        <input {{ $product->discount_exempt ? 'checked' : '' }} class="form-check-input" name="discount_exempt" type="checkbox" value="1"/>
                        <label>
                            Discount Exempt
                        </label>
                    </div>

                    @error('discount_exempt')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-6">
                    <div class="form-check">
                        <input {{ $product->open_price ? 'checked' : '' }} class="form-check-input" name="open_price" type="checkbox" value="1"/>
                        <label>
                            Open Price
                        </label>
                    </div>

                    @error('open_price')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-7">
                    <label class="form-label">Raw Materials</label>
                    <div class="repeater">
                        <div class="form-group">
                            <div data-repeater-list="raw_items" class="d-flex flex-column gap-3">
                                @if (empty(old('raw_items')) && count($product->rawItems) == 0)
                                    <div data-repeater-item="" class="form-group d-flex flex-wrap align-items-center gap-5">
                                        <div class="w-100 w-md-200px">
                                            <select class="form-select" name="product_id" data-placeholder="Select a variation">
                                                <option value="">Product</option>
                                                @foreach($company->rawProducts as $selectProduct)
                                                    <option value="{{ $selectProduct->id }}">{{ $selectProduct->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <input type="text" class="form-control mw-100 w-200px" name="quantity" placeholder="Quantity" />

                                        <div class="w-100 w-md-200px">
                                            <select class="form-select" name="uom_id">
                                                <option value="">Unit of measurement</option>
                                                @foreach($company->unitOfMeasurements as $uom)
                                                    <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <button type="button" data-repeater-delete="" class="btn btn-sm btn-icon btn-light-danger">
                                            <i class="fa-solid fa-xmark fs-1"></i>
                                        </button>
                                    </div>
                                @else
                                    @php
                                        $fromOld = !empty(old('raw_items'));
                                        $rawItems = old('raw_items') ?? [];

                                        if (!$fromOld) {
                                            $rawItems = $product->rawItems->toArray();
                                        }
                                    @endphp

                                    @foreach ($rawItems as $key => $rawItem)
                                        @php
                                            $productId = $fromOld ? $rawItem['product_id'] : $rawItem['raw_item']['raw_product_id'];
                                            $quantity = $fromOld ? $rawItem['quantity'] : $rawItem['raw_item']['quantity'];
                                            $uomId = $fromOld ? $rawItem['uom_id'] : $rawItem['raw_item']['uom_id'];
                                        @endphp

                                        <div data-repeater-item="" class="form-group d-flex flex-wrap align-items-center gap-5">
                                            <div class="w-100 w-md-200px">
                                                <select class="form-select" name="product_id" data-placeholder="Select a variation">
                                                    <option value="">Product</option>
                                                    @foreach($company->rawProducts as $selectProduct)
                                                        <option value="{{ $selectProduct->id }}" {{ $selectProduct->id == $productId ? 'selected' : '' }}>{{ $selectProduct->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <input value="{{ $quantity }}" type="text" class="form-control mw-100 w-200px @error('raw_items.' . $key . '.quantity') is-invalid @enderror" name="quantity" placeholder="Quantity"/>

                                            <div class="w-100 w-md-200px">
                                                <select class="form-select @error('raw_items.' . $key . '.uom_id') is-invalid @enderror" name="uom_id">
                                                    <option value="">Unit of measurement</option>
                                                    @foreach($company->unitOfMeasurements as $uom)
                                                        <option value="{{ $uom->id }}"  {{ $uom->id == $uomId ? 'selected' : '' }}>{{ $uom->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <button type="button" data-repeater-delete="" class="btn btn-sm btn-icon btn-light-danger">
                                                <i class="fa-solid fa-xmark fs-1"></i>
                                            </button>

                                            <div class="invalid-feedback">
                                                @error('raw_items.' . $key . '.quantity')
                                                    <p>{{ $message }}</p>
                                                @enderror

                                                @error('raw_items.' . $key . '.uom_id')
                                                    <p>{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="form-group mt-5">
                            <button type="button" data-repeater-create="" class="btn btn-sm btn-light-primary">
                            <i class="fa-solid fa-plus fs-2"></i>Add another variation</button>
                        </div>
                    </div>
                </div>

                <div class="mb-4 mt-7">
                    <label class="form-label">Min. Stock Level</label>
                    <input value="{{ old('minimum_stock_level') ?? $product->minimum_stock_level }}" name="minimum_stock_level" type="text" class="form-control @error('minimum_stock_level') is-invalid @enderror" placeholder="Min. Stock Level" required/>

                    @error('minimum_stock_level')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Max. Stock Level</label>
                    <input value="{{ old('maximum_stock_level') ?? $product->maximum_stock_level }}" name="maximum_stock_level" type="text" class="form-control @error('maximum_stock_level') is-invalid @enderror" placeholder="Max. Stock Level" required/>

                    @error('maximum_stock_level')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-7">
                    <label class="form-label">Running Stock Level</label>
                    <input value="{{ old('stock_on_hand') ?? $product->stock_on_hand }}" name="stock_on_hand" type="text" class="form-control @error('stock_on_hand') is-invalid @enderror" placeholder="Running Stock Level" required/>

                    @error('stock_on_hand')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-7">
                    <label class="form-label">Bundled Items</label>
                    <div class="repeater">
                        <div class="form-group">
                            <div data-repeater-list="bundled_items" class="d-flex flex-column gap-3">
                                @if (empty(old('bundled_items')) && count($product->bundledItems) == 0)
                                    <div data-repeater-item="" class="form-group d-flex flex-wrap align-items-center gap-5">
                                        <div class="w-100 w-md-200px">
                                            <select class="form-select" name="product_id" data-placeholder="Select a variation">
                                                <option value="">Product</option>
                                                @foreach($company->visibleProducts as $selectProduct)
                                                    <option value="{{ $selectProduct->id }}">{{ $selectProduct->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <input type="text" class="form-control mw-100 w-200px" name="quantity" placeholder="Quantity" />

                                        <button type="button" data-repeater-delete="" class="btn btn-sm btn-icon btn-light-danger">
                                            <i class="fa-solid fa-xmark fs-1"></i>
                                        </button>
                                    </div>
                                @else
                                    @php
                                        $fromOld = !empty(old('bundled_items'));
                                        $bundledItems = old('bundled_items') ?? [];

                                        if (!$fromOld) {
                                            $bundledItems = $product->bundledItems->toArray();
                                        }
                                    @endphp

                                    @foreach ($bundledItems as $key => $bundleItem)
                                        @php
                                            $productId = $fromOld ? $bundleItem['product_id'] : $bundleItem['bundled_item']['included_product_id'];
                                            $quantity = $fromOld ? $rawItem['quantity'] : $bundleItem['bundled_item']['quantity'];
                                        @endphp

                                        <div data-repeater-item="" class="form-group d-flex flex-wrap align-items-center gap-5">
                                            <div class="w-100 w-md-200px">
                                                <select class="form-select" name="product_id" data-placeholder="Select a variation">
                                                    <option value="">Product</option>
                                                    @foreach($company->visibleProducts as $selectProduct)
                                                        <option value="{{ $selectProduct->id }}" {{ $selectProduct->id == $productId ? 'selected' : '' }}>{{ $selectProduct->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <input value="{{ $quantity }}" type="text" class="form-control mw-100 w-200px @error('raw_items.' . $key . '.quantity') is-invalid @enderror" name="quantity" placeholder="Quantity"/>

                                            <button type="button" data-repeater-delete="" class="btn btn-sm btn-icon btn-light-danger">
                                                <i class="fa-solid fa-xmark fs-1"></i>
                                            </button>

                                            <div class="invalid-feedback">
                                                @error('raw_items.' . $key . '.uom_id')
                                                    <p>{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="form-group mt-5">
                            <button type="button" data-repeater-create="" class="btn btn-sm btn-light-primary">
                            <i class="fa-solid fa-plus fs-2"></i>Add another variation</button>
                        </div>
                    </div>
                </div>

                <div class="mb-4 mt-7">
                    <label class="form-label">Image</label><br>

                    <!--begin::Image input-->
                    <div class="image-input image-input-placeholder image-input-empty" data-kt-image-input="true">
                        <!--begin::Image preview wrapper-->
                        <div class="image-input-wrapper w-125px h-125px" style="background-size:contain"></div>
                        <!--end::Image preview wrapper-->

                        <!--begin::Edit button-->
                        <label class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                        data-kt-image-input-action="change"
                        data-bs-toggle="tooltip"
                        data-bs-dismiss="click"
                        title="Change avatar">
                            <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i>

                            <!--begin::Inputs-->
                            <input type="file" name="avatar" accept=".png, .jpg, .jpeg" />
                            <input type="hidden" name="avatar_remove" />
                            <!--end::Inputs-->
                        </label>
                        <!--end::Edit button-->

                        <!--begin::Cancel button-->
                        <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                        data-kt-image-input-action="cancel"
                        data-bs-toggle="tooltip"
                        data-bs-dismiss="click"
                        title="Cancel avatar">
                            <i class="ki-outline ki-cross fs-3"></i>
                        </span>
                        <!--end::Cancel button-->

                        <!--begin::Remove button-->
                        <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                        data-kt-image-input-action="remove"
                        data-bs-toggle="tooltip"
                        data-bs-dismiss="click"
                        title="Remove avatar">
                            <i class="ki-outline ki-cross fs-3"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-4 mt-7">
                    <label class="form-label">Mark Up</label>
                    <input value="{{ old('markup') ?? $product->markup }}" name="markup" type="text" class="form-control @error('markup') is-invalid @enderror" placeholder="markup" required/>

                    @error('markup')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Item Type</label>
                    <select id="item_type_id" name="item_type_id" data-control="select2" data-placeholder="Select Item Type" class="form-select @error('item_type_id') is-invalid @enderror" required>
                        <option value=""></option>
                        @foreach ($company->itemTypes as $itemType)
                        <option value="{{ $itemType->id }}" {{ $itemType->id == old('item_type_id') || $itemType->id == $product->item_type_id ? 'selected' : '' }}>{{ $itemType->name }}</option>
                        @endforeach
                    </select>

                    @error('item_type_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary mt-5">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>


<style>
    .image-input-placeholder {
        background-image: url('/assets/media/avatars/blank.png');
        background-size: contain
    }

    [data-bs-theme="dark"] .image-input-placeholder {
        background-image: url('svg/avatars/blank-dark.svg');
    }
</style>