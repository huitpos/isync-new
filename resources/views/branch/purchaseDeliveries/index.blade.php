<x-default-layout>

    @section('title')
        Purchase Deliveries
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.purchaseDeliveries.index', $company, $branch) }}
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
