<x-default-layout>
    @section('title')
        Inventory
    @endsection

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Choose a branch</h2>
        </div>
        <div class="card-body">
            <p class="text-gray-600">Branch stock is the quantity the POS uses. Inventory tracking is the movement history.</p>
            <div class="row g-4">
                @foreach ($branches as $branch)
                    <div class="col-md-4">
                        <a class="btn btn-light-primary w-100 py-6" href="{{ route('company.branch-inventory.index', ['companySlug' => $company->slug, 'branchId' => $branch->id]) }}">
                            {{ $branch->name }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-default-layout>
