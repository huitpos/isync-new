<x-default-layout>

    @section('title')
        Create a new cluster
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.clusters.create') }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('branch.users.store', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="form-label">Email</label>
                    <input value="{{ old('email') }}" autocomplete="off" name="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email" required/>

                    @error('email')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Username</label>
                    <input value="{{ old('username') }}" name="username" type="text" class="form-control @error('username') is-invalid @enderror" placeholder="Username" required/>

                    @error('username')
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
                    <label class="form-label">First Name</label>
                    <input value="{{ old('first_name') }}" name="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" placeholder="First Name" required/>

                    @error('first_name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Last Name</label>
                    <input value="{{ old('last_name') }}" name="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" placeholder="Last Name" required/>

                    @error('last_name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 mt-5">
                    <input value="1" checked name="is_active" class="form-check-input" type="checkbox" id="is_active">
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
