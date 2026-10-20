<x-default-layout>

    @section('title')
        Journals
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.journals.index', $company) }}
    @endsection

    <div class="card" data-dt-card>
        <div class="card-header border-0 pt-6">
            <div class="card-title"></div>
            <div class="card-toolbar">
                <a href="{{ route('company.journals.create', ['companySlug' => $company->slug]) }}" class="btn btn-primary">
                    {!! getIcon('plus', 'fs-2', '', 'i') !!}
                    New Journal
                </a>
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
