<x-default-layout>
    @section('title')
        {{ $cluster->name }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.clusters.show', $cluster->name) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="pb-1 fs-6">
                <div class="fw-bold">Company Name</div>
                <div class="text-gray-600">{{ $cluster->company->company_name }}</div>
            </div>

            <div class="pb-1 fs-6 mt-5">
                <div class="fw-bold">Cluster Name</div>
                <div class="text-gray-600">{{ $cluster->name }}</div>
            </div>

            <div class="pb-1 fs-6 mt-5">
                <div class="fw-bold">Description</div>
                <div class="text-gray-600">{{ $cluster->description }}</div>
            </div>

            <div class="pb-1 fs-6 mt-5">
                <div class="fw-bold">Status</div>
                <div class="text-gray-600 text-capitalize">{{ $cluster->status }}</div>
            </div>
        </div>
    </div>
</x-default-layout>
