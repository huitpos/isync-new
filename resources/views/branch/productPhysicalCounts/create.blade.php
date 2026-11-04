<x-default-layout>

    @section('title')
        {{ $count ? 'Edit product physical count' : 'Create a new product physical count' }}
    @endsection

    @section('breadcrumbs')
        @if ($count)
            {{ Breadcrumbs::render('branch.productPhysicalCount.edit', $company, $branch, $count) }}
        @else
            {{ Breadcrumbs::render('branch.productPhysicalCount.create', $company, $branch) }}
        @endif
    @endsection

    @error('pr_items')
        <div class="alert alert-danger">Select at least 1 item</div>
    @enderror

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ $count ? route('branch.product-physical-counts.update', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug, 'product_physical_count' => $count->id]) : route('branch.product-physical-counts.store', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @if ($count)
                    @method('PUT')
                @endif

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Requested By</label>
                        <input value="{{ $count ? $count->createdBy->name : Auth::user()->first_name . ' ' . Auth::user()->last_name }}" type="text" disabled class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select id="department_id" name="department_id" class="form-select pr_department_id @error('department_id') is-invalid @enderror" required>
                            <option value="">Select Department</option>
                            <option value="all" {{ old('department_id', $count?->department_id ?? '') == 'all' ? 'selected' : '' }}>All Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ (string) $department->id === (string) old('department_id', $count?->department_id ?? '') ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>

                        @error('department_id')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-12 mb-5">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control">{{ old('remarks', $count?->remarks ?? '') }}</textarea>
                    </div>
                </div>

                <div class="mt-7">
                    <label class="form-label">Items</label>
                    <hr>
                    <!--begin::Repeater-->
                    <div class="repeater" data-init-empty="{{ empty($items) }}">
                        <!--begin::Form group-->
                        <div class="form-group">
                            <div data-repeater-list="pr_items">
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
                                                <input name="barcode" readonly type="text" class="form-control barcode"/>
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
                                                            <option value="{{ $item['uom_id'] }}" selected="selected">{{ $item['pr_selected_uom_text'] }}</option>
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
                                                    <textarea name="remarks" class="form-control">{{ $item['remarks'] ?? '' }}</textarea>
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
                    <input type="hidden" name="status" id="status" value="pending">
                    <button type="submit" class="btn btn-light-primary disable-on-click" data-button-link="#status" value="draft">Save as Draft</button>
                    <button type="submit" class="btn btn-primary disable-on-click ms-2" data-button-link="#status" value="pending">Submit</button>
                    <a href="{{ $count ? route('branch.product-physical-counts.show', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug, 'product_physical_count' => $count->id]) : route('branch.product-physical-counts.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]) }}" class="btn btn-label-secondary waves-effect">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-default-layout>