<x-default-layout>
    @section('title')
        {{ $chargeAccount->name }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="pb-1 fs-6 mb-2">
                <div class="fw-bold">Name</div>
                <div class="text-gray-600">{{ $chargeAccount->name }}</div>
            </div>

            <div class="pb-1 fs-6 mb-2">
                <div class="fw-bold">Address</div>
                <div class="text-gray-600">{{ $chargeAccount->address }}</div>
            </div>

            <div class="pb-1 fs-6 mb-2">
                <div class="fw-bold">Contact Number</div>
                <div class="text-gray-600">{{ $chargeAccount->contact_number }}</div>
            </div>

            <div class="pb-1 fs-6 mb-2">
                <div class="fw-bold">Email</div>
                <div class="text-gray-600">{{ $chargeAccount->email }}</div>
            </div>

            <div class="pb-1 fs-6 mb-2">
                <div class="fw-bold">Credit Limit</div>
                <div class="text-gray-600">{{ $chargeAccount->credit_limit }}</div>
            </div>
        </div>
    </div>
</x-default-layout>
