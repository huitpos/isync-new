<x-default-layout>

    @section('title')
        Delivery {{ $pd->pd_number }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.purchaseDeliveries.show', $company, $branch, $pd) }}
    @endsection

    <div class="card">
        <form class="mt-3" action="{{ route('company.purchase-deliveries.update', ['companySlug' => $company->slug, 'purchase_delivery' => $pd->id]) }}" method="POST" novalidate enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card-body py-4">
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Supplier</label>
                        <input value="{{ $pd->purchaseOrder->supplier->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Purchase Order</label>
                        <input value="{{ $pd->purchaseOrder->po_number }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <input value="{{ $pd->status }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Action By</label>
                        <input value="{{ $pd->actionBy?->name }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Sales Invoice Number</label>
                        <input value="{{ $pd->sales_invoice_number }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Delivery Number</label>
                        <input value="{{ $pd->delivery_number }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="mt-7">
                    <h2>Items</h2>

                    @foreach($pd->items as $key => $item)
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
                                <input value="{{ $item->purchaseOrderItem->quantity }}" type="text" readonly class="form-control"/>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Balance:</label>
                                <input value="{{ $item->purchaseOrderItem->balance }}" type="text" readonly class="form-control"/>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Unit Price:</label>
                                <input
                                    value="{{ $item->unit_price }}"
                                    type="text"
                                    class="form-control text-end"
                                    readonly
                                />
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Receive Quantity:</label>
                                <input
                                    value="{{ $item->qty }}"
                                    placeholder="0"
                                    type="number"
                                    class="form-control text-end"
                                    readonly
                                />

                                @error('items.' . $key. '.qty')
                                    <div class="invalid-feedback"> {{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </form>
    </div>
</x-default-layout>

