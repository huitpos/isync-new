<x-default-layout>
    @section('title')
        {{ $itemType->name }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.itemTypes.show', $company, $itemType) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="pb-1 fs-6 mt-5">
                <div class="fw-bold">Item Type Name</div>
                <div class="text-gray-600">{{ $itemType->name }}</div>
            </div>

            <div class="pb-1 fs-6 mt-5">
                <div class="fw-bold">Description</div>
                <div class="text-gray-600">{{ $itemType->description }}</div>
            </div>

            <div class="pb-1 fs-6 mt-5">
                <div class="fw-bold">Show in cashier</div>
                <div class="text-gray-600">{{ $itemType->show_in_cashier ? 'Yes' : 'No' }}</div>
            </div>
        </div>
    </div>
</x-default-layout>
