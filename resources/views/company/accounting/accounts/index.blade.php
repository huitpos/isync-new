<x-default-layout>

    @section('title')
        Chart of Accounts
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.accounts.index', $company) }}
    @endsection

    <div class="card" data-dt-card>
        <div class="card-header border-0 pt-6">
            <div class="card-title"></div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end gap-2">
                    @if (!$hasAccounts)
                        <form method="POST" action="{{ route('company.accounts.starter', ['companySlug' => $company->slug]) }}">
                            @csrf
                            <button type="submit" class="btn btn-light-primary">Load starter chart</button>
                        </form>
                    @endif
                    <a href="{{ route('company.accounts.create', ['companySlug' => $company->slug]) }}" class="btn btn-primary">
                        {!! getIcon('plus', 'fs-2', '', 'i') !!}
                        Add Account
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
