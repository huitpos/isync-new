@extends('layout.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">Process Inventory Movement</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('inventory-tracking.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h2>Movement Details</h2>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $isTransaction = $type === 'transactions';
                        $movementId = $isTransaction ? $movement->transaction_id : $movement->id;
                        $status = $isTransaction ? ($movement->is_complete ? 'complete' : 'pending') : $movement->status;
                        $branchId = $movement->branch_id ?? null;
                        $createdAt = $movement->created_at ?? null;

                        $branch = \App\Models\Branch::find($branchId);
                        $branchName = $branch ? $branch->name : 'N/A';
                    @endphp

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Type</label>
                            <p class="h6">{{ $types[$type] ?? $type }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Status</label>
                            <p>
                                <span class="badge bg-{{ in_array($status, ['approved', 'complete']) ? 'success' : 'warning' }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Branch</label>
                            @if($branchId)
                                <p class="h6">{{ $branchName }}</p>
                            @else
                                <p class="h6">N/A</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Created</label>
                            <p class="h6">{{ $createdAt ? \Carbon\Carbon::parse($createdAt)->format('M d, Y H:i') : 'N/A' }}</p>
                        </div>
                    </div>

                    @if($isTransaction)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">SI</label>
                            <p class="h6">{{ $movement->receipt_number }}</p>
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
                <div class="card-body">
                    @if($isTransaction)
                        @php
                            $orders = \Illuminate\Support\Facades\DB::connection('transactional_db')
                                ->table('orders')
                                ->where('transaction_id', $movement->transaction_id)
                                ->where('branch_id', $movement->branch_id)
                                ->where('pos_machine_id', $movement->pos_machine_id)
                                ->where('is_completed', true)
                                ->where('is_void', false)
                                ->where('is_back_out', false)
                                ->get();
                        @endphp
                        @if($orders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Product ID</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($orders as $order)
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
                            <div class="alert alert-warning" role="alert">
                                No completed orders in this transaction.
                            </div>
                        @endif
                    @else
                        @if(isset($movement->items) && is_countable($movement->items) && count($movement->items) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
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
                            <div class="alert alert-warning" role="alert">
                                No items in this movement.
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <div class="card-title">
                        <h2>Process Movement</h2>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $isProcessed = $isTransaction ? $movement->inventory_processed : $movement->inventory_processed;
                        $canProcess = $isTransaction ? $movement->is_complete : ($movement->status === 'approved');
                    @endphp

                    @if($isProcessed)
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle"></i> This movement has already been processed.
                        </div>
                    @elseif(!$canProcess)
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> This movement must be approved/completed before processing.
                        </div>
                    @else
                        <p class="text-muted mb-4">
                            Click the button below to process this inventory movement. This will update the inventory counts and create an audit log.
                        </p>

                        <form method="POST" action="{{ route('inventory-tracking.process', [$type, $movementId]) }}" onsubmit="return confirm('Are you sure you want to process this movement?');">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 btn-lg">
                                <i class="fas fa-check"></i> Process Movement
                            </button>
                        </form>

                        <hr>

                        <h6 class="text-muted mb-3">Processing Summary:</h6>
                        <ul class="small text-muted">
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
</div>

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header {
        border-bottom: 1px solid #e3e6f0;
    }
</style>
@endpush
@endsection
