<x-default-layout>

    @section('title')
        Quotations {{ $pr->pr_number }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.purchaseRequests.quotations', $company, $pr) }}
    @endsection

    <div class="card mb-5">
        <div class="card-body py-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="fw-bold fs-4">{{ $pr->pr_number }}</div>
                    <div class="text-gray-600">{{ $pr->branch->name }} · Needed {{ $pr->date_needed }} · {{ ucfirst($pr->status) }}</div>
                </div>
                <a href="{{ route('company.purchase-requests.show', ['companySlug' => $company->slug, 'purchase_request' => $pr->id]) }}" class="btn btn-light">Back to request</a>
            </div>

            @if ($purchaseOrders->isNotEmpty())
                <div class="mt-4">
                    Purchase orders:
                    @foreach ($purchaseOrders as $purchaseOrder)
                        <a href="{{ route('company.purchase-orders.show', ['companySlug' => $company->slug, 'purchase_order' => $purchaseOrder->id]) }}">{{ $purchaseOrder->po_number }}</a>@if (!$loop->last), @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @if ($pr->status === 'pending')
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="card-title">Add supplier</h3>
            </div>
            <div class="card-body">
                @if ($suppliers->isEmpty())
                    <div class="text-gray-600">Every active supplier already has a quotation, or there is no active supplier to add.</div>
                @else
                    <form method="POST" action="{{ route('company.purchase-requests.quotations.store', ['companySlug' => $company->slug, 'id' => $pr->id]) }}">
                        @csrf
                        <div class="row g-4 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label">Supplier</label>
                                <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                                    <option value="">Select supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ (string) old('supplier_id') === (string) $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Lead time (days)</label>
                                <input type="number" min="0" step="1" name="lead_time_days" value="{{ old('lead_time_days') }}" class="form-control @error('lead_time_days') is-invalid @enderror">
                                @error('lead_time_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Remarks</label>
                                <input type="text" name="remarks" value="{{ old('remarks') }}" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Add</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif

    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title">Comparison</h3>
        </div>
        <div class="card-body">
            @if ($pr->quotations->isEmpty())
                <div class="text-gray-600">Add a supplier to start encoding prices.</div>
            @else
                <form method="POST" action="{{ route('company.purchase-requests.quotations.update', ['companySlug' => $company->slug, 'id' => $pr->id]) }}">
                    @csrf
                    @method('PUT')
                    <div class="table-responsive">
                        <table class="table table-row-bordered align-middle gs-3">
                            <thead>
                                <tr class="fw-bold text-gray-700">
                                    <th>Item</th>
                                    <th>Requested</th>
                                    @foreach ($pr->quotations as $quotation)
                                        <th>
                                            {{ $quotation->supplier->name }}
                                            @if ($quotation->items->contains('is_awarded', true))
                                                <span class="badge badge-light-success">Awarded</span>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    @foreach ($pr->quotations as $quotation)
                                        <th class="fw-normal">
                                            <label class="form-label mb-1">Lead time (days)</label>
                                            <input
                                                type="number"
                                                min="0"
                                                step="1"
                                                name="quotations[{{ $quotation->id }}][lead_time_days]"
                                                value="{{ $quotation->lead_time_days }}"
                                                class="form-control form-control-sm mb-2"
                                                {{ $pr->status !== 'pending' ? 'readonly' : '' }}
                                            >
                                            <label class="form-label mb-1">Remarks</label>
                                            <input
                                                type="text"
                                                name="quotations[{{ $quotation->id }}][remarks]"
                                                value="{{ $quotation->remarks }}"
                                                class="form-control form-control-sm"
                                                {{ $pr->status !== 'pending' ? 'readonly' : '' }}
                                            >
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pr->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item->product->name }}</div>
                                            <div class="text-gray-600 fs-7">{{ $item->uom->name }}</div>
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        @foreach ($pr->quotations as $quotation)
                                            @php
                                                $quoteItem = $quotation->items->firstWhere('purchase_request_item_id', $item->id);
                                                $isLowest = $quoteItem
                                                    && $quoteItem->unit_price
                                                    && $lowestPrices[$item->id] !== null
                                                    && (float) $quoteItem->unit_price === (float) $lowestPrices[$item->id];
                                            @endphp
                                            <td class="{{ $isLowest ? 'bg-light-success' : '' }}">
                                                @if ($quoteItem)
                                                    <label class="form-label mb-1">Unit price</label>
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        step="0.0001"
                                                        name="quotations[{{ $quotation->id }}][items][{{ $quoteItem->id }}][unit_price]"
                                                        value="{{ $quoteItem->unit_price }}"
                                                        class="form-control form-control-sm mb-2"
                                                        {{ $pr->status !== 'pending' ? 'readonly' : '' }}
                                                    >
                                                    <label class="form-label mb-1">Available qty</label>
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        step="0.0001"
                                                        name="quotations[{{ $quotation->id }}][items][{{ $quoteItem->id }}][available_qty]"
                                                        value="{{ $quoteItem->available_qty }}"
                                                        placeholder="{{ $item->quantity }}"
                                                        class="form-control form-control-sm"
                                                        {{ $pr->status !== 'pending' ? 'readonly' : '' }}
                                                    >
                                                    @if ($quoteItem->is_awarded)
                                                        <div class="text-success fs-7 mt-1">Awarded {{ $quoteItem->awarded_qty }}</div>
                                                    @endif
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-gray-600 fs-7 mb-4">The lowest unit price on each item is highlighted. Leave available quantity blank to use the requested quantity.</div>
                    @if ($pr->status === 'pending')
                        <button type="submit" class="btn btn-primary">Save quotations</button>
                    @endif
                </form>
            @endif

            @error('quotations')
                <div class="text-danger mt-3">{{ $message }}</div>
            @enderror
            @error('award_mode')
                <div class="text-danger mt-3">{{ $message }}</div>
            @enderror
        </div>
    </div>

    @if ($pr->status === 'pending' && $pr->quotations->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Award</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('company.purchase-requests.quotations.award', ['companySlug' => $company->slug, 'id' => $pr->id]) }}">
                    @csrf
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <label class="form-label">Payment term</label>
                            <select name="payment_term_id" class="form-select @error('payment_term_id') is-invalid @enderror" required>
                                <option value="">Select payment term</option>
                                @foreach ($paymentTerms as $paymentTerm)
                                    <option value="{{ $paymentTerm->id }}" {{ (string) old('payment_term_id') === (string) $paymentTerm->id ? 'selected' : '' }}>{{ $paymentTerm->name }}</option>
                                @endforeach
                            </select>
                            @error('payment_term_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier term</label>
                            <select name="supplier_term_id" class="form-select @error('supplier_term_id') is-invalid @enderror" required>
                                <option value="">Select supplier term</option>
                                @foreach ($supplierTerms as $supplierTerm)
                                    <option value="{{ $supplierTerm->id }}" {{ (string) old('supplier_term_id') === (string) $supplierTerm->id ? 'selected' : '' }}>{{ $supplierTerm->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_term_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-5">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="award_mode" id="award_mode_whole" value="whole" {{ old('award_mode', 'whole') === 'whole' ? 'checked' : '' }}>
                            <label class="form-check-label" for="award_mode_whole">Award the whole request to one supplier</label>
                        </div>
                        <select name="award_supplier_id" class="form-select @error('award_supplier_id') is-invalid @enderror">
                            <option value="">Select supplier</option>
                            @foreach ($pr->quotations as $quotation)
                                <option value="{{ $quotation->supplier_id }}" {{ (string) old('award_supplier_id') === (string) $quotation->supplier_id ? 'selected' : '' }}>{{ $quotation->supplier->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">This supplier must be able to supply the full quantity of every item. If a supplier is short, split the quantity.</div>
                        @error('award_supplier_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="award_mode" id="award_mode_split" value="split" {{ old('award_mode') === 'split' ? 'checked' : '' }}>
                            <label class="form-check-label" for="award_mode_split">Split the quantity across suppliers</label>
                        </div>
                        <div class="text-gray-600 fs-7 mb-4">Enter how many to buy from each supplier. The total for each item must equal the requested quantity, and it cannot be more than the available quantity.</div>
                        @error('awards')
                            <div class="text-danger mb-3">{{ $message }}</div>
                        @enderror
                        @foreach ($pr->items as $item)
                            <div class="fw-semibold mb-2">{{ $item->product->name }} <span class="text-gray-600 fw-normal">requested {{ $item->quantity }} {{ $item->uom->name }}</span></div>
                            <div class="table-responsive mb-5">
                                <table class="table table-row-bordered align-middle gs-3 mb-0">
                                    <thead>
                                        <tr class="fw-bold text-gray-700">
                                            <th>Supplier</th>
                                            <th>Available</th>
                                            <th>Unit price</th>
                                            <th style="width: 160px;">Award qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pr->quotations as $quotation)
                                            @php
                                                $quoteItem = $quotation->items->firstWhere('purchase_request_item_id', $item->id);
                                                $available = $quoteItem && $quoteItem->available_qty !== null ? $quoteItem->available_qty : $item->quantity;
                                            @endphp
                                            <tr>
                                                <td>{{ $quotation->supplier->name }}</td>
                                                <td>{{ $available }}</td>
                                                <td>{{ $quoteItem && $quoteItem->unit_price !== null ? $quoteItem->unit_price : '—' }}</td>
                                                <td>
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        step="0.0001"
                                                        name="awards[{{ $item->id }}][{{ $quotation->id }}]"
                                                        value="{{ old('awards.' . $item->id . '.' . $quotation->id) }}"
                                                        class="form-control form-control-sm"
                                                    >
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-success">Award and create purchase orders</button>
                </form>
            </div>
        </div>
    @endif

</x-default-layout>
