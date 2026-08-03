<x-default-layout>

    @section('title')
        Process Inventory Movement
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('inventory-tracking.show', $company, $types[$type] ?? $type) }}
    @endsection

    <div class="d-flex justify-content-end mb-5">
        <a href="{{ route('inventory-tracking.index', array_filter([
            'type' => request('type'),
            'branch_id' => request('branch_id'),
            'page' => request('page'),
        ])) }}" class="btn btn-light">
            {!! getIcon('arrow-left', 'fs-2', '', 'i') !!}
            Back to List
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
                    @php
                        $isTransaction = $type === 'transactions';
                        $movementId = $movement->id;
                        $status = $isTransaction ? ($movement->is_complete ? 'complete' : 'pending') : $movement->status;
                        $branchId = $movement->branch_id ?? null;
                        $createdAt = $movement->created_at ?? null;

                        $branch = \App\Models\Branch::find($branchId);
                        $branchName = $branch ? $branch->name : 'N/A';
                    @endphp

                    <div class="row mb-5">
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <input value="{{ $types[$type] ?? $type }}" type="text" readonly class="form-control"/>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <input value="{{ ucfirst($status) }}" type="text" readonly class="form-control"/>
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-6">
                            <label class="form-label">Branch</label>
                            <input value="{{ $branchName }}" type="text" readonly class="form-control"/>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Created</label>
                            <input value="{{ $createdAt ? \Carbon\Carbon::parse($createdAt)->format('M d, Y H:i') : 'N/A' }}" type="text" readonly class="form-control"/>
                        </div>
                    </div>

                    @if($isTransaction)
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <label class="form-label">SI</label>
                            <input value="{{ $movement->receipt_number }}" type="text" readonly class="form-control"/>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h2>Items to Process</h2>
                    </div>
                </div>
                <div class="card-body py-4">
                    @if($isTransaction)
                        @php
                            $totalItemOrders = 0;
                            $orders = \Illuminate\Support\Facades\DB::connection('transactional_db')
                                ->table('orders')
                                ->where('transaction_id', $movement->transaction_id)
                                ->where('branch_id', $movement->branch_id)
                                ->where('pos_machine_id', $movement->pos_machine_id)
                                ->where('is_void', false)
                                ->where('is_back_out', false)
                                ->get();
                        @endphp
                        @if($orders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead>
                                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                            <th>Product Name</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($orders as $order)
                                            @php
                                                if ($order->bundle_order_id || $order->is_completed) {
                                                    if ($order->qty > 0) {
                                                        $totalItemOrders += $order->qty;
                                                    }
                                                }

                                                if ($order->bundle_order_id) {
                                                    continue;
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $order->name }}</td>
                                                <td>{{ $order->qty }}</td>
                                                <td>₱ {{ number_format($order->amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-dismissible bg-light-warning d-flex flex-column flex-sm-row p-5">
                                <i class="fa-solid fa-triangle-exclamation fs-2hx text-warning me-4 mb-5 mb-sm-0"></i>
                                <div class="d-flex flex-column pe-0 pe-sm-10">
                                    <span>No completed orders in this transaction.</span>
                                </div>
                            </div>
                        @endif
                    @else
                        @if(isset($movement->items) && is_countable($movement->items) && count($movement->items) > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead>
                                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>UOM</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($movement->items as $item)
                                            <tr>
                                                <td>
                                                    @if(isset($item->product))
                                                        {{ $item->product->name }}
                                                    @else
                                                        Product #{{ $item->product_id }}
                                                    @endif
                                                </td>
                                                <td>{{ $item->qty ?? $item->quantity ?? 'N/A' }}</td>
                                                <td>{{ $item->uom_id ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-dismissible bg-light-warning d-flex flex-column flex-sm-row p-5">
                                <i class="fa-solid fa-triangle-exclamation fs-2hx text-warning me-4 mb-5 mb-sm-0"></i>
                                <div class="d-flex flex-column pe-0 pe-sm-10">
                                    <span>No items in this movement.</span>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h2>Process Movement</h2>
                    </div>
                </div>
                <div class="card-body py-4">
                    @php
                        $isProcessed = $isTransaction ? $movement->inventory_processed : $movement->inventory_processed;
                        $canProcess = $isTransaction ? $movement->is_complete : ($movement->status === 'approved');

                        $itemsMissing = false;
                        if ($isTransaction) {
                            if ($totalItemOrders != $movement->total_quantity) {
                                $itemsMissing = true;
                            }
                        }
                    @endphp

                    @if($isProcessed)
                        <div class="alert alert-dismissible bg-light-success d-flex flex-column flex-sm-row p-5 mb-0">
                            <i class="fa-solid fa-circle-check fs-2hx text-success me-4 mb-5 mb-sm-0"></i>
                            <div class="d-flex flex-column pe-0 pe-sm-10">
                                <span>This movement has already been processed.</span>
                            </div>
                        </div>
                    @elseif(!$canProcess)
                        <div class="alert alert-dismissible bg-light-warning d-flex flex-column flex-sm-row p-5 mb-0">
                            <i class="fa-solid fa-triangle-exclamation fs-2hx text-warning me-4 mb-5 mb-sm-0"></i>
                            <div class="d-flex flex-column pe-0 pe-sm-10">
                                <span>This movement must be approved/completed before processing.</span>
                            </div>
                        </div>
                    @elseif($itemsMissing)
                        <div class="alert alert-dismissible bg-light-warning d-flex flex-column flex-sm-row p-5 mb-0">
                            <i class="fa-solid fa-triangle-exclamation fs-2hx text-warning me-4 mb-5 mb-sm-0"></i>
                            <div class="d-flex flex-column pe-0 pe-sm-10">
                                <span>Some items are missing from the transaction. Please wait for the orders to sync before processing.</span>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-5">
                            Click the button below to process this inventory movement. This will update the inventory counts and create an audit log.
                        </p>

                        <form method="POST" action="{{ route('inventory-tracking.process', [$type, $movementId]) }}" onsubmit="return confirm('Are you sure you want to process this movement?');">
                            @csrf
                            <input type="hidden" name="return_type" value="{{ request('type', $type) }}">
                            <input type="hidden" name="return_branch_id" value="{{ request('branch_id') }}">
                            <input type="hidden" name="return_page" value="{{ request('page') }}">
                            <button type="submit" class="btn btn-success w-100">
                                {!! getIcon('check', 'fs-2', '', 'i') !!}
                                Process Movement
                            </button>
                        </form>

                        <div class="separator separator-dashed my-5"></div>

                        <h6 class="text-muted mb-3">Processing Summary:</h6>
                        <ul class="small text-muted mb-0">
                            <li>
                                @if($isTransaction)
                                    @php $itemCount = count($orders); @endphp
                                @else
                                    @php $itemCount = isset($movement->items) ? count($movement->items) : 0; @endphp
                                @endif
                                {{ $itemCount }} item(s) will be processed
                            </li>
                            @if($type === 'purchase_deliveries')
                                <li>Inventory will be <strong>increased</strong></li>
                            @elseif($type === 'product_disposals')
                                <li>Inventory will be <strong>decreased</strong></li>
                            @elseif($type === 'product_physical_counts')
                                <li>Inventory will be <strong>set to counted quantities</strong></li>
                            @elseif($type === 'transactions')
                                <li>Inventory will be <strong>decreased</strong> by sales qty</li>
                            @else
                                <li>Inventory will be updated accordingly</li>
                            @endif
                            <li>An audit log will be created</li>
                            <li>This action cannot be undone</li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-default-layout>
