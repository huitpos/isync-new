<x-default-layout>

    @section('title')
        {{$branch->name}} - {{ $product->name }}
    @endsection

    @if($errors->all())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card mb-5">
        <div class="card-body">
            <div class="fw-bold">POS cost {{ number_format($branchCost ?? 0, 2) }}</div>
            <div class="text-gray-600 mb-4">This is the oldest remaining FIFO layer. The catalog cost on the product is only the starting markup cost.</div>
            <table class="table table-row-dashed">
                <thead>
                    <tr>
                        <th>Received</th>
                        <th>Source</th>
                        <th class="text-end">Remaining</th>
                        <th class="text-end">Unit cost</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($layers as $layer)
                        <tr>
                            <td>{{ $layer->received_at }}</td>
                            <td>{{ str_replace('_', ' ', $layer->source_type) }}</td>
                            <td class="text-end">{{ number_format($layer->qty_remaining, 2) }}</td>
                            <td class="text-end">{{ number_format($layer->unit_cost, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No remaining layers.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" data-dt-card>
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                
                <!--end::Search-->
                <!--begin::Export buttons-->
                <div id="kt_ecommerce_report_returns_export" class="d-none"></div>
                <!--end::Export buttons-->
            </div>
            <!--end::Card title-->
        </div>

        <div class="card-body py-4">
            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts() }}
        
    @endpush

</x-default-layout>