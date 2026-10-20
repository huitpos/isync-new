<x-default-layout>

    @section('title')
        Add Account
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.accounts.create', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form method="POST" action="{{ route('company.accounts.store', ['companySlug' => $company->slug]) }}">
                @csrf
                @include('company.accounting.accounts._form')
                <button type="submit" class="btn btn-primary mt-5">Save</button>
                <a href="{{ route('company.accounts.index', ['companySlug' => $company->slug]) }}" class="btn btn-light mt-5">Cancel</a>
            </form>
        </div>
    </div>

</x-default-layout>
