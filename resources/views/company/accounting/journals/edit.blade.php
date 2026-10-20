<x-default-layout>

    @section('title')
        Edit Journal
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.journals.edit', $company, $entry) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form method="POST" action="{{ route('company.journals.update', ['companySlug' => $company->slug, 'id' => $entry->id]) }}">
                @csrf
                @method('PUT')
                @include('company.accounting.journals._form')
                <button type="submit" class="btn btn-primary mt-5">Save draft</button>
            </form>
        </div>
    </div>

</x-default-layout>
