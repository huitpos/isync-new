<x-default-layout>

    @section('title')
        Create Purchase Return
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.purchaseReturns.create', $company, $branch) }}
    @endsection

    <div class="card mb-5">
        <div class="card-body">
            <form method="GET">
                <label class="form-label">Purchase order</label>
                <select name="purchase_order_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Select purchase order</option>
                    @foreach ($orders as $order)
                        <option value="{{ $order->id }}" {{ $purchaseOrder && $purchaseOrder->id === $order->id ? 'selected' : '' }}>
                            {{ $order->po_number }} — {{ $order->supplier->name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Only approved purchase orders with received quantity are listed. The return quantity cannot exceed what was received and not yet returned.</div>
            </form>
        </div>
    </div>

    @if ($purchaseOrder)
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('branch.purchase-returns.store', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]) }}">
                    @csrf
                    <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">
                    <div class="row mb-5">
                        <div class="col-md-4">
                            <label class="form-label">Return date</label>
                            <input type="date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" value="{{ old('remarks') }}" class="form-control">
                        </div>
                    </div>

                    @error('items')
                        <div class="text-danger mb-4">{{ $message }}</div>
                    @enderror
                    @error('purchase_order_id')
                        <div class="text-danger mb-4">{{ $message }}</div>
                    @enderror

                    @forelse ($lines as $line)
                        <div class="border border-gray-300 rounded p-4 mb-4">
                            <div class="fw-semibold mb-3">{{ $line['item']->product->name }} <span class="text-gray-600 fw-normal">{{ $line['item']->uom->name }}</span></div>
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Received</label>
                                    <input type="text" value="{{ $line['received'] }}" class="form-control" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Returnable</label>
                                    <input type="text" value="{{ $line['returnable'] }}" class="form-control" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Return qty</label>
                                    <input type="number" min="0" max="{{ $line['returnable'] }}" step="0.0001" name="items[{{ $line['item']->id }}][qty]" value="{{ old('items.' . $line['item']->id . '.qty') }}" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Reason</label>
                                    <input type="text" name="items[{{ $line['item']->id }}][reason]" value="{{ old('items.' . $line['item']->id . '.reason') }}" class="form-control">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-gray-600">Nothing on this purchase order can be returned.</div>
                    @endforelse

                    @if ($lines)
                        <button type="submit" class="btn btn-primary">Save return</button>
                    @endif
                </form>
            </div>
        </div>
    @endif

</x-default-layout>
