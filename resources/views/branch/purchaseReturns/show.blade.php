<x-default-layout>

    @section('title')
        Purchase Return {{ $purchaseReturn->return_number }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.purchaseReturns.show', $company, $branch, $purchaseReturn) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="row mb-5">
                <div class="col-md-4">
                    <label class="form-label">Return number</label>
                    <input value="{{ $purchaseReturn->return_number }}" class="form-control" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Purchase order</label>
                    <input value="{{ $purchaseReturn->purchaseOrder->po_number }}" class="form-control" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <input value="{{ ucfirst($purchaseReturn->status) }}" class="form-control" readonly>
                </div>
            </div>
            <div class="row mb-5">
                <div class="col-md-4">
                    <label class="form-label">Supplier</label>
                    <input value="{{ $purchaseReturn->supplier->name }}" class="form-control" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Return date</label>
                    <input value="{{ $purchaseReturn->return_date->format('Y-m-d') }}" class="form-control" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Remarks</label>
                    <input value="{{ $purchaseReturn->remarks }}" class="form-control" readonly>
                </div>
            </div>

            @foreach ($purchaseReturn->items as $item)
                <div class="border border-gray-300 rounded p-4 mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Product</label>
                            <input value="{{ $item->product->name }}" class="form-control" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">UOM</label>
                            <input value="{{ $item->uom->name }}" class="form-control" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quantity</label>
                            <input value="{{ $item->qty }}" class="form-control" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Unit price</label>
                            <input value="{{ $item->unit_price }}" class="form-control text-end" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Reason</label>
                            <input value="{{ $item->reason }}" class="form-control" readonly>
                        </div>
                    </div>
                </div>
            @endforeach

            @error('status')
                <div class="text-danger mb-4">{{ $message }}</div>
            @enderror

            @if ($purchaseReturn->status === 'pending')
                <form method="POST" action="{{ route('branch.purchase-returns.approve', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug, 'id' => $purchaseReturn->id]) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">Approve</button>
                </form>
                <form method="POST" action="{{ route('branch.purchase-returns.reject', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug, 'id' => $purchaseReturn->id]) }}" class="d-inline ms-3">
                    @csrf
                    <button type="submit" class="btn btn-danger">Reject</button>
                </form>
            @endif

            @if ($purchaseReturn->status === 'approved')
                <form method="POST" action="{{ route('branch.purchase-returns.void', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug, 'id' => $purchaseReturn->id]) }}">
                    @csrf
                    <button type="submit" class="btn btn-light-danger">Void</button>
                </form>
            @endif
        </div>
    </div>

</x-default-layout>
