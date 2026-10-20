<x-default-layout>

    @section('title')
        Supplier Invoices
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.supplierInvoices.index', $company) }}
    @endsection

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
                    <option value="matched">Matched</option>
                    <option value="variance">Variance</option>
                    <option value="accepted">Accepted</option>
                    <option value="void">Void</option>
                </select>

                <a href="{{ route('company.supplier-invoices.create', ['companySlug' => $company->slug]) }}" class="btn btn-primary">Add Supplier Invoice</a>
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
