<x-default-layout>
    @section('title')
        {{ $uom->name }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.uom.show', $company, $uom) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="pb-4 fs-6">
                <div class="fw-bold">Name</div>
                <div class="text-gray-600">{{ $uom->name }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Description</div>
                <div class="text-gray-600">{{ $uom->description }}</div>
            </div>
        </div>
    </div>
</x-default-layout>
