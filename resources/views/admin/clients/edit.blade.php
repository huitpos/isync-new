<x-default-layout>

    @section('title')
        Clients
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.clients.edit') }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('admin.clients.update', ['client' => $company->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' || $company->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' || $company->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <h3>Company Registered Name</h3>

                <div class="mb-4 mt-5">
                    <label class="form-label">Company Name</label>
                    <input value="{{ old('company_name') ?? $company->company_name }}" name="company_name" type="text" class="form-control @error('company_name') is-invalid @enderror" required/>

                    @error('company_name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Trade Name</label>
                    <input value="{{ old('trade_name') ?? $company->trade_name }}" name="trade_name" type="text" class="form-control @error('trade_name') is-invalid @enderror" placeholder="Trade Name" required/>

                    @error('trade_name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Company Logo</label><br>

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

                <div class="mb-4">
                    <label class="form-label">Owner Name</label>
                    <input value="{{ old('owner_name') ?? $company->client->name }}" name="owner_name" type="text" class="form-control @error('owner_name') is-invalid @enderror" placeholder="Owner Name" required/>

                    @error('owner_name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Country</label>
                    <input readonly="readonly" value="{{ old('country') ?? 'Philippines' }}" name="country" type="text" class="form-control @error('country') is-invalid @enderror" placeholder="Country" required/>
                </div>

                <div class="mb-4">
                    <label class="form-label">Contact Number</label>
                    <input value="{{ old('phone_number') ?? $company->phone_number }}" name="phone_number" type="text" class="form-control @error('phone_number') is-invalid @enderror" placeholder="Contact Number" required/>

                    @error('phone_number')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <h3>Address</h3>

                <div class="mb-4">
                    <label class="form-label">Unit No./Floor Bldg.</label>
                    <input value="{{ old('unit_floor_number') ?? $company->unit_floor_number }}" name="unit_floor_number" type="text" class="form-control @error('unit_floor_number') is-invalid @enderror" placeholder="Unit No./Floor Bldg." required/>

                    @error('unit_floor_number')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Street</label>
                    <input value="{{ old('street') ?? $company->street }}" name="street" type="text" class="form-control @error('street') is-invalid @enderror" placeholder="Street" required/>

                    @error('street')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Region</label>
                    <select id="region_id" name="region_id" data-control="select2" data-placeholder="Select a region" class="form-control @error('region_id') is-invalid @enderror" required>
                        <option value=""></option>
                        @foreach ($regions as $region)
                            <option value="{{ $region->id }}" {{ $region->id == old('region_id') || $region->id == $company->region_id ? 'selected' : '' }}>{{ $region->name }}</option>
                        @endforeach
                    </select>

                    @error('region_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Province</label>
                    <select id="province_id" name="province_id" data-control="select2" data-placeholder="Select a province" class="form-control @error('province_id') is-invalid @enderror" required>
                        <option value=""></option>
                        @if (!empty($provinces))
                            @foreach ($provinces as $province)
                                <option value="{{ $province->id }}" {{ $province->id == old('province_id') || $province->id == $company->province_id ? 'selected' : '' }}>{{ $province->name }}</option>
                            @endforeach
                        @endif
                    </select>

                    @error('province_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">City</label>
                    <select id="city_id" name="city_id" data-control="select2" data-placeholder="Select a city" class="form-control @error('city_id') is-invalid @enderror" required>
                        <option value=""></option>
                        @if (!empty($cities))
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" {{ $city->id == old('city_id') || $city->id == $company->city_id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        @endif
                    </select>

                    @error('city_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Barangay</label>
                    <select id="barangay_id" name="barangay_id" data-control="select2" data-placeholder="Select a barangay" class="form-control @error('barangay_id') is-invalid @enderror" required>
                        <option value=""></option>
                        @if (!empty($barangays))
                            @foreach ($barangays as $barangay)
                                <option value="{{ $barangay->id }}" {{ $barangay->id == old('barangay_id') || $barangay->id == $company->barangay_id ? 'selected' : '' }}>{{ $barangay->name }}</option>
                            @endforeach
                        @endif
                    </select>

                    @error('barangay_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">POS Type</label>
                    <select class="form-select @error('pos_type') is-invalid @enderror" name="pos_type">
                        <option value="">Select a POS Type</option>
                        <option value="retail" {{ old('pos_type') == 'retail' || $company->pos_type == 'retail' ? 'selected' : '' }}>Retail</option>
                        <option value="restaurant" {{ old('pos_type') == 'restaurant' || $company->pos_type == 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                        <option value="hospitality" {{ old('pos_type') == 'hospitality' || $company->pos_type == 'hospitality' ? 'selected' : '' }}>Hospitality</option>
                    </select>

                    @error('pos_type')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <h3>Account Registration</h3>

                <div class="mb-4">
                    <label class="form-label">Email</label>
                    <input value="{{ old('email') ?? $company->client->user->email }}" autocomplete="off" name="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email Address" required/>

                    @error('email')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div data-kt-password-meter="true">
                        <div>
                            <div class="position-relative mb-3">
                                <input class="form-control form-control @error('password') is-invalid @enderror"" type="password" placeholder="" required name="password" autocomplete="off" />

                                @error('password')
                                    <div class="invalid-feedback"> {{ $message }}</div>
                                @enderror

                                <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2"
                                    data-kt-password-meter-control="visibility">
                                        <i class="ki-duotone ki-eye-slash fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                        <i class="ki-duotone ki-eye d-none fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirm Passwword</label>
                    <div data-kt-password-meter="true">
                        <div>
                            <div class="position-relative mb-3">
                                <input class="form-control form-control @error('password_confirmation') is-invalid @enderror"" type="password" placeholder="" required name="password_confirmation" autocomplete="off" />

                                @error('password_confirmation')
                                    <div class="invalid-feedback"> {{ $message }}</div>
                                @enderror

                                <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2"
                                    data-kt-password-meter-control="visibility">
                                        <i class="ki-duotone ki-eye-slash fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                        <i class="ki-duotone ki-eye d-none fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
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