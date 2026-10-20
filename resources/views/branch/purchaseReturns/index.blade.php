<x-default-layout>

    @section('title')
        Purchase Returns
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.purchaseReturns.index', $company, $branch) }}
    @endsection

    <x-datatable-card :dataTable="$dataTable">
        <x-slot:filters>
            <select id="status" class="form-select form-select-solid w-150px" data-dt-filter="status">
                <option value="">Status</option>
                @foreach (['pending', 'approved', 'rejected', 'void'] as $status)
                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </x-slot:filters>
        <x-slot:actions>
            <a href="{{ route('branch.purchase-returns.create', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]) }}" class="btn btn-primary">Add Purchase Return</a>
        </x-slot:actions>
    </x-datatable-card>

</x-default-layout>
