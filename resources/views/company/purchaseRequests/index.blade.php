<x-default-layout>

    @section('title')
        Purchase Requests
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.purchaseRequests.index', $company) }}
    @endsection

    <div class="alert alert-primary">This is the status of every branch. Create and approve the request on the branch.</div>

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

                <select id="status" class="form-control form-control-solid w-100 mw-250px" data-dt-filter="status">
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
        
        <script>
            

            

            
        </script>
    @endpush

</x-default-layout>
