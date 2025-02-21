<x-default-layout>

    @section('title')
        Create a new product disposal
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.productDisposals.create', $company, $branch) }}
    @endsection

    @error('pr_items')
        <div class="alert alert-danger">Select at least 1 item</div>
    @enderror

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('branch.product-disposals.store', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Requested By</label>
                        <input value="{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}" type="text" disabled class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select id="department_id" name="department_id" class="form-select pr_department_id @error('department_id') is-invalid @enderror" required>
                            <option value="">Select Department</option>
                            <option value="all">All Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ $department->id == old('department_id') ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>

                        @error('department_id')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-12 mb-5">
                        <label class="form-label">Reason</label>
                        <select id="product_disposal_reason_id" name="product_disposal_reason_id" class="form-select @error('product_disposal_reason_id') is-invalid @enderror" required>
                            @foreach($reasons as $reason)
                                <option value="{{ $reason->id }}" {{ $reason->id == old('product_disposal_reason_id') ? 'selected' : '' }}>{{ $reason->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-12 mb-5">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control"></textarea>
                    </div>
                </div>

                <div class="mt-7">
                    <label class="form-label">Items</label>
                    <hr>
                    <!--begin::Repeater-->
                    <div class="repeater" data-init-empty="{{ empty(old('pr_items')) }}">
                        <!--begin::Form group-->
                        <div class="form-group">
                            <div data-repeater-list="pr_items">
                                @php
                                    $old = old();
                                    $items = old('pr_items');
                                @endphp
                                @if (empty($items))
                                    <div data-repeater-item>
                                        <div class="form-group row mb-5">
                                            <div class="col-md-4">
                                                <label class="form-label">Product:</label>
                                                <select
                                                    name="product_id"
                                                    data-control="select2"
                                                    data-ajax-url="/ajax/get-products"
                                                    data-placeholder="Select a product"
                                                    class="form-control @error('company_id') is-invalid @enderror select2-ajax pr_product_id"
                                                    data-param-name="department_id"
                                                    data-param-link="#department_id"
                                                    required
                                                ></select>

                                                <input name="pr_selected_product_text" type="hidden" class="pr_selected_product_text">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">UOM:</label>
                                                <select data-control="select2" name="uom_id" data-placeholder="Select UOM" class="form-control @error('company_id') is-invalid @enderror select2-ajax pr_uom_id" required>
                                                </select>

                                                <input name="pr_selected_uom_text" type="hidden" class="pr_selected_uom_text">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">Barcode:</label>
                                                <input readonly type="text" class="form-control barcode"/>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">Quantity:</label>
                                                <input name="quantity" value="0" type="text" class="form-control"/>
                                            </div>

                                            <div class="col-md-12 mt-3">
                                                <label class="form-label">Remarks:</label>
                                                <textarea name="remarks" class="form-control"></textarea>
                                            </div>

                                            <div class="col-md-12">
                                                <a href="javascript:;" data-repeater-delete class="btn btn-sm btn-flex btn-light-danger mt-3">
                                                    Remove Item
                                                </a>
                                            </div>
                                        </div>

                                        <hr>
                                    </div>
                                @else
                                    @foreach ($items as $key => $item)
                                        <div data-repeater-item>
                                            <div class="form-group row mb-5">
                                                <div class="col-md-4">
                                                    <label class="form-label">Product:</label>
                                                    <select
                                                        name="product_id"
                                                        data-control="select2"
                                                        data-ajax-url="/ajax/get-products"
                                                        data-placeholder="Select a product"
                                                        class="form-control @error('company_id') is-invalid @enderror select2-ajax pr_product_id"
                                                        data-param-name="department_id"
                                                        data-param-link="#department_id"
                                                        required
                                                    >
                                                        <option value="{{ $item['product_id'] ?? '' }}" selected="selected">{{ $item['pr_selected_product_text'] ?? '' }}</option>
                                                    </select>

                                                    <input name="pr_selected_product_text" value="{{ $item['pr_selected_product_text'] ?? '' }}" type="hidden" class="pr_selected_product_text">
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label">UOM:</label>
                                                    <select data-control="select2" name="uom_id" data-placeholder="Select UOM" class="form-control @error('pr_items.' . $key . '.uom_id') is-invalid @enderror select2-ajax pr_uom_id" required>
                                                        @if (!empty($item['uom_id']))
                                                            <option value="{{ $item['uom_id'] }}" selected="selected">{{ $item['pr_selected_uom_text'] ?? '' }}</option>
                                                        @endif
                                                    </select>
    
                                                    <input name="pr_selected_uom_text" value="{{ $item['pr_selected_uom_text'] ?? '' }}" type="hidden" class="pr_selected_uom_text">

                                                    <div class="invalid-feedback">
                                                        @error('pr_items.' . $key . '.uom_id')
                                                            <p>{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label">Barcode:</label>
                                                    <input readonly value="{{ $item['barcode'] ?? '' }}" name="barcode" type="text" class="form-control barcode"/>
                                                </div>

                                                <div class="col-md-2">
                                                    <label class="form-label">Quantity:</label>
                                                    <input name="quantity" value="{{ $item['quantity'] }}" type="text" class="form-control @error('pr_items.' . $key . '.quantity') is-invalid @enderror"/>

                                                    <div class="invalid-feedback">
                                                        @error('pr_items.' . $key . '.quantity')
                                                            <p>{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-12 mt-3">
                                                    <label class="form-label">Remarks:</label>
                                                    <textarea name="remarks" class="form-control">{{ $item['remarks'] }}</textarea>
                                                </div>
    
                                                <div class="col-md-12">
                                                    <a href="javascript:;" data-repeater-delete class="btn btn-sm btn-flex btn-light-danger mt-3">
                                                        Remove Item
                                                    </a>
                                                </div>
                                            </div>

                                            <hr>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <!--end::Form group-->

                        <!--begin::Form group-->
                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <a href="javascript:;" data-repeater-create class="btn btn-flex btn-light-primary">
                                        <i class="ki-duotone ki-plus fs-3"></i>
                                        Add Item
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!--end::Form group-->
                    </div>
                    <!--end::Repeater-->
                </div>


                <div class="mt-8">
                    <button type="submit" class="btn btn-primary disable-on-click">Submit</button>
                    <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-default-layout>