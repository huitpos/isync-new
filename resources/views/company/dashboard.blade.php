<x-default-layout>

    @section('title')
        Dashboard
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.dashboard', $company) }}
    @endsection

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-6">
            @include('partials/widgets/cards/_widget-7')
            @include('partials/widgets/charts/_widget-8')
        </div>
    </div>
</x-default-layout>
