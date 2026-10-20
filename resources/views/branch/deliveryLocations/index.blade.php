<x-default-layout>

    @section('title')
        Delivery Locations
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.deliveryLocations.index', $company, $branch) }}
    @endsection

    <div class="card" data-dt-card>
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                    <a href="{{ route('branch.delivery-locations.create', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]) }}" class="btn btn-primary">
                        {!! getIcon('plus', 'fs-2', '', 'i') !!}
                        Add Delivery Location
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
