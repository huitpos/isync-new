<x-default-layout>

    @section('title')
        Create Debit Memo
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.debitMemos.create', $company) }}
    @endsection

    <div class="card mb-5">
        <div class="card-body">
            <form method="GET">
                <label class="form-label">Supplier invoice</label>
                <select name="supplier_invoice_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Select accepted invoice</option>
                    @foreach ($invoices as $option)
                        <option value="{{ $option->id }}" {{ $invoice && $invoice->id === $option->id ? 'selected' : '' }}>
                            {{ $option->invoice_number }} — {{ $option->supplier->name }} — {{ $option->purchaseOrder->po_number }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">A return line uses the purchase order price and cannot exceed the returned quantity that has already been billed. An allowance cannot exceed the remaining invoice line amount.</div>
            </form>
        </div>
    </div>

    @if ($invoice)
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('company.debit-memos.store', ['companySlug' => $company->slug]) }}">
                    @csrf
                    <input type="hidden" name="supplier_invoice_id" value="{{ $invoice->id }}">
                    <div class="row mb-5">
                        <div class="col-md-4">
                            <label class="form-label">Memo date</label>
                            <input type="date" name="memo_date" value="{{ old('memo_date', date('Y-m-d')) }}" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" value="{{ old('remarks') }}" class="form-control">
                        </div>
                    </div>

                    @error('items')
                        <div class="text-danger mb-4">{{ $message }}</div>
                    @enderror
                    @error('supplier_invoice_id')
                        <div class="text-danger mb-4">{{ $message }}</div>
                    @enderror

                    @foreach ($rows as $row)
                        @php $item = $row['item']; @endphp
                        <div class="border border-gray-300 rounded p-4 mb-4">
                            <div class="fw-semibold mb-3">{{ $item->product->name }} <span class="text-gray-600 fw-normal">billed {{ $item->qty }} {{ $item->uom->name }} · remaining {{ $row['remaining'] }}</span></div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Purchase return</label>
                                    <select name="items[{{ $item->id }}][purchase_return_item_id]" class="form-select debit-return" data-price-input="#debit-price-{{ $item->id }}">
                                        <option value="" data-price="{{ $item->unit_price }}">Allowance, no return</option>
                                        @foreach ($row['returns'] as $returnLine)
                                            <option value="{{ $returnLine['purchase_return_item_id'] }}" data-price="{{ $returnLine['unit_price'] }}" {{ (string) old('items.' . $item->id . '.purchase_return_item_id') === (string) $returnLine['purchase_return_item_id'] ? 'selected' : '' }}>
                                                {{ $returnLine['return_number'] }} — {{ $returnLine['open'] }} available
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" min="0" step="0.0001" name="items[{{ $item->id }}][qty]" value="{{ old('items.' . $item->id . '.qty') }}" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Unit price</label>
                                    <input id="debit-price-{{ $item->id }}" type="number" min="0" step="0.0001" name="items[{{ $item->id }}][unit_price]" value="{{ old('items.' . $item->id . '.unit_price', $item->unit_price) }}" class="form-control">
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div id="debit-line-count" class="text-gray-700 mb-4">Lines with a quantity: 0</div>
                    <button type="submit" class="btn btn-primary">Issue debit memo</button>
                </form>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            var debitForm = document.querySelector('form[method="POST"], form[method="post"]');
            var debitCount = document.getElementById('debit-line-count');
            function debitLineCount() {
                var count = 0;
                document.querySelectorAll('input[name$="[qty]"]').forEach(function (input) {
                    if (Number(input.value) > 0) {
                        count++;
                    }
                });
                if (debitCount) {
                    debitCount.textContent = 'Lines with a quantity: ' + count;
                }
                return count;
            }
            document.querySelectorAll('input[name$="[qty]"]').forEach(function (input) {
                input.addEventListener('input', debitLineCount);
            });
            debitLineCount();
            if (debitForm) {
                debitForm.addEventListener('submit', function (event) {
                    var count = debitLineCount();
                    if (!count) {
                        event.preventDefault();
                        window.alert('Enter a quantity for at least one line.');
                        return;
                    }
                    if (!window.confirm('Issue a debit memo for ' + count + ' line(s)? Lines left blank are not included.')) {
                        event.preventDefault();
                    }
                });
            }

            document.querySelectorAll('.debit-return').forEach(function (select) {
                var apply = function () {
                    var option = select.options[select.selectedIndex];
                    var price = document.querySelector(select.dataset.priceInput);
                    if (!price || !option) {
                        return;
                    }
                    price.value = option.dataset.price || price.value;
                    price.readOnly = select.value !== '';
                };
                select.addEventListener('change', apply);
                apply();
            });
        </script>
    @endpush

</x-default-layout>
