<x-default-layout>

    @section('title')
        Table Statuses
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    {!! getIcon('magnifier', 'fs-3 position-absolute ms-5') !!}
                    <input type="text" data-kt-user-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Search" id="searchBar"/>
                </div>
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                    <a href="{{ route('branch.table-statuses.create', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]) }}" class="btn btn-primary">
                        {!! getIcon('plus', 'fs-2', '', 'i') !!}
                        Add Status
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body py-4">
            <div class="table-responsive">
                <table id="kt_datatable_zero_configuration" class="table table-striped table-row-bordered gy-5">
                    <thead>
                        <tr class="fw-semibold fs-6 text-muted">
                            <th>Name</th>
                            <th>Color</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statuses as $status)
                            <tr>
                                <td>
                                    <div style="width: 20px; height: 20px; background-color: {{ $status->color }};"></div>
                                    {{ $status->name }}
                                </td>
                                <td>{{ $status->color }}</td>
                                <td>{{ $status->status }}</td>
                                <td>
                                    @if($status->branch_id)
                                    <a href="{{ route('branch.table-statuses.edit', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug, 'table_status' => $status->id]) }}" class="">
                                        <i class="fa-regular fa-pen-to-square fs-2" title="Edit"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $("#kt_datatable_zero_configuration").DataTable();
        </script>
    @endpush

</x-default-layout>
