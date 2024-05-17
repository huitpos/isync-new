<x-default-layout>

    @section('title')
        Stock Tranfer Request {{ $sto->str_number }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.stockTransferOrders.show', $company, $branch, $sto) }}
    @endsection

    <div class="card">
        <form class="mt-3" action="{{ route('branch.stock-transfer-orders.update', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug , 'stock_transfer_order' => $sto->id]) }}" method="POST" novalidate enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card-body py-4">
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">STO Number</label>
                        <input value="{{ $sto->sto_number }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <input value="{{ ucfirst($sto->status) }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Requested By</label>
                        <input value="{{ $sto->createdBy->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <input value="{{ $sto->department->name }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">Delivery Location</label>
                        <input value="{{ $sto->deliveryLocation->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6 mb-5">
                        <label class="form-label">Source Branch</label>
                        <input value="{{ $sto->sourceBranch->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Delivery Address</label>
                        <input type="text"
                            value="{{ $sto->deliveryLocation->unit_floor_number . ', ' . $sto->deliveryLocation->street . ', ' . $sto->deliveryLocation->barangay->name . ', ' . $sto->deliveryLocation->city->name . ', ' . $sto->deliveryLocation->province->name . ', ' . $sto->deliveryLocation->region->name }}"
                            readonly
                            class="form-control"
                        />
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-12 mb-5">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" readonly>{{ $sto->remarks }}</textarea>
                    </div>
                </div>

                <div class="mt-7">
                    <h2>Items</h2>
                    @foreach($sto->items as $item)
                    <hr>
                    <div class="form-group row mb-5 bg-light-dark p-2">
                        <div class="col-md-3">
                            <label class="form-label">Product:</label>
                            <input value="{{ $item->product->name }}" type="text" readonly class="form-control"/>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">UOM:</label>
                            <input value="{{ $item->uom->name }}" type="text" readonly class="form-control"/>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Barcode:</label>
                            <input value="{{ $item->product->barcode }}" type="text" readonly class="form-control"/>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Cost:</label>
                            <input value="{{ $item->product->cost }}" type="text" readonly class="form-control"/>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Quantity:</label>
                            <input name="item_id[]" value="{{ $item->id }}" type="hidden">
                            <input name="quantity[]" value="{{ $item->quantity }}" type="number" class="form-control"/>
                        </div>

                        <div class="col-md-12 mt-3">
                            <label class="form-label">Remarks:</label>
                            <textarea readonly class="form-control">{{ $item->remarks }}</textarea>
                        </div>
                    </div>
                    @endforeach

                    @if($sto->status == 'pending')
                    <div class="mt-8">
                        <input type="hidden" name="status" id="status">
                        <button type="submit" class="btn btn-success disable-on-click" data-button-link="#status" value="for_review">Submit For Approval</button>
                    </div>
                    @endif

                    @if($sto->status == 'for_review')
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