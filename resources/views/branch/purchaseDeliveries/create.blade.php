<x-default-layout>

    @section('title')
        Create Delivery for {{ $po->po_number }}
    @endsection

    @section('breadcrumbs')
        {{-- {{ Breadcrumbs::render('branch.users.create', $company, $branch) }} --}}
    @endsection

    <div class="card">
        <form class="mt-3" action="{{ route('branch.purchase-deliveries.store', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="purchase_order_id" value="{{ $po->id }}">
            <input name="total_amount" type="hidden" class="total" value="0">
            <input id="total_qty" name="total_qty" type="hidden" class="total" value="0">
            <input name="is_closed" type="hidden" id="is_closed" value="0">

            <div class="card-body py-4">
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Supplier</label>
                        <input value="{{ $po->supplier->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Total</label>
                        <input name="grandtotal" value="{{ $po->total }}" type="text" id="grandTotal" readonly class="form-control"/>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Supplier Reference</label>
                        <input value="" name="sales_invoice_number" type="text" class="form-control @error('sales_invoice_number') is-invalid @enderror"/>
                        <div class="form-text">Optional. The supplier bill is encoded later as a Supplier Invoice.</div>

                        @error('sales_invoice_number')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Delivery Number</label>
                        <input value="" name="delivery_number" type="text" class="form-control @error('delivery_number') is-invalid @enderror"/>

                        @error('delivery_number')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-7">
                    <h2>Items</h2>

                    @php
                        $items = $po->items;
                        $old = old('items');
                    @endphp

                    @foreach($items as $key => $item)
                        <input name="items[{{ $key }}][purchase_order_item_id]" value="{{ $item->id }}" type="hidden"/>
                        <input name="items[{{ $key }}][product_id]" value="{{ $item->product_id }}" type="hidden"/>
                        <input name="items[{{ $key }}][uom_id]" value="{{ $item->uom_id }}" type="hidden"/>

                        <hr>
                        <div class="form-group row mb-5 bg-light-dark p-2">
                            <div class="col-md-2">
                                <label class="form-label">Product:</label>
                                <input value="{{ $item->product->name }}" type="text" readonly class="form-control"/>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">UOM:</label>
                                <input value="{{ $item->uom->name }}" type="text" readonly class="form-control"/>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Quantity:</label>
                                <input value="{{ $item->quantity }}" type="text" readonly class="form-control quantity"/>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Balance:</label>
                                <input name="items[{{ $key }}][balance]" value="{{ $item->balance }}" type="text" readonly class="form-control balance"/>
                                <input value="{{ $item->balance }}" type="hidden" readonly class="form-control original_balance"/>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Unit Price:</label>
                                <input name="items[{{ $key }}][po_unit_price]"  value="{{ $item->unit_price }}" type="hidden" readonly class="form-control po_unit_price"/>
                                <input
                                    name="items[{{ $key }}][unit_price]"
                                    value="{{ $old[$key]['unit_price'] ?? $item->unit_price }}"
                                    type="text"
                                    class="form-control text-end @error('items.' . $key. '.unit_price') is-invalid @enderror unit_price"
                                />

                                @error('items.' . $key. '.unit_price')
                                    <div class="invalid-feedback"> {{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Receive Quantity:</label>
                                <input
                                    value="{{ $old[$key]['qty'] ?? '' }}"
                                    name="items[{{ $key }}][qty]"
                                    placeholder="0"
                                    type="number"
                                    min="0"
                                    step="0.0001"
                                    class="form-control text-end @error('items.' . $key. '.qty') is-invalid @enderror receive_quantity"
                                />

                                @error('items.' . $key. '.qty')
                                    <div class="invalid-feedback"> {{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endforeach

                    <div class="row mb-5">
                        <div class="col-md-6">
                            <div class="form-group">
                                <button type="submit" value="0" class="btn btn-flex btn-light-success disable-on-click receive-btn" data-button-link="#is_closed" data-confirm="Receive these quantities?">
                                    Receive
                                </button>

                                <button type="submit" value="1" class="btn btn-flex btn-light-warning ms-5 disable-on-click receive-close-btn" data-button-link="#is_closed" data-confirm="Receive these quantities and close the purchase order? Any remaining balance can no longer be received.">
                                    Receive and close PO
                                </button>
                                <div class="form-text mt-3">Leave receive quantity blank for items that did not arrive. A price that differs from the purchase order is called out before you save.</div>
                                <div id="receive-warning" class="text-danger mt-2"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group float-end">
                                <h2>TOTAL: <span class="total">0.00</span></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            recalculate();

            $(document).on('change keyup', '.receive_quantity', function() {
                recalculate()
            });

            $(document).on('change keyup', '.unit_price', function() {
                recalculate()
            });

           function recalculate()
           {
                var total = 0;
                var totalQty = 0;
                var blocked = false;
                var priceDiff = false;
                var blockMessage = 'Enter a receive quantity for at least one item.';

                $('.receive_quantity').each(function() {
                    var raw = $(this).val();
                    var qty = raw === '' ? 0 : parseFloat(raw);
                    var row = $(this).parent().parent();
                    var original = parseFloat(row.find('.original_balance').val()) || 0;
                    var price = parseFloat(row.find('.unit_price').val());
                    var poPrice = parseFloat(row.find('.po_unit_price').val());

                    if (isNaN(qty) || qty < 0) {
                        blocked = true;
                        blockMessage = 'Receive quantity cannot be negative.';
                        $(this).addClass('is-invalid');
                        qty = 0;
                    } else if (qty > original + 0.0000001) {
                        blocked = true;
                        blockMessage = 'Receive quantity cannot be more than the balance.';
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }

                    row.find('.balance').val((original - qty).toFixed(4));

                    if (qty > 0) {
                        totalQty += qty;
                        if (!isNaN(price)) {
                            total += qty * price;
                        }
                        if (!isNaN(poPrice) && !isNaN(price) && Math.abs(price - poPrice) > 0.0001) {
                            priceDiff = true;
                        }
                    }
                });

                if (totalQty <= 0) {
                    blocked = true;
                }

                $('.total').text(total.toFixed(2));
                $('.total').val(total.toFixed(2));
                $('#total_qty').val(totalQty.toFixed(4));

                var priceNote = priceDiff ? ' Some prices differ from the purchase order.' : '';
                $('.receive-btn').attr('data-confirm', 'Receive ' + totalQty + ' in total?' + priceNote);
                $('.receive-close-btn').attr('data-confirm', 'Receive ' + totalQty + ' and close this purchase order? Any remaining balance can no longer be received.' + priceNote);
                $('.receive-btn, .receive-close-btn').attr('data-blocked', blocked ? '1' : '0');
                $('.receive-btn, .receive-close-btn').attr('data-blocked-message', blockMessage);
                $('#receive-warning').text(priceDiff ? 'A unit price is different from the purchase order.' : '');
           }
        </script>
    @endpush
</x-default-layout>

