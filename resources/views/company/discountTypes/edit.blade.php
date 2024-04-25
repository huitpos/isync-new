<x-default-layout>

    @section('title')
        Edit discount type
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.discountTypes.edit', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('company.discount-types.update', ['companySlug' => $company->slug, 'discount_type' => $discountType->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' || $discountType->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' || $discountType->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Discount Name</label>
                    <input value="{{ old('name') ?? $discountType->name }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Discount Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <input value="{{ old('description') ?? $discountType->description }}" name="description" type="text" class="form-control @error('description') is-invalid @enderror" placeholder="Description" required/>

                    @error('description')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    @php
                        $selectedDepartments = [];
                        foreach ($discountType->departments as $department) {
                            $selectedDepartments[] = $department->id;
                        }
                    @endphp

                    <label class="form-label">Departments</label>
                    <select class="form-select" name="departments[]" data-control="select2" data-close-on-select="false" data-placeholder="Select department" data-allow-clear="true" multiple="multiple">
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ in_array($department->id, $selectedDepartments) ? 'selected' : '' }}>{{ $department->name }}</option>
                        @endforeach
                    </select>

                    @error('departments')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Type</label>
                    <select class="form-select @error('type') is-invalid @enderror" name="type">
                        <option {{ $discountType->type == 'amount' ? 'selected' : '' }} value="amount">Amount</option>
                        <option {{ $discountType->type == 'percentage' ? 'selected' : '' }} value="percentage">Percentage</option>
                    </select>

                    @error('type')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Discount</label>
                    <input value="{{ old('discount') ?? $discountType->discount }}" name="discount" type="text" class="form-control @error('discount') is-invalid @enderror" placeholder="Discount" required/>

                    @error('discount')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-5">
                    <input {{ $discountType->is_vat_exempt ? 'checked' : '' }} value="1" name="is_vat_exempt" class="form-check-input" type="checkbox" id="is_vat_exempt">
                    <label class="form-check-label" for="is_vat_exempt">
                        Is Vat Exempt
                    </label>
                </div>

                <div class="mb-4 mt-5">
                    <input {{ $discountType->is_zero_rated ? 'checked' : '' }} value="1" name="is_zero_rated" class="form-check-input" type="checkbox" id="is_zero_rated">
                    <label class="form-check-label" for="is_zero_rated">
                        Is Zero Rated Sales
                    </label>
                </div>

                <div class="mt-7">
                    <label class="form-label">Fields</label>
                    <!--begin::Repeater-->
                    <div class="repeater">
                        <!--begin::Form group-->
                        <div class="form-group">
                            <div data-repeater-list="discount_type_fields">
                                @if (empty(old('raw_items')) && count($discountType->fields) == 0)
                                    <div data-repeater-item>
                                        <div class="form-group row mb-5">
                                            <div class="col-md-3">
                                                <label class="form-label">Field Name:</label>
                                                <input name="name" class="form-control mb-2 mb-md-0" placeholder="Field Name"/>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">Field Type:</label>
                                                <select name="field_type" class="form-control">
                                                    <option value=""></option>
                                                    <option value="textbox">Textbox</option>
                                                    <option value="select">Select</option>
                                                    <option value="radio">Radio</option>
                                                    <option value="checkbox">Checkbox</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="inner-repeater">
                                                    <div data-repeater-list="options" class="mb-5">
                                                        <div data-repeater-item>
                                                            <label class="form-label">Option:</label>
                                                            <input name="option" class="form-control mb-2 mb-md-0" placeholder="Option"/>
                                                        </div>
                                                    </div>
                                                    <button class="btn btn-sm btn-flex btn-light-primary" data-repeater-create type="button">
                                                        <i class="ki-duotone ki-plus fs-5"></i>
                                                        Add Option
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <a href="javascript:;" data-repeater-delete class="btn btn-sm btn-flex btn-light-danger mt-3 mt-md-9">
                                                    <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                    Delete Field
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    @php
                                        $fromOld = !empty(old('discount_type_fields'));
                                        $fields = old('discount_type_fields') ?? [];


                                        if (!$fromOld) {
                                            $fields = $discountType->fields->toArray();
                                        }
                                    @endphp

                                    @foreach ($fields as $key => $field)
                                        <div data-repeater-item>
                                            <div class="form-group row mb-5">
                                                <div class="col-md-3">
                                                    <label class="form-label">Field Name:</label>
                                                    <input value="{{ $field['name'] }}" name="name" class="form-control mb-2 mb-md-0" placeholder="Field Name"/>
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label">Field Type:</label>
                                                    <select name="field_type" class="form-control @error('discount_type_fields.' . $key . '.field_type') is-invalid @enderror">
                                                        <option value=""></option>
                                                        <option value="textbox" {{ $field['field_type'] == 'textbox' ? 'selected' : '' }}>Textbox</option>
                                                        <option value="select" {{ $field['field_type'] == 'select' ? 'selected' : '' }}>Select</option>
                                                        <option value="radio" {{ $field['field_type'] == 'radio' ? 'selected' : '' }}>Radio</option>
                                                        <option value="checkbox" {{ $field['field_type'] == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                                    </select>

                                                    @error('discount_type_fields.' . $key . '.field_type')
                                                        <div class="invalid-feedback"> {{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="inner-repeater">
                                                        <div data-repeater-list="options" class="mb-5">
                                                            @if (empty($field['options']))
                                                            <div data-repeater-item>
                                                                <label class="form-label">Option:</label>
                                                                <input name="option" class="form-control mb-2 mb-md-0 @error('discount_type_fields.' . $key . '.options') is-invalid @enderror" placeholder="Option"/>

                                                                @error('discount_type_fields.' . $key . '.options')
                                                                    <div class="invalid-feedback"> {{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            @else
                                                                @foreach ($field['options'] as $option)
                                                                    <div data-repeater-item>
                                                                        <label class="form-label">Option:</label>
                                                                        <input value="{{ $option['option'] ?? $option }}" name="option" class="form-control mb-2 mb-md-0 @error('discount_type_fields.' . $key . '.options') is-invalid @enderror" placeholder="Option"/>
        
                                                                        @error('discount_type_fields.' . $key . '.options')
                                                                            <div class="invalid-feedback"> {{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                        <button class="btn btn-sm btn-flex btn-light-primary" data-repeater-create type="button">
                                                            <i class="ki-duotone ki-plus fs-5"></i>
                                                            Add Option
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <a href="javascript:;" data-repeater-delete class="btn btn-sm btn-flex btn-light-danger mt-3 mt-md-9">
                                                        <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                        Delete Row
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <!--end::Form group-->

                        <!--begin::Form group-->
                        <div class="form-group">
                            <a href="javascript:;" data-repeater-create class="btn btn-flex btn-light-primary">
                                <i class="ki-duotone ki-plus fs-3"></i>
                                Add Field
                            </a>
                        </div>
                        <!--end::Form group-->
                    </div>
                    <!--end::Repeater-->
                </div>


                <button type="submit" class="btn btn-primary mt-5 disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
