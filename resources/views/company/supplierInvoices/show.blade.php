<x-default-layout>

    @section('title')
        Supplier Invoice {{ $invoice->invoice_number }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.supplierInvoices.show', $company, $invoice) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row mb-5">
                <div class="col-md-4">
                    <label class="form-label">Invoice Number</label>
                    <input value="{{ $invoice->invoice_number }}" type="text" readonly class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Invoice Date</label>
                    <input value="{{ $invoice->invoice_date->format('Y-m-d') }}" type="text" readonly class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <input value="{{ ucfirst($invoice->status) }}" type="text" readonly class="form-control">
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-md-4">
                    <label class="form-label">Supplier</label>
                    <input value="{{ $invoice->supplier->name }}" type="text" readonly class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Purchase Order</label>
                    <input value="{{ $invoice->purchaseOrder->po_number }}" type="text" readonly class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Branch</label>
                    <input value="{{ $invoice->branch->name }}" type="text" readonly class="form-control">
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-md-4">
                    <label class="form-label">Created By</label>
                    <input value="{{ $invoice->createdBy->name }}" type="text" readonly class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Accepted By</label>
                    <input value="{{ $invoice->acceptedBy?->name }}" type="text" readonly class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Voided By</label>
                    <input value="{{ $invoice->voidedBy?->name }}" type="text" readonly class="form-control">
                </div>
            </div>

            <form method="POST" action="{{ route('company.supplier-invoices.accept', ['companySlug' => $company->slug, 'id' => $invoice->id]) }}">
                @csrf
                <div class="mt-7">
                    <h2>Items</h2>
                    @foreach ($invoice->items as $item)
                        @php
                            $allocated = $item->allocations->sum('qty');
                            $varianceLabel = match ($item->variance_type) {
                                'qty' => 'Quantity',
                                'price' => 'Price',
                                'both' => 'Quantity and price',
                                default => 'None',
                            };
                        @endphp
                        <hr>
                        <div class="form-group row mb-5 bg-light-dark p-2">
                            <div class="col-md-3">
                                <label class="form-label">Product</label>
                                <input value="{{ $item->product->name }}" type="text" readonly class="form-control">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">UOM</label>
                                <input value="{{ $item->uom->name }}" type="text" readonly class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Billed Qty</label>
                                <input value="{{ $item->qty }}" type="text" readonly class="form-control text-end">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">PO Price</label>
                                <input value="{{ number_format($item->po_unit_price, 2) }}" type="text" readonly class="form-control text-end">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Invoice Price</label>
                                <input value="{{ number_format($item->unit_price, 2) }}" type="text" readonly class="form-control text-end">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Amount</label>
                                <input value="{{ number_format($item->line_amount, 2) }}" type="text" readonly class="form-control text-end">
                            </div>
                            <div class="col-md-3 mt-3">
                                <label class="form-label">Variance</label>
                                <input value="{{ $varianceLabel }}" type="text" readonly class="form-control">
                            </div>
                            <div class="col-md-3 mt-3">
                                <label class="form-label">Allocated to Deliveries</label>
                                <input value="{{ $allocated }}" type="text" readonly class="form-control">
                            </div>
                            <div class="col-md-6 mt-3">
                                <label class="form-label">Deliveries</label>
                                <input value="{{ $item->allocations->map(fn ($allocation) => $allocation->purchaseDeliveryItem->purchaseDelivery->pd_number . ' (' . $allocation->qty . ')')->join(', ') }}" type="text" readonly class="form-control">
                            </div>
                            @if ($item->variance_type && $invoice->status === 'variance')
                                <div class="col-md-12 mt-3">
                                    <label class="form-label">Variance Reason</label>
                                    <textarea name="variance_reasons[{{ $item->id }}]" class="form-control @error('variance_reasons.' . $item->id) is-invalid @enderror">{{ old('variance_reasons.' . $item->id) }}</textarea>
                                </div>
                            @elseif ($item->variance_reason)
                                <div class="col-md-12 mt-3">
                                    <label class="form-label">Variance Reason</label>
                                    <textarea readonly class="form-control">{{ $item->variance_reason }}</textarea>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between align-items-center mt-8">
                    <div>
                        @if (in_array($invoice->status, ['matched', 'variance']))
                            <button type="submit" class="btn btn-success">Accept for Payment</button>
                        @endif
                    </div>
                    <h2 class="mb-0">TOTAL: {{ number_format($invoice->total_amount, 2) }}</h2>
                </div>
            </form>

            @if (in_array($invoice->status, ['matched', 'variance', 'accepted']))
                <form class="mt-4" method="POST" action="{{ route('company.supplier-invoices.void', ['companySlug' => $company->slug, 'id' => $invoice->id]) }}" onsubmit="return confirm('Void this supplier invoice?')">
                    @csrf
                    <button type="submit" class="btn btn-danger">Void</button>
                </form>
            @endif
        </div>
    </div>
</x-default-layout>
