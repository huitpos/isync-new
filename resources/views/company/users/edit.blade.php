<x-default-layout>

    @section('title')
        Edit user
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.users.edit', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('company.users.update', ['companySlug' => $company->slug, 'user' => $user->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-5">
                    <input value="1" {{ $user->is_active ? 'checked' : '' }} name="is_active" class="form-check-input" type="checkbox" id="is_active">
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>

                <div class="mb-4">
                    @php
                        $roles = $user->getRoleNames()->toArray();
                    @endphp

                    <label class="form-label">Role</label>
                    <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                        @foreach($company->roles as $role)
                            <option value="{{ $role->id }}" {{ (old('role') == $role->id || in_array($role->name, $roles)) ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>

                    @error('role')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Branches</label>
                    <select class="form-select @error('branches') is-invalid @enderror" name="branches[]" data-control="select2" data-close-on-select="false" data-placeholder="Select branch" data-allow-clear="true" multiple="multiple">
                        @foreach ($company->activeBranches as $branch)
                            <option {{ in_array($branch->id, old('branches') ?? $user->activeBranches->pluck('id')->toArray() ?? []) ? 'selected' : '' }} value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>

                    @error('branches')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Email</label>
                    <input value="{{ old('email') ?? $user->email }}" autocomplete="off" name="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email" required/>

                    @error('email')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Username</label>
                    <input value="{{ old('username') ?? $user->username }}" name="username" type="text" class="form-control @error('username') is-invalid @enderror" placeholder="Username" required/>

                    @error('username')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div data-kt-password-meter="true">
                        <div>
                            <div class="position-relative mb-3">
                                <input class="form-control form-control @error('password') is-invalid @enderror"" type="password" placeholder="Password" required name="password" autocomplete="off" />

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
                    <input value="{{ old('first_name') ?? $user->first_name }}" name="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" placeholder="First Name" required/>

                    @error('first_name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Last Name</label>
                    <input value="{{ old('last_name') ?? $user->last_name }}" name="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" placeholder="Last Name" required/>

                    @error('last_name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary mt-5 disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
