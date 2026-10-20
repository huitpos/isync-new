<x-default-layout>

    @section('title')
        Create Supplier Invoice
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.supplierInvoices.create', $company) }}
    @endsection

    <div class="card">
        <form class="mt-3" method="POST" action="{{ route('company.supplier-invoices.store', ['companySlug' => $company->slug]) }}">
            @csrf

            <div class="card-body py-4">
                <p class="text-gray-600">Encode the supplier bill against an approved purchase order that already has received quantity. A billed quantity above the open quantity, or a price different from the purchase order, is saved as a variance.</p>

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
                        <label class="form-label">Branch</label>
                        <select id="branch_id" class="form-select" required>
                            <option value="">Select branch</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Purchase Order</label>
                        <select id="purchase_order_id" name="purchase_order_id" class="form-select @error('purchase_order_id') is-invalid @enderror" required>
                            <option value="">Select purchase order</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Supplier</label>
                        <input id="supplier_name" type="text" class="form-control" readonly>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-4">
                        <label class="form-label">Invoice Number</label>
                        <input name="invoice_number" value="{{ old('invoice_number') }}" type="text" class="form-control @error('invoice_number') is-invalid @enderror" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Invoice Date</label>
                        <input name="invoice_date" value="{{ old('invoice_date') }}" type="date" class="form-control @error('invoice_date') is-invalid @enderror" required>
                    </div>
                </div>

                <div class="mt-7">
                    <h2>Items</h2>
                    <div id="invoice-items" class="text-gray-600">Select a purchase order to load received items.</div>
                </div>

                <div class="mt-8">
                    <button type="submit" class="btn btn-primary">Save Invoice</button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            const ordersUrl = '{{ route('company.supplier-invoices.open-purchase-orders', ['companySlug' => $company->slug]) }}';
            const linesUrl = '{{ route('company.supplier-invoices.purchase-order-lines', ['companySlug' => $company->slug]) }}';
            const itemsEl = document.getElementById('invoice-items');
            const poSelect = document.getElementById('purchase_order_id');
            const supplierInput = document.getElementById('supplier_name');

            function money(value) {
                return Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 4 });
            }

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            document.getElementById('branch_id').addEventListener('change', function () {
                poSelect.innerHTML = '<option value="">Select purchase order</option>';
                supplierInput.value = '';
                itemsEl.innerHTML = 'Select a purchase order to load received items.';
                if (!this.value) {
                    return;
                }

                fetch(ordersUrl + '?branch_id=' + this.value, { headers: { 'Accept': 'application/json' } })
                    .then(response => response.json())
                    .then(orders => {
                        orders.forEach(order => {
                            const option = document.createElement('option');
                            option.value = order.id;
                            option.textContent = order.po_number + ' — ' + order.supplier;
                            option.dataset.supplier = order.supplier;
                            poSelect.appendChild(option);
                        });
                    });
            });

            document.querySelector('form').addEventListener('submit', function (event) {
                if (!document.querySelector('input[name^="items"]')) {
                    event.preventDefault();
                    window.alert('Select a purchase order with received items first.');
                    return;
                }

                var differences = 0;
                document.querySelectorAll('.variance-note').forEach(function (note) {
                    if (note.textContent.trim() !== '') {
                        differences++;
                    }
                });

                var message = differences
                    ? differences + ' line(s) differ from the purchase order. Save the invoice with those variances?'
                    : 'Save this supplier invoice?';

                if (!window.confirm(message)) {
                    event.preventDefault();
                }
            });

            poSelect.addEventListener('change', function () {
                const selected = this.options[this.selectedIndex];
                supplierInput.value = selected.dataset.supplier || '';
                itemsEl.innerHTML = '';
                if (!this.value) {
                    itemsEl.textContent = 'Select a purchase order to load received items.';
                    return;
                }

                fetch(linesUrl + '?purchase_order_id=' + this.value, { headers: { 'Accept': 'application/json' } })
                    .then(response => response.json())
                    .then(payload => {
                        if (!payload.items.length) {
                            itemsEl.textContent = 'This purchase order has no open received quantity.';
                            return;
                        }

                        payload.items.forEach((item, index) => {
                            const row = document.createElement('div');
                            row.className = 'form-group row mb-5 bg-light-dark p-2';
                            row.innerHTML = `
                                <input type="hidden" name="items[${index}][purchase_order_item_id]" value="${item.purchase_order_item_id}">
                                <div class="col-md-2"><label class="form-label">Product</label><input class="form-control" readonly value="${escapeHtml(item.product)}"></div>
                                <div class="col-md-1"><label class="form-label">UOM</label><input class="form-control" readonly value="${escapeHtml(item.uom)}"></div>
                                <div class="col-md-1"><label class="form-label">Ordered</label><input class="form-control" readonly value="${item.ordered}"></div>
                                <div class="col-md-1"><label class="form-label">Received</label><input class="form-control" readonly value="${item.received}"></div>
                                <div class="col-md-1"><label class="form-label">Billed</label><input class="form-control" readonly value="${item.billed}"></div>
                                <div class="col-md-1"><label class="form-label">Open</label><input class="form-control open-qty" readonly value="${item.open}"></div>
                                <div class="col-md-1"><label class="form-label">PO Price</label><input class="form-control po-price" readonly value="${money(item.po_unit_price)}"></div>
                                <div class="col-md-2"><label class="form-label">Invoice Qty</label><input name="items[${index}][qty]" type="number" min="0" step="0.0001" class="form-control text-end invoice-qty" value="${item.open}"></div>
                                <div class="col-md-2"><label class="form-label">Invoice Price</label><input name="items[${index}][unit_price]" type="number" min="0" step="0.0001" class="form-control text-end invoice-price" value="${item.po_unit_price}"></div>
                                <div class="col-12 mt-2 variance-note text-danger"></div>
                            `;
                            itemsEl.appendChild(row);

                            const qtyInput = row.querySelector('.invoice-qty');
                            const priceInput = row.querySelector('.invoice-price');
                            const note = row.querySelector('.variance-note');
                            const mark = () => {
                                const notes = [];
                                if (Number(qtyInput.value) > Number(item.open)) {
                                    notes.push('Quantity is above the open received quantity.');
                                }
                                if (Number(priceInput.value).toFixed(4) !== Number(item.po_unit_price).toFixed(4)) {
                                    notes.push('Price is different from the purchase order.');
                                }
                                note.textContent = notes.join(' ');
                            };
                            qtyInput.addEventListener('input', mark);
                            priceInput.addEventListener('input', mark);
                        });
                    });
            });
        </script>
    @endpush
</x-default-layout>
