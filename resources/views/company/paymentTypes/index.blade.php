<x-default-layout>

    @section('title')
        Payment Types
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.paymentTypes.index', $company) }}
    @endsection

    <div class="card" data-dt-card>
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                    @if (in_array('Settings/Payment Types/Add', $permissions))
                    <a href="{{ route('company.payment-types.create', ['companySlug' => $company->slug]) }}" class="btn btn-primary">
                        {!! getIcon('plus', 'fs-2', '', 'i') !!}
                        Add Payment Type
                    </a>
                    @endif
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
