<x-default-layout>

    @section('title')
        Purchase Order {{ $po->po_number }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.purchaseOrders.show', $company, $branch, $po) }}
    @endsection

    <div class="card">
        <form class="mt-3" action="{{ route('company.purchase-requests.update', ['companySlug' => $company->slug, 'purchase_request' => $po->id]) }}" method="POST" novalidate enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card-body py-4">
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">PR Number</label>
                        <input value="{{ $po->purchaseRequest->pr_number }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <input value="{{ ucfirst($po->status) }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Requested By</label>
                        <input value="{{ $po->createdBy->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <input value="{{ $po->department->name }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <input value="For PO" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Date Needed</label>
                        <input value="{{ $po->date_needed }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">Delivery Location</label>
                        <input value="{{ $po->deliveryLocation->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Supplier</label>
                        <input value="{{ $po->supplier->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Delivery Address</label>
                        <input type="text"
                            value="{{ $po->deliveryLocation->unit_floor_number . ', ' . $po->deliveryLocation->street . ', ' . $po->deliveryLocation->barangay->name . ', ' . $po->deliveryLocation->city->name . ', ' . $po->deliveryLocation->province->name . ', ' . $po->deliveryLocation->region->name }}"
                            readonly
                            class="form-control"
                        />
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-12 mb-5">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" readonly>{{ $po->pr_remarks }}</textarea>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Payment Terms</label>
                        <input value="{{ $po->paymentTerm->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Supplier Terms</label>
                        <input value="{{ $po->supplierTerm->name }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="mt-7">
                    <h2>Items</h2>
                    @foreach($po->items as $item)
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
                            <label class="form-label">Barcode:</label>
                            <input value="{{ $item->product->barcode }}" type="text" readonly class="form-control"/>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Unit Price:</label>
                            <input value="{{ $item->unit_price }}" type="text" readonly class="form-control text-end"/>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Quantity:</label>
                            <input value="{{ $item->quantity }}" type="text" readonly class="form-control"/>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Total:</label>
                            <input value="{{ $item->total }}" type="text" readonly class="form-control text-end"/>
                        </div>

                        <div class="col-md-12 mt-3">
                            <label class="form-label">Remarks:</label>
                            <textarea readonly class="form-control">{{ $item->pr_remarks }}</textarea>
                        </div>
                    </div>
                    @endforeach

                    <div class="row mb-5">
                        <div class="col-md-12">
                            <div class="form-group float-end">
                                <h2>TOTAL: <span class="grandtotal"> {{ $po->total }}</span></h2>
                            </div>
                        </div>
                    </div>

                    @if($po->status == 'pending')
                        <div class="mt-8">
                            <input type="hidden" name="status" id="status">
                            <button type="submit" class="btn btn-success disable-on-click" data-button-link="#status" value="approved">Approve</button>
                            <button type="submit" class="btn btn-danger disable-on-click ms-4" data-button-link="#status" value="rejected">Reject</button>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</x-default-layout>