<x-default-layout>

    @section('title')
        Product Physical Counts
    @endsection

    @section('breadcrumbs')
        {{-- {{ Breadcrumbs::render('branch.stockTransferRequests.index', $company, $branch) }} --}}
    @endsection

    <div class="card" data-dt-card>
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                
                <div id="kt_ecommerce_report_returns_export" class="d-none"></div>
            </div>

            <div class="card-toolbar flex-row-fluid justify-content-end gap-5">

                <select id="status" class="form-select form-control-solid w-100 mw-250px" data-dt-filter="status">
                    <option value="">Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>

                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                    <a href="{{ route('branch.product-physical-counts.create', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]) }}" class="btn btn-primary">
                        {!! getIcon('plus', 'fs-2', '', 'i') !!}
                        Add Product Physical Count
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body py-4">
            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts() }}
        
        
    @endpush

</x-default-layout>
