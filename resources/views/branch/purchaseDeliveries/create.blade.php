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
                        <label class="form-label">Sales Invoice Number</label>
                        <input value="" name="sales_invoice_number" type="text" class="form-control @error('sales_invoice_number') is-invalid @enderror"/>

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
                                <input name="items[{{ $key }}][po_unit_price]"  value="{{ $item->unit_price }}" type="hidden" readonly class="form-control"/>
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
                                <button type="submit" value="1" class="btn btn-flex btn-light-warning disable-on-click" data-button-link="#is_closed">
                                    Receive and close PO
                                </button>

                                <button type="submit" value="0" class="btn btn-flex btn-light-success ms-5 disable-on-click" data-button-link="#is_closed">
                                    Receive
                                </button>
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
                total = 0;
                grandTotal = 0;
                totalQty = 0;
                $('.receive_quantity').each(function() {
                    var quantity = $(this).val();
                    var grandparentElement = $(this).parent().parent();

                    if (grandparentElement.find('.unit_price').val() == '') {
                        return
                    }

                    grandTotal += grandparentElement.find('.unit_price').val() * grandparentElement.find('.quantity').val();

                    if (quantity == '') {
                        return
                    }

                    totalQty += parseInt(quantity);

                    var balanceElem = grandparentElement.find('.balance');
                    var originalBalanceElem = grandparentElement.find('.original_balance');

                    var balance = originalBalanceElem.val() - quantity;
                    balanceElem.val(balance);

                    total += quantity * grandparentElement.find('.unit_price').val();
                    
                });

                $('.total').text(total.toFixed(2));
                $('.total').val(total.toFixed(2));
                $('#grandTotal').val(grandTotal.toFixed(2));
                $('#total_qty').val(totalQty.toFixed(2));
           }
        </script>
    @endpush
</x-default-layout>

