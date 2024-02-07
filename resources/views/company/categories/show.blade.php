<x-default-layout>
    @section('title')
        {{ $category->name }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.categories.show', $company, $category) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="pb-4 fs-6">
                <div class="fw-bold">Category Name</div>
                <div class="text-gray-600">{{ $category->name }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Description</div>
                <div class="text-gray-600">{{ $category->description }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Department</div>
                <div class="text-gray-600">{{ $category->department->name }}</div>
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
                            @foreach($category->suppliers as $supplier)
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
