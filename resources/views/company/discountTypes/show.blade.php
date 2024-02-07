<x-default-layout>
    @section('title')
        {{ $discountType->name }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.discountTypes.show', $company, $discountType) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="pb-4 fs-6">
                <div class="fw-bold">Name</div>
                <div class="text-gray-600">{{ $discountType->name }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Description</div>
                <div class="text-gray-600">{{ $discountType->description }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Department</div>
                <div class="text-gray-600">{{ $discountType->department->name }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Type</div>
                <div class="text-gray-600">{{ $discountType->type }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Type</div>
                <div class="text-gray-600">{{ $discountType->discount }}</div>
            </div>
        </div>
    </div>
</x-default-layout>
