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
                <div class="text-gray-600">
                    @foreach($discountType->departments as $department)
                        <span class="badge badge-light">{{ $department->name }}</span>
                    @endforeach
                </div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Type</div>
                <div class="text-gray-600">{{ $discountType->type }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Discount</div>
                <div class="text-gray-600">{{ $discountType->discount }}</div>
            </div>

            <div class="pb-1 fs-6 mb-2">
                <div class="fw-bold">Fields</div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr class="fw-bold fs-6 text-gray-800">
                                <th>Field Name</th>
                                <th>Field Type</th>
                                <th>Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($discountType->fields as $field)
                                <tr>
                                    <td>{{ $field->name }}</td>
                                    <td>{{ $field->field_type }}</td>
                                    <td>
                                        @foreach($field->options ?? [] as $option)
                                            <span class="badge badge-light">{{ $option }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-default-layout>
