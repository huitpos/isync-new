<x-default-layout>
    @section('title')
        {{ $supplier->name }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.suppliers.show', $company, $supplier) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="pb-4 fs-6">
                <div class="fw-bold">Name</div>
                <div class="text-gray-600">{{ $supplier->name }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Contact Person</div>
                <div class="text-gray-600">{{ $supplier->contact_person }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Contact Number</div>
                <div class="text-gray-600">{{ $supplier->contact_number }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Email Address</div>
                <div class="text-gray-600">{{ $supplier->email }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Address</div>
                <div class="text-gray-600">{{ $supplier->address }}</div>
            </div>
        </div>
    </div>
</x-default-layout>
