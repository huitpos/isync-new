@extends('layout.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">Inventory Processing History</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('inventory-tracking.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Tracking
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Movement Type</label>
                    <select name="movement_type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ $currentType === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $key => $branch)
                            <option value="{{ $branch->id }}" {{ $currentBranch == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Product</label>
                    <select
                        name="product_id"
                        data-control="select2"
                        data-ajax-url="/ajax/get-products?company_id={{ auth()->user()->company_id }}"
                        data-placeholder="Select a product"
                        class="form-control select2-ajax"
                        data-minimum-input="3"
                        required
                    ></select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
                @if ($currentType || $currentBranch || $currentProduct)
                <div class="col-md-1 d-flex align-items-end">
                    <a href="/inventory-tracking/history/view" class="btn btn-primary w-100">
                        <i class="fas fa-redo"></i> Clear Filter
                    </a>
                </div>
                @endif
            </form>

            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Product</th>
                                <th>Movement Type</th>
                                <th>Branch</th>
                                <th>Previous Qty</th>
                                <th>New Qty</th>
                                <th>Processed By</th>
                                <th>Processed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>
                                        @if($log->product)
                                            <strong>{{ $log->product->name }}</strong><br>
                                            <small class="text-muted">#{{ $log->product_id }}</small>
                                        @else
                                            Product #{{ $log->product_id }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ $types[$log->movement_type] ?? $log->movement_type }}
                                    </td>
                                    <td>
                                        @if($log->branch)
                                            {{ $log->branch->name }}
                                        @else
                                            Branch #{{ $log->branch_id }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ number_format($log->previous_qty, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ number_format($log->new_qty, 2) }}</span>
                                        @php
                                            $diff = $log->new_qty - $log->previous_qty;
                                            $sign = $diff >= 0 ? '+' : '';
                                            $class = $diff >= 0 ? 'text-success' : 'text-danger';
                                        @endphp
                                        <br>
                                        <small class="{{ $class }}">{{ $sign }}{{ number_format($diff, 2) }}</small>
                                    </td>
                                    <td>
                                        @if($log->processedBy)
                                            {{ $log->processedBy->name }}
                                        @else
                                            System
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $log->processed_at->format('M d, Y') }}</small><br>
                                        <small class="text-muted">{{ $log->processed_at->format('H:i:s') }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} entries
                    </div>
                    <div>
                        {{ $logs->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @else
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i> No processed inventory movements found.
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .table-responsive {
        border-radius: 0.25rem;
    }
</style>
@endpush
@endsection
