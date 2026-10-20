<x-default-layout>

    @section('title')
        New Journal
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.journals.create', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form method="POST" action="{{ route('company.journals.store', ['companySlug' => $company->slug]) }}">
                @csrf
                @include('company.accounting.journals._form')
                <button type="submit" class="btn btn-primary mt-5">Save draft</button>
            </form>
        </div>
    </div>

</x-default-layout>
