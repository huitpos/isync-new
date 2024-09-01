<x-default-layout>

    @section('title')
        Delivery {{ $pd->pd_number }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.purchaseDeliveries.show', $company, $branch, $pd) }}
    @endsection

    <div class="card">
        <form class="mt-3" action="{{ route('branch.purchase-deliveries.update', ['companySlug' => $company->slug, 'purchase_delivery' => $pd->id, 'branchSlug' => $branch->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
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
                    <div class="col-md-{{ $pd->status != 'pending' ? 6 : 12 }}">
                        <label class="form-label">Status</label>
                        <input value="{{ $pd->status }}" type="text" readonly class="form-control"/>
                    </div>

                    @if ($pd->status != 'pending')
                    <div class="col-md-6">
                        <label class="form-label">Approved/Rejected By</label>
                        <input value="{{ $pd->actionBy?->name }}" type="text" readonly class="form-control"/>
                    </div>
                    @endif
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

                    <div class="row mb-5">
                        @if ($pd->status == 'approved')
                        <div class="col-md-6">
                            <div class="form-group">
                                <a href="{{ route('branch.purchase-deliveries.print', ['companySlug' => $company->slug, 'id' => $pd->id, 'branchSlug' => $branch->slug]) }}" class="btn btn-primary">Print</a>
                            </div>
                        </div>
                        @endif

                        <div class="col-md-<?= $pd->status != 'approved' ? '12' : '6' ?>">
                            <div class="form-group float-end">
                                <h2>TOTAL: <span class="total">{{ number_format($pd->total_amount, 2) }}</span></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        @if($pd->status == 'pending')
                            <div class="form-group">
                                <input type="hidden" name="status" id="status">
                                <button type="submit" class="btn btn-success disable-on-click" data-button-link="#status" value="approved">Approve</button>
                                <button type="submit" class="btn btn-danger disable-on-click ms-4" data-button-link="#status" value="rejected">Reject</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-default-layout>

