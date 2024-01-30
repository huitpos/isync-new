<x-default-layout>
    @section('title')
        {{ $department->name }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.clusters.show', $department->name) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <h2 class="mb-6">{{ $department->name }}</h2>

            <div class="pb-1 fs-6 mb-2">
                <div class="fw-bold">Description</div>
                <div class="text-gray-600">{{ $department->description }}</div>
            </div>

            <div class="pb-1 fs-6 mb-2">
                <div class="fw-bold">Suppliers</div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr class="fw-bold fs-6 text-gray-800">
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($department->suppliers as $supplier)
                                <tr>
                                    <td>{{ $supplier->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-default-layout>
