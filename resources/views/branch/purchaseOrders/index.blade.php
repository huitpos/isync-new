<x-default-layout>

    @section('title')
        Purchase Orders
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.purchaseOrders.index', $company, $branch) }}
    @endsection

    <div class="card" data-dt-card>
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                
                <!--end::Search-->
                <!--begin::Export buttons-->
                <div id="kt_ecommerce_report_returns_export" class="d-none"></div>
                <!--end::Export buttons-->
            </div>
            <!--end::Card title-->
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
