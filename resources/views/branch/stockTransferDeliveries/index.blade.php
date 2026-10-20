<x-default-layout>

    @section('title')
        Stock Transfer Deliveries
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.stockTransferDeliveries.index', $company, $branch) }}
    @endsection

    <div class="card" data-dt-card>
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                
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
