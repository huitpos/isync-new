<x-default-layout>

    @section('title')
        Create a new product
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.products.create', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('company.products.store', ['companySlug' => $company->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
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
                    <label class="form-label">Product Name</label>
                    <input value="{{ old('name') }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Product Name" required/>

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
                    <label class="form-label">SKU</label>
                    <input value="{{ old('sku') }}" name="sku" type="text" class="form-control @error('sku') is-invalid @enderror" placeholder="SKU" required/>

                    @error('sku')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Item Abbreviation</label>
                    <input value="{{ old('abbreviation') }}" name="abbreviation" type="text" class="form-control @error('abbreviation') is-invalid @enderror" placeholder="Item Abbreviation" required/>

                    @error('abbreviation')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Uom</label>
                    <select id="uom_id" name="uom_id" data-control="select2" data-placeholder="select OUM" class="form-select @error('uom_id') is-invalid @enderror uom-conversion-selector" required>
                        <option value=""></option>
                        @foreach ($company->unitOfMeasurements as $uom)
                            <option value="{{ $uom->id }}" {{ $uom->id == old('uom_id') ? 'selected' : '' }}>{{ $uom->name }}</option>
                        @endforeach
                    </select>

                    @error('uom_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Delivery Uom</label>
                    <select id="delivery_converion_id" name="delivery_uom_id" data-control="select2" data-placeholder="Select a delivery UOM" class="form-select @error('delivery_uom_id') is-invalid @enderror" required>
                        <option value=""></option>
                    </select>

                    @error('delivery_uom_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Barcode</label>
                    <input value="{{ old('barcode') }}" name="barcode" type="text" class="form-control @error('barcode') is-invalid @enderror" placeholder="Barcode" required/>

                    @error('barcode')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Part No.</label>
                    <input value="{{ old('part_number') }}" name="part_number" type="text" class="form-control @error('part_number') is-invalid @enderror" placeholder="Part No." required/>

                    @error('part_number')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Department</label>
                    <select id="department_id" name="department_id" data-control="select2" data-placeholder="Select Department" class="form-select @error('department_id') is-invalid @enderror department-category-selector" required>
                        <option value=""></option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ $department->id == old('department_id') ? 'selected' : '' }}>{{ $department->name }}</option>
                        @endforeach
                    </select>

                    @error('department_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Category</label>
                    <select id="category_id" name="category_id" data-control="select2" data-placeholder="Select Category" class="form-select @error('category_id') is-invalid @enderror category-subcategory-selector" required>
                        <option value="">Select a category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ $category->id == old('category_id') ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>

                    @error('category_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Sub-category</label>
                    <select id="subcategory_id" name="subcategory_id" data-control="select2" data-placeholder="Select Subcategory" class="form-select @error('subcategory_id') is-invalid @enderror" required>
                        <option value="">Select a subcategory</option>
                        @foreach ($subcategories as $subcategory)
                            <option value="{{ $subcategory->id }}" {{ $subcategory->id == old('subcategory_id') ? 'selected' : '' }}>{{ $subcategory->name }}</option>
                        @endforeach
                    </select>

                    @error('subcategory_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Cost</label>
                    <input id="cost" value="{{ old('cost') }}" name="cost" type="number" class="form-control @error('cost') is-invalid @enderror compute-srp" placeholder="Cost" required/>

                    @error('cost')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Markup Type</label>
                    <select id="markup_type" name="markup_type" class="form-control @error('status') is-invalid @enderror compute-srp" required>
                        <option value="fixed" {{ old('status') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                        <option value="percentage" {{ old('status') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-7">
                    <label class="form-label">Mark Up</label>
                    <input id="markup" value="{{ old('markup') }}" name="markup" type="number" class="form-control @error('markup') is-invalid @enderror compute-srp" placeholder="Markup" required/>

                    @error('markup')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">SRP</label>
                    <input id="srp" value="{{ old('srp') ?? 0 }}" name="srp" readonly type="text" class="form-control @error('srp') is-invalid @enderror" placeholder="SRP" required/>

                    @error('srp')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-6">
                    <div class="form-check">
                        <input class="form-check-input" name="vat_exempt" type="checkbox" value="1"/>
                        <label>
                            Vat Exempt
                        </label>
                    </div>

                    @error('vat_exempt')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-6">
                    <div class="form-check">
                        <input class="form-check-input" name="discount_exempt" type="checkbox" value="1"/>
                        <label>
                            Discount Exempt (SC/PWD)
                        </label>
                    </div>

                    @error('discount_exempt')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-6">
                    <div class="form-check">
                        <input class="form-check-input" name="open_price" type="checkbox" value="1"/>
                        <label>
                            Open Price
                        </label>
                    </div>

                    @error('open_price')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-6">
                    <div class="form-check">
                        <input class="form-check-input" name="with_serial" type="checkbox" value="1"/>
                        <label>
                            With Serial
                        </label>
                    </div>

                    @error('with_serial')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-7">
                    <label class="form-label">Raw Materials</label>
                    <div class="repeater">
                        <div class="form-group">
                            <div data-repeater-list="raw_items" class="d-flex flex-column gap-3">
                                @if (empty(old('raw_items')))
                                    <div data-repeater-item="" class="form-group d-flex flex-wrap align-items-center gap-5">
                                        <div class="w-100 w-md-200px">
                                            <select class="form-select" name="product_id" data-placeholder="Select a variation">
                                                <option value="">Product</option>
                                                @foreach($company->rawProducts as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="w-100 w-md-200px">
                                            <input type="text" class="form-control mw-100 w-200px" name="quantity" placeholder="Quantity" />
                                        </div>

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
                                    @foreach (old('raw_items') as $key => $rawItem)
                                        <div data-repeater-item="" class="form-group d-flex flex-wrap align-items-center gap-5">
                                            <div class="w-100 w-md-200px">
                                                <select class="form-select" name="product_id" data-placeholder="Select a variation">
                                                    <option value="">Product</option>
                                                    @foreach($company->rawProducts as $product)
                                                        <option value="{{ $product->id }}" {{ $product->id == $rawItem['product_id'] ? 'selected' : '' }}>{{ $product->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <input type="text" class="form-control mw-100 w-200px @error('raw_items.' . $key . '.quantity') is-invalid @enderror" name="quantity" placeholder="Quantity"/>

                                            <div class="w-100 w-md-200px">
                                                <select class="form-select @error('raw_items.' . $key . '.uom_id') is-invalid @enderror" name="uom_id">
                                                    <option value="">Unit of measurement</option>
                                                    @foreach($company->unitOfMeasurements as $uom)
                                                        <option value="{{ $uom->id }}"  {{ $uom->id == $rawItem['uom_id'] ? 'selected' : '' }}>{{ $uom->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <button type="button" data-repeater-delete="" class="btn btn-sm btn-icon btn-light-danger mt-5">
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
                            <i class="fa-solid fa-plus fs-2"></i>Add raw material</button>
                        </div>
                    </div>
                </div>

                <div class="mb-4 mt-7">
                    <label class="form-label">Min. Stock Level</label>
                    <input value="{{ old('minimum_stock_level') }}" name="minimum_stock_level" type="text" class="form-control @error('minimum_stock_level') is-invalid @enderror" placeholder="Min. Stock Level" required/>

                    @error('minimum_stock_level')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Max. Stock Level</label>
                    <input value="{{ old('maximum_stock_level') }}" name="maximum_stock_level" type="text" class="form-control @error('maximum_stock_level') is-invalid @enderror" placeholder="Max. Stock Level" required/>

                    @error('maximum_stock_level')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-7">
                    <label class="form-label">Bundled Items</label>
                    <div class="repeater">
                        <div class="form-group">
                            <div data-repeater-list="bundled_items" class="d-flex flex-column gap-3">
                                @if (empty(old('bundled_items')))
                                    <div data-repeater-item="" class="form-group d-flex flex-wrap align-items-center gap-5">
                                        <div class="w-100 w-md-200px">
                                            <select class="form-select" name="product_id" data-placeholder="Select a variation">
                                                <option value="">Product</option>
                                                @foreach($company->visibleProducts as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <input type="text" class="form-control mw-100 w-200px" name="quantity" placeholder="Quantity" />

                                        <button type="button" data-repeater-delete="" class="btn btn-sm btn-icon btn-light-danger">
                                            <i class="fa-solid fa-xmark fs-1"></i>
                                        </button>
                                    </div>
                                @else
                                    @foreach (old('bundled_items') as $key => $rawItem)
                                        <div data-repeater-item="" class="form-group d-flex flex-wrap align-items-center gap-5">
                                            <div class="w-100 w-md-200px">
                                                <select class="form-select" name="product_id" data-placeholder="Select a variation">
                                                    <option value="">Product</option>
                                                    @foreach($company->visibleProducts as $product)
                                                        <option value="{{ $product->id }}" {{ $product->id == $rawItem['product_id'] ? 'selected' : '' }}>{{ $product->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <input type="text" class="form-control mw-100 w-200px @error('bundled_items.' . $key . '.quantity') is-invalid @enderror" name="quantity" placeholder="Quantity"/>

                                            <button type="button" data-repeater-delete="" class="btn btn-sm btn-icon btn-light-danger">
                                                <i class="fa-solid fa-xmark fs-1"></i>
                                            </button>

                                            @error('bundled_items.' . $key . '.quantity')
                                                <div class="invalid-feedback"> {{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="form-group mt-5">
                            <button type="button" data-repeater-create="" class="btn btn-sm btn-light-primary">
                            <i class="fa-solid fa-plus fs-2"></i>Add budle item</button>
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
                        title="Change image">
                            <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i>

                            <!--begin::Inputs-->
                            <input type="file" name="image" accept=".png, .jpg, .jpeg" />
                            <input type="hidden" name="image_remove" />
                            <!--end::Inputs-->
                        </label>
                        <!--end::Edit button-->

                        <!--begin::Cancel button-->
                        <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                        data-kt-image-input-action="cancel"
                        data-bs-toggle="tooltip"
                        data-bs-dismiss="click"
                        title="Cancel image">
                            <i class="ki-outline ki-cross fs-3"></i>
                        </span>
                        <!--end::Cancel button-->

                        <!--begin::Remove button-->
                        <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                        data-kt-image-input-action="remove"
                        data-bs-toggle="tooltip"
                        data-bs-dismiss="click"
                        title="Remove image">
                            <i class="ki-outline ki-cross fs-3"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Item Type</label>
                    <select id="item_type_id" name="item_type_id" data-control="select2" data-placeholder="Select Item Type" class="form-select @error('item_type_id') is-invalid @enderror" required>
                        <option value=""></option>
                        @foreach ($itemTypes as $itemType)
                        <option value="{{ $itemType->id }}" {{ $itemType->id == old('item_type_id') ? 'selected' : '' }}>{{ $itemType->name }}</option>
                        @endforeach
                    </select>

                    @error('item_type_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Item Location</label>
                    <select class="form-select" name="item_locations[]" data-control="select2" data-close-on-select="false" data-placeholder="Select location" data-allow-clear="true" multiple="multiple">
                        @foreach ($company->itemLocations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4 mt-7">
                    <label class="form-label">Max Discount</label>
                    <input name="max_discount" type="text" class="form-control @error('max_discount') is-invalid @enderror" required/>

                    @error('max_discount')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-7">
                    <label class="form-label">Discounts</label>
                    <div class="repeater">
                        <div class="form-group">
                            <div data-repeater-list="discounts" class="d-flex flex-column gap-3">
                                @if (empty(old('discounts')) && count($product->discounts) == 0)
                                    <div data-repeater-item="" class="form-group d-flex flex-wrap align-items-center gap-5">
                                        <div class="w-100 w-md-200px">
                                            <select class="form-select" name="discount_type_id" data-placeholder="Select discount">
                                                <option value="">Discount</option>
                                                @foreach($discountTypes as $discountType)
                                                    <option value="{{ $discountType->id }}">{{ $discountType->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="w-100 w-md-200px">
                                            <select class="form-select" name="type" data-placeholder="Select a variation">
                                                <option value="">Type</option>
                                                <option value="percentage">Percentage</option>
                                                <option value="amount">Amount</option>
                                            </select>
                                        </div>

                                        <input type="text" class="form-control mw-100 w-200px" name="discount" placeholder="Discount" />

                                        <button type="button" data-repeater-delete="" class="btn btn-sm btn-icon btn-light-danger">
                                            <i class="fa-solid fa-xmark fs-1"></i>
                                        </button>
                                    </div>
                                @else
                                    @php
                                        $fromOld = !empty(old('discounts'));
                                        $discounts = old('discounts') ?? [];

                                        if (!$fromOld) {
                                            $discounts = $product->discounts->toArray();
                                        }
                                    @endphp

                                    @foreach ($discounts as $key => $discount)
                                        @php
                                            $discountId = $fromOld ? $discount['discount_type_id'] : $discount['pivot']['discount_type_id'];
                                            $type = $fromOld ? $discount['type'] : $discount['pivot']['type'];
                                            $discount = $fromOld ? $discount['discount'] : $discount['pivot']['discount'];
                                        @endphp

                                        <div data-repeater-item="" class="form-group d-flex flex-wrap align-items-center gap-5">
                                            <div class="w-100 w-md-200px">
                                                <select class="form-select" name="discount_type_id" data-placeholder="Select Discount">
                                                    <option value="">Discount</option>

                                                    @foreach($discountTypes as $discountType)
                                                        <option value="{{ $discountType->id }}" {{ $discountType->id == $discountId ? 'selected' : '' }}>{{ $discountType->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="w-100 w-md-200px">
                                                <select class="form-select" name="type" data-placeholder="Select a variation">
                                                    <option value="">Type</option>
                                                    <option {{ $type == 'percentage' ? 'selected="selected"' : '' }} value="percentage">Percentage</option>
                                                    <option {{ $type == 'amount' ? 'selected="selected"' : '' }} value="amount">Amount</option>
                                                </select>
                                            </div>
    
                                            <input value="{{ $discount }}" type="text" class="form-control mw-100 w-200px" name="discount" placeholder="Discount" />

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

                <button type="submit" class="btn btn-primary mt-5 disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>


<style>
    .image-input-placeholder {
        background-image: url('/assets/media/avatars/blank.png');
        background-size: contain;
        border: 1px dashed #92A0B3;
    }

    [data-bs-theme="dark"] .image-input-placeholder {
        background-image: url('svg/avatars/blank-dark.svg');
    }
</style>