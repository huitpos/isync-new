<x-default-layout>

    @section('title')
        Debit Memo {{ $memo->memo_number }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.debitMemos.show', $company, $memo) }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="row mb-5">
                <div class="col-md-3">
                    <label class="form-label">Memo number</label>
                    <input value="{{ $memo->memo_number }}" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Supplier invoice</label>
                    <input value="{{ $memo->supplierInvoice->invoice_number }}" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Purchase order</label>
                    <input value="{{ $memo->supplierInvoice->purchaseOrder->po_number }}" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <input value="{{ ucfirst($memo->status) }}" class="form-control" readonly>
                </div>
            </div>
            <div class="row mb-5">
                <div class="col-md-4">
                    <label class="form-label">Supplier</label>
                    <input value="{{ $memo->supplier->name }}" class="form-control" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Memo date</label>
                    <input value="{{ $memo->memo_date->format('Y-m-d') }}" class="form-control" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Remarks</label>
                    <input value="{{ $memo->remarks }}" class="form-control" readonly>
                </div>
            </div>

            @foreach ($memo->items as $item)
                <div class="border border-gray-300 rounded p-4 mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Product</label>
                            <input value="{{ $item->product->name }}" class="form-control" readonly>
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
                            <label class="form-label">Amount</label>
                            <input value="{{ $item->line_amount }}" class="form-control text-end" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Purchase return</label>
                            <input value="{{ $item->purchaseReturnItem->purchaseReturn->return_number ?? 'Allowance' }}" class="form-control" readonly>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="fs-4 fw-bold mb-5">Total: {{ $memo->total_amount }}</div>

            @error('status')
                <div class="text-danger mb-4">{{ $message }}</div>
            @enderror

            @if ($memo->status === 'issued')
                <form method="POST" action="{{ route('company.debit-memos.void', ['companySlug' => $company->slug, 'id' => $memo->id]) }}">
                    @csrf
                    <button type="submit" class="btn btn-light-danger">Void</button>
                </form>
            @endif
        </div>
    </div>

</x-default-layout>
