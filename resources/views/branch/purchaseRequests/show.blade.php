<x-default-layout>

    @section('title')
        Purchase Request {{ $pr->pr_number }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.purchaseRequests.show', $company, $branch, $pr) }}
    @endsection

    <div class="card">
        <form class="mt-3" action="{{ route('branch.purchase-requests.update', ['companySlug' => $company->slug, 'purchase_request' => $pr->id, 'branchSlug' => $branch->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card-body py-4">
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">PR Number</label>
                        <input value="{{ $pr->pr_number }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <input value="{{ ucfirst($pr->status) }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                @if ($pr->status != 'pending')
                <div class="row mb-5">
                    <div class="col-md-12">
                        <label class="form-label">Approved/Rejected By:</label>
                        <input value="{{ $pr->actionBy->name }}" type="text" readonly class="form-control"/>
                    </div>
                </div>
                @endif

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Requested By</label>
                        <input value="{{ $pr->createdBy->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <input value="{{ $pr->department->name }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <input value="For PO" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Date Needed</label>
                        <input value="{{ $pr->date_needed }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">Delivery Location</label>
                        <input value="{{ $pr->deliveryLocation->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Supplier</label>
                        <input value="{{ $pr->supplier->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Delivery Address</label>
                        <input type="text"
                            value="{{ $pr->deliveryLocation->unit_floor_number . ', ' . $pr->deliveryLocation->street . ', ' . $pr->deliveryLocation->barangay->name . ', ' . $pr->deliveryLocation->city->name . ', ' . $pr->deliveryLocation->province->name . ', ' . $pr->deliveryLocation->region->name }}"
                            readonly
                            class="form-control"
                        />
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-12 mb-5">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" readonly>{{ $pr->remarks }}</textarea>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Payment Terms</label>
                        <select {{ $pr->status != 'pending' ? 'disabled' : '' }} name="payment_term_id" id="" class="form-select @error('payment_term_id') is-invalid @enderror">
                            @foreach($paymentTerms as $paymentTerm)
                                <option value="{{ $paymentTerm->id }}" {{ $pr->payment_term_id == $paymentTerm->id ? 'selected' : '' }}>{{ $paymentTerm->name }}</option>
                            @endforeach
                        </select>

                        @error('payment_term_id')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Supplier Terms</label>
                        <select {{ !empty($pr->supplier->supplier_term_id) ? 'disabled' : '' }} {{ $pr->status != 'pending' ? 'disabled' : '' }} name="{{ !empty($pr->supplier->supplier_term_id) ? '' : 'supplier_term_id' }}" id="" class="form-select @error('supplier_term_id') is-invalid @enderror">
                            @foreach($supplierTerms as $supplierTerm)
                                <option value="{{ $supplierTerm->id }}" {{ ($pr->status != 'pending' && $pr->supplier_term_id == $supplierTerm->id) || ($pr->status == 'pending' && $pr->supplier->supplier_term_id == $supplierTerm->id) ? 'selected' : '' }}>{{ $supplierTerm->name }}</option>
                            @endforeach
                        </select>

                        <input name="{{ empty($pr->supplier->supplier_term_id) ? '' : 'supplier_term_id' }}" value="{{ $pr->supplier->supplier_term_id }}" type="hidden" readonly class="form-control"/>

                        @error('supplier_term_id')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-7">
                    <h2>Items</h2>
                    @foreach($pr->items as $item)
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
                            <textarea readonly class="form-control">{{ $item->remarks }}</textarea>
                        </div>
                    </div>
                    @endforeach

                    <div class="row mb-5">
                        <div class="col-md-12">
                            <div class="form-group float-end">
                                <h2>TOTAL: <span class="grandtotal"> {{ $pr->total }}</span></h2>
                            </div>
                        </div>
                    </div>

                    @if($pr->status == 'pending')
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