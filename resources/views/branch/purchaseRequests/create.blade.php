<x-default-layout>

    @section('title')
        Create a new purchase request
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.purchaseRequests.create', $company, $branch) }}
    @endsection

    @error('pr_items')
        <div class="alert alert-danger">Select at least 1 item</div>
    @enderror

    <form action="{{ route('branch.purchase-requests.store', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="type" value="for_po">

        <div class="card mb-5">
            <div class="card-header">
                <h3 class="card-title">Request</h3>
            </div>
            <div class="card-body">
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Requested By</label>
                        <div class="fw-semibold fs-6 text-gray-800">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <div><span class="badge badge-light-primary">For PO</span></div>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select id="department_id" name="department_id" class="form-select pr_department_id @error('department_id') is-invalid @enderror" required>
                            <option value="">Select Department</option>
                            <option value="all">All departments — products from every department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ $department->id == old('department_id') ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date Needed</label>
                        <div class="position-relative">
                            <input data-min-date="today" value="{{ old('date_needed') }}" name="date_needed" type="text" class="form-control pe-10 @error('date_needed') is-invalid @enderror flatpack-picker" placeholder="Select a date" required/>
                            <i class="fa-solid fa-calendar position-absolute top-50 end-0 translate-middle-y me-4 text-gray-500 pe-none"></i>
                        </div>
                        @error('date_needed')
                            <div class="invalid-feedback d-block"> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Delivery Location</label>
                        <select id="delivery_location_id" name="delivery_location_id" class="form-select @error('delivery_location_id') is-invalid @enderror" required>
                            <option value="">Select Location</option>
                            @foreach($deliveryLocations as $deliveryLocation)
                                @php
                                    $address = $deliveryLocation->unit_floor_number . ' ' . $deliveryLocation->street . ', ' . $deliveryLocation->barangay->name . ', ' . $deliveryLocation->city->name . ', ' . $deliveryLocation->province->name . ', ' . $deliveryLocation->region->name;
                                @endphp
                                <option data-address="{{ $address }}" value="{{ $deliveryLocation->id }}" {{ (string) old('delivery_location_id') === (string) $deliveryLocation->id ? 'selected' : '' }}>{{ $deliveryLocation->name }}</option>
                            @endforeach
                        </select>
                        <input id="delivery_address" type="text" readonly tabindex="-1" class="form-control-plaintext form-control-sm px-0 mt-1 text-gray-600 d-none">
                        @error('delivery_location_id')
                            <div class="invalid-feedback d-block"> {{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Supplier</label>
                        <select id="supplier_id" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ $supplier->id == old('supplier_id') ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Leave blank if the company will request quotations.</div>
                        @error('supplier_id')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" rows="2" class="form-control">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Items</h3>
            </div>
            <div class="card-body">
                <div class="repeater" data-init-empty="{{ empty(old('pr_items')) }}">
                    <div class="form-group">
                        <div data-repeater-list="pr_items">
                            @if (empty(old('pr_items')))
                                <div data-repeater-item>
                                    <div class="border border-gray-300 rounded p-4 mb-4">
                                        <div class="form-group row g-3 align-items-start">
                                            <div class="col-md-4">
                                                <label class="form-label">Product</label>
                                                <select
                                                    name="product_id"
                                                    data-control="select2"
                                                    data-ajax-url="/ajax/get-products"
                                                    data-placeholder="Select a product"
                                                    class="form-control select2-ajax pr_product_id"
                                                    data-param-name="department_id"
                                                    data-param-link="#department_id"
                                                    required
                                                ></select>
                                                <input name="pr_selected_product_text" type="hidden" class="pr_selected_product_text">
                                                <input name="barcode" type="hidden" class="barcode">
                                                <div class="form-text">Barcode: <span class="barcode-text">—</span></div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">UOM</label>
                                                <select data-control="select2" name="uom_id" data-placeholder="Select UOM" class="form-control select2-ajax pr_uom_id" required></select>
                                                <input name="pr_selected_uom_text" type="hidden" class="pr_selected_uom_text">
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label">Qty</label>
                                                <input name="quantity" value="" type="number" min="0.0001" step="0.0001" class="form-control pr_quantity" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Unit Price</label>
                                                <input readonly name="unit_price" type="text" class="form-control unit_price">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Total</label>
                                                <input readonly name="total" value="0" type="text" class="form-control pr_item_total text-end">
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label d-block">&nbsp;</label>
                                                <a href="javascript:;" data-repeater-delete class="btn btn-icon btn-sm btn-light-danger" title="Remove item">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <input name="remarks" type="text" class="form-control" placeholder="Item note">
                                        </div>
                                    </div>
                                </div>
                            @else
                                @foreach (old('pr_items') as $key => $item)
                                    <div data-repeater-item>
                                        <div class="border border-gray-300 rounded p-4 mb-4">
                                            <div class="form-group row g-3 align-items-start">
                                                <div class="col-md-4">
                                                    <label class="form-label">Product</label>
                                                    <select
                                                        name="product_id"
                                                        data-control="select2"
                                                        data-ajax-url="/ajax/get-products"
                                                        data-placeholder="Select a product"
                                                        class="form-control select2-ajax pr_product_id @error('pr_items.' . $key . '.product_id') is-invalid @enderror"
                                                        data-param-name="department_id"
                                                        data-param-link="#department_id"
                                                        required
                                                    >
                                                        <option value="{{ $item['product_id'] ?? '' }}" selected="selected">{{ $item['pr_selected_product_text'] ?? '' }}</option>
                                                    </select>
                                                    <input name="pr_selected_product_text" value="{{ $item['pr_selected_product_text'] ?? '' }}" type="hidden" class="pr_selected_product_text">
                                                    <input name="barcode" value="{{ $item['barcode'] ?? '' }}" type="hidden" class="barcode">
                                                    <div class="form-text">Barcode: <span class="barcode-text">{{ $item['barcode'] ?: '—' }}</span></div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">UOM</label>
                                                    <select data-control="select2" name="uom_id" data-placeholder="Select UOM" class="form-control select2-ajax pr_uom_id @error('pr_items.' . $key . '.uom_id') is-invalid @enderror" required>
                                                        @if (!empty($item['uom_id']))
                                                            <option value="{{ $item['uom_id'] }}" selected="selected">{{ $item['pr_selected_uom_text'] }}</option>
                                                        @endif
                                                    </select>
                                                    <input name="pr_selected_uom_text" value="{{ $item['pr_selected_uom_text'] ?? '' }}" type="hidden" class="pr_selected_uom_text">
                                                    @error('pr_items.' . $key . '.uom_id')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-1">
                                                    <label class="form-label">Qty</label>
                                                    <input name="quantity" value="{{ $item['quantity'] }}" type="number" min="0.0001" step="0.0001" class="form-control pr_quantity @error('pr_items.' . $key . '.quantity') is-invalid @enderror" required>
                                                    @error('pr_items.' . $key . '.quantity')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Unit Price</label>
                                                    <input readonly name="unit_price" value="{{ $item['unit_price'] }}" type="text" class="form-control unit_price">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Total</label>
                                                    <input readonly name="total" value="{{ $item['total'] }}" type="text" class="form-control pr_item_total text-end">
                                                </div>
                                                <div class="col-md-1">
                                                    <label class="form-label d-block">&nbsp;</label>
                                                    <a href="javascript:;" data-repeater-delete class="btn btn-icon btn-sm btn-light-danger" title="Remove item">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <input name="remarks" value="{{ $item['remarks'] ?? '' }}" type="text" class="form-control" placeholder="Item note">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <a href="javascript:;" data-repeater-create class="btn btn-flex btn-light-primary">
                        <i class="ki-duotone ki-plus fs-3"></i>
                        Add Item
                    </a>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center border-top pt-5 mt-6 gap-4">
                    <div class="text-gray-600 pr-item-count">0 items</div>
                    <div class="d-flex flex-wrap align-items-center gap-4">
                        <div class="text-end">
                            <input type="hidden" name="total" id="pr_total" value="{{ old('total') ?? 0 }}">
                            <div class="text-gray-600 fs-7">Total</div>
                            <div class="fs-2 fw-bold grandtotal">{{ old('total') ?? '0.00' }}</div>
                        </div>
                        <a href="{{ url()->previous() }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary disable-on-click">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            function updatePrItemCount() {
                var count = $('.repeater [data-repeater-item]:visible').length;
                $('.pr-item-count').text(count + (count === 1 ? ' item' : ' items'));
            }

            $(document).on('keyup input', '.pr_quantity', function () {
                setTimeout(updatePrItemCount, 0);
            });

            $(document).on('click', '[data-repeater-create], [data-repeater-delete]', function () {
                setTimeout(updatePrItemCount, 450);
            });

            $(updatePrItemCount);
        </script>
    @endpush
</x-default-layout>
