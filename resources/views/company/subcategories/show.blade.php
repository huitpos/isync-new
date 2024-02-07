<x-default-layout>
    @section('title')
        {{ $subcategory->name }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.subcategories.show', $company, $subcategory) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="pb-1 fs-6 mt-5">
                <div class="fw-bold">Subcategory Name</div>
                <div class="text-gray-600">{{ $subcategory->name }}</div>
            </div>

            <div class="pb-1 fs-6 mt-5">
                <div class="fw-bold">Description</div>
                <div class="text-gray-600">{{ $subcategory->description }}</div>
            </div>

            <div class="pb-1 fs-6 mt-5">
                <div class="fw-bold">Subcategory Name</div>
                <div class="text-gray-600">{{ $subcategory->category->name }}</div>
            </div>
        </div>
    </div>
</x-default-layout>
