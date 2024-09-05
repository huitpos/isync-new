<x-default-layout>

    @section('title')
        Purchase Order {{ $po->po_number }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.purchaseOrders.show', $company, $po) }}
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
                        <select name="payment_term_id" id="" class="form-select">
                            @foreach($company->paymentTerms as $paymentTerm)
                                <option value="{{ $paymentTerm->id }}" {{ $po->payment_term_id == $paymentTerm->id ? 'selected' : '' }}>{{ $paymentTerm->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Supplier Terms</label>
                        <select name="supplier_term_id" id="" class="form-select">
                            @foreach($company->supplierTerms as $supplierTerm)
                                <option value="{{ $supplierTerm->id }}" {{ $po->supplier_term_id == $supplierTerm->id ? 'selected' : '' }}>{{ $supplierTerm->name }}</option>
                            @endforeach
                        </select>
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
                        @if ($po->status == 'approved')
                        <div class="col-md-6">
                            <div class="form-group">
                                <a href="{{ route('company.purchase-orders.print', ['companySlug' => $company->slug, 'id' => $po->id]) }}" class="btn btn-primary">Print</a>
                            </div>
                        </div>
                        @endif

                        <div class="col-md-<?= $po->status != 'approved' ? '12' : '6' ?>">
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