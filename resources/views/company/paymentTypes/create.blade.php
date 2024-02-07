<x-default-layout>

    @section('title')
        Create a new payment type
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.paymentTypes.create', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('company.payment-types.store', ['companySlug' => $company->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
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
                    <label class="form-label">Name</label>
                    <input value="{{ old('name') }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <input value="{{ old('description') }}" name="description" type="text" class="form-control @error('description') is-invalid @enderror" placeholder="Description"/>

                    @error('description')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-5">
                    <input value="1" name="is_ar" class="form-check-input" type="checkbox" id="is_ar">
                    <label class="form-check-label" for="is_ar">
                        Is Account Receivable?
                    </label>
                </div>

                <div class="mb-4">
                    <label class="form-label">Logo</label><br>

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
                        title="Change logo">
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

                <div class="mt-7">
                    <label class="form-label">Fields</label>
                    <!--begin::Repeater-->
                    <div class="repeater">
                        <!--begin::Form group-->
                        <div class="form-group">
                            <div data-repeater-list="payment_type_fields">
                                @if (empty(old('payment_type_fields')))
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
                                                    <div data-repeater-list="option_list" class="mb-5">
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
                                    @foreach (old('payment_type_fields') as $key => $field)
                                        <div data-repeater-item>
                                            <div class="form-group row mb-5">
                                                <div class="col-md-3">
                                                    <label class="form-label">Field Name:</label>
                                                    <input value="{{ $field['name'] }}" name="name" class="form-control mb-2 mb-md-0" placeholder="Field Name"/>
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label">Field Type:</label>
                                                    <select name="field_type" class="form-control @error('payment_type_fields.' . $key . '.field_type') is-invalid @enderror">
                                                        <option value=""></option>
                                                        <option value="textbox" {{ $field['field_type'] == 'textbox' ? 'selected' : '' }}>Textbox</option>
                                                        <option value="select" {{ $field['field_type'] == 'select' ? 'selected' : '' }}>Select</option>
                                                        <option value="radio" {{ $field['field_type'] == 'radio' ? 'selected' : '' }}>Radio</option>
                                                        <option value="checkbox" {{ $field['field_type'] == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                                    </select>

                                                    @error('payment_type_fields.' . $key . '.field_type')
                                                        <div class="invalid-feedback"> {{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="inner-repeater">
                                                        <div data-repeater-list="options" class="mb-5">
                                                            @if (empty($field['options']))
                                                            <div data-repeater-item>
                                                                <label class="form-label">Option:</label>
                                                                <input name="option" class="form-control mb-2 mb-md-0 @error('payment_type_fields.' . $key . '.options') is-invalid @enderror" placeholder="Option"/>

                                                                @error('payment_type_fields.' . $key . '.options')
                                                                    <div class="invalid-feedback"> {{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            @else
                                                                @foreach ($field['options'] as $option)
                                                                    <div data-repeater-item>
                                                                        <label class="form-label">Option:</label>
                                                                        <input value="{{ $option['option'] }}" name="option" class="form-control mb-2 mb-md-0 @error('payment_type_fields.' . $key . '.options') is-invalid @enderror" placeholder="Option"/>
        
                                                                        @error('payment_type_fields.' . $key . '.options')
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