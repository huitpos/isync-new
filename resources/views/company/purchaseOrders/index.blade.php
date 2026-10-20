<x-default-layout>

    @section('title')
        Purchase Orders
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.purchaseOrders.index', $company) }}
    @endsection

    <div class="alert alert-primary">This is the status of every branch. The purchase order is created when the branch approves the request, or when the company awards quotations.</div>

    <div class="card" data-dt-card>
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                
            </div>

            <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                <select id="branch_id" class="form-control form-control-solid w-100 mw-250px" data-dt-filter="branch_id">
                    <option value="">Branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
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
