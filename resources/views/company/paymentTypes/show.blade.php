<x-default-layout>
    @section('title')
        {{ $paymentType->name }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.paymentTypes.show', $company, $paymentType) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="pb-4 fs-6">
                <div class="fw-bold">Status</div>
                <div class="text-gray-600">{{ $paymentType->status }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Payment Type Name</div>
                <div class="text-gray-600">{{ $paymentType->name }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">description</div>
                <div class="text-gray-600">{{ $paymentType->description }}</div>
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
                            @foreach($paymentType->fields as $field)
                                <tr>
                                    <td>{{ $field->name }}</td>
                                    <td>{{ $field->field_type }}</td>
                                    <td>
                                        @foreach($field->options as $option)
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
