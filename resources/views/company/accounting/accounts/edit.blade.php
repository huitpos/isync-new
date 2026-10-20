<x-default-layout>

    @section('title')
        Edit Account
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.accounts.edit', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form method="POST" action="{{ route('company.accounts.update', ['companySlug' => $company->slug, 'id' => $account->id]) }}">
                @csrf
                @method('PUT')
                @include('company.accounting.accounts._form')
                <button type="submit" class="btn btn-primary mt-5">Save</button>
                <a href="{{ route('company.accounts.index', ['companySlug' => $company->slug]) }}" class="btn btn-light mt-5">Cancel</a>
            </form>
        </div>
    </div>

</x-default-layout>
