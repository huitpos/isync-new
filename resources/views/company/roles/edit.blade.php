<x-default-layout>

    @section('title')
        Edit a role
    @endsection

    @section('breadcrumbs')
        {{-- {{ Breadcrumbs::render('company.supplierTerms.create', $company) }} --}}
    @endsection

    @php
        $rolePermissions = $role->permissions()->pluck('id')->toArray();
    @endphp
    
    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('company.roles.update', ['companySlug' => $company->slug, 'role' => $role->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-10">
                    <label class="form-label">Name</label>
                    <input value="{{ old('name') ?? $role->name }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Permissions</label>

                    <ul class="nav nav-pills nav-pills-custom mb-3">
                        <li class="nav-item mb-3 me-3 me-lg-6">
                            <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden  pt-5 pb-5 active" id="kt_stats_widget_16_tab_link_1" data-bs-toggle="pill" href="#company_permissions">
                                <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Company</span>
                                <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                            </a>
                        </li>

                        <li class="nav-item mb-3 me-3 me-lg-6">
                            <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden  pt-5 pb-5" id="kt_stats_widget_16_tab_link_1" data-bs-toggle="pill" href="#branch_permissions">
                                <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Branch</span>
                                <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                            </a>
                        </li>

                        <li class="nav-item mb-3 me-3 me-lg-6">
                            <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden  pt-5 pb-5" id="kt_stats_widget_16_tab_link_1" data-bs-toggle="pill" href="#pos_permissions">
                                <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">POS</span>
                                <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="company_permissions">
                            <div class="table-responsive">
                                <table class="table table-hover table-rounded table-striped border gy-3 gs-3">
                                    <tbody>
                                        @foreach ($companyPermissions as $permission)
                                            <tr>
                                                <td class="fw-bold">{{ $permission->name }}</td>
                                                <td class="text-end">
                                                    <input {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }} name="permission[]" value="{{ $permission->id }}" type="checkbox" data-permission-id="{{ $permission->id }}" class="form-check-input permission-parent">
                                                </td>
                                            </tr>

                                            @foreach ($permission->children as $child)
                                                <tr>
                                                    <td class="ps-10">{{ $child->name }}</td>
                                                    <td class="text-end">
                                                        <input {{ in_array($child->id, $rolePermissions) ? 'checked' : '' }} name="permission[]" value="{{ $child->id }}" data-parent-id="{{ $child->parent_id }}" type="checkbox" class="form-check-input permission-child" data-permission-id="{{ $child->id }}">
                                                    </td>
                                                </tr>

                                                @foreach ($child->children as $grandchild)
                                                    <tr>
                                                        <td class="ps-20">{{ $grandchild->name }}</td>
                                                        <td class="text-end">
                                                            <input {{ in_array($grandchild->id, $rolePermissions) ? 'checked' : '' }} name="permission[]" value="{{ $grandchild->id }}" data-parent-id="{{ $grandchild->parent_id }}" type="checkbox" class="form-check-input permission-grandchild" data-grandparent-id="{{ $permission->id }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade show" id="branch_permissions">
                            <div class="table-responsive">
                                <table class="table table-hover table-rounded table-striped border gy-3 gs-3">
                                    <tbody>
                                        @foreach ($branchPermissions as $permission)
                                            <tr>
                                                <td class="fw-bold">{{ $permission->name }}</td>
                                                <td class="text-end">
                                                    <input {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }} name="permission[]" value="{{ $permission->id }}" type="checkbox" data-permission-id="{{ $permission->id }}" class="form-check-input permission-parent">
                                                </td>
                                            </tr>

                                            @foreach ($permission->children as $child)
                                                <tr>
                                                    <td class="ps-10">{{ $child->name }}</td>
                                                    <td class="text-end">
                                                        <input {{ in_array($child->id, $rolePermissions) ? 'checked' : '' }} name="permission[]" value="{{ $child->id }}" data-parent-id="{{ $child->parent_id }}" type="checkbox" class="form-check-input permission-child" data-permission-id="{{ $child->id }}">
                                                    </td>
                                                </tr>

                                                @foreach ($child->children as $grandchild)
                                                    <tr>
                                                        <td class="ps-20">{{ $grandchild->name }}</td>
                                                        <td class="text-end">
                                                            <input {{ in_array($grandchild->id, $rolePermissions) ? 'checked' : '' }} name="permission[]" value="{{ $grandchild->id }}" data-parent-id="{{ $grandchild->parent_id }}" type="checkbox" class="form-check-input permission-grandchild" data-grandparent-id="{{ $permission->id }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade show" id="pos_permissions">
                            <div class="table-responsive">
                                <table class="table table-hover table-rounded table-striped border gy-3 gs-3">
                                    <tbody>
                                        @foreach ($posPermissions as $permission)
                                            <tr>
                                                <td class="fw-bold">{{ $permission->name }}</td>
                                                <td class="text-end">
                                                    <input {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }} name="permission[]" value="{{ $permission->id }}" type="checkbox" data-permission-id="{{ $permission->id }}" class="form-check-input permission-parent">
                                                </td>
                                            </tr>

                                            @foreach ($permission->children as $child)
                                                <tr>
                                                    <td class="ps-10">{{ $child->name }}</td>
                                                    <td class="text-end">
                                                        <input {{ in_array($child->id, $rolePermissions) ? 'checked' : '' }} name="permission[]" value="{{ $child->id }}" data-parent-id="{{ $child->parent_id }}" type="checkbox" class="form-check-input permission-child" data-permission-id="{{ $child->id }}">
                                                    </td>
                                                </tr>

                                                @foreach ($child->children as $grandchild)
                                                    <tr>
                                                        <td class="ps-20">{{ $grandchild->name }}</td>
                                                        <td class="text-end">
                                                            <input {{ in_array($grandchild->id, $rolePermissions) ? 'checked' : '' }} name="permission[]" value="{{ $grandchild->id }}" data-parent-id="{{ $grandchild->parent_id }}" type="checkbox" class="form-check-input permission-grandchild" data-grandparent-id="{{ $permission->id }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-5 disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
