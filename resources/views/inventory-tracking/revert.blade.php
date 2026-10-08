<x-default-layout>

    @section('title')
        Revert Inventory Movement
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('inventory-tracking.revert', $company) }}
    @endsection

    <div class="d-flex justify-content-end mb-5">
        <a href="{{ route('inventory-tracking.history', array_filter([
            'movement_type' => $return_movement_type,
            'branch_id' => $return_branch_id,
            'product_id' => $return_product_id,
        ])) }}" class="btn btn-light">
            {!! getIcon('arrow-left', 'fs-2', '', 'i') !!}
            Back to History
        </a>
    </div>

    <div class="row g-5">
        <div class="col-lg-8">
            <div class="card mb-5">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h2>Movement Details</h2>
                    </div>
                </div>
                <div class="card-body py-4">
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <input value="{{ $types[$movement_type] ?? $movement_type }}" type="text" readonly class="form-control"/>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference</label>
                            <input value="{{ $reference ?? 'Object #' . $object_id }}" type="text" readonly class="form-control"/>
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-6">
                            <label class="form-label">Branch</label>
                            <input value="{{ $branch->name ?? 'N/A' }}" type="text" readonly class="form-control"/>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Processed At</label>
                            <input value="{{ $processed_at ? \Carbon\Carbon::parse($processed_at)->format('M d, Y H:i') : 'N/A' }}" type="text" readonly class="form-control"/>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Processed By</label>
                            <input value="{{ $processed_by }}" type="text" readonly class="form-control"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h2>Items to Revert ({{ $item_count }})</h2>
                    </div>
                </div>
                <div class="card-body py-4">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Product</th>
                                    <th>Previous Qty</th>
                                    <th>Current Qty</th>
                                    <th>Revert Effect</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    @php
                                        $effectLabel = match($item['revert_operation']) {
                                            'add' => 'Add back ' . number_format($item['revert_qty'], 2),
                                            'subtract' => 'Remove ' . number_format($item['revert_qty'], 2),
                                            'set' => 'Set to ' . number_format($item['revert_qty'], 2),
                                            default => number_format($item['revert_qty'], 2),
                                        };
                                        $effectClass = match($item['revert_operation']) {
                                            'add' => 'text-success',
                                            'subtract' => 'text-danger',
                                            default => 'text-primary',
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ $item['product_name'] }}</td>
                                        <td>{{ number_format($item['previous_qty'], 2) }}</td>
                                        <td>{{ number_format($item['new_qty'], 2) }}</td>
                                        <td><span class="fw-bold {{ $effectClass }}">{{ $effectLabel }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h2>Confirm Revert</h2>
                    </div>
                </div>
                <div class="card-body py-4">
                    <div class="alert alert-dismissible bg-light-warning d-flex flex-column flex-sm-row p-5 mb-5">
                        <i class="fa-solid fa-triangle-exclamation fs-2hx text-warning me-4 mb-5 mb-sm-0"></i>
                        <div class="d-flex flex-column pe-0 pe-sm-10">
                            <span>This will revert <strong>{{ $item_count }}</strong> item(s) and restore stock levels. This action is permanent — the movement cannot be processed again.</span>
                        </div>
                    </div>

                    <form method="POST"
                          action="{{ route('inventory-tracking.revert') }}"
                          onsubmit="return confirm('Are you sure you want to revert this movement? Stock will be restored for {{ $item_count }} item(s). This cannot be undone.');">
                        @csrf
                        <input type="hidden" name="movement_type" value="{{ $movement_type }}">
                        <input type="hidden" name="object_id" value="{{ $object_id }}">
                        <input type="hidden" name="branch_id" value="{{ $branch_id }}">
                        <input type="hidden" name="return_movement_type" value="{{ $return_movement_type }}">
                        <input type="hidden" name="return_branch_id" value="{{ $return_branch_id }}">
                        <input type="hidden" name="return_product_id" value="{{ $return_product_id }}">

                        <button type="submit" class="btn btn-danger w-100">
                            {!! getIcon('arrows-loop', 'fs-2', '', 'i') !!}
                            Revert {{ $item_count }} Item(s)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-default-layout>
