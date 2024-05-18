<x-default-layout>

    @section('title')
        Stock Tranfer Request {{ $str->str_number }}
    @endsection

    @section('breadcrumbs')
        {{-- {{ Breadcrumbs::render('company.stockTransferRequests.show', $company, $str) }} --}}
    @endsection

    <div class="card">
        <form class="mt-3" action="{{ route('branch.stock-transfer-requests.update', ['companySlug' => $company->slug, 'stock_transfer_request' => $str->id, 'branchSlug' => $branch->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card-body py-4">
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">STR Number</label>
                        <input value="{{ $str->str_number }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <input value="{{ ucfirst($str->status) }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                @if ($str->status != 'pending')
                <div class="row mb-5">
                    <div class="col-md-12">
                        <label class="form-label">Approved/Rejected By</label>
                        <input value="{{ ucfirst($str->actionBy?->name) }}" type="text" readonly class="form-control"/>
                    </div>
                </div>
                @endif

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Requested By</label>
                        <input value="{{ $str->createdBy->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <input value="{{ $str->department->name }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">Delivery Location</label>
                        <input value="{{ $str->deliveryLocation->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6 mb-5">
                        <label class="form-label">Source Branch</label>
                        <input value="{{ $str->sourceBranch->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Delivery Address</label>
                        <input type="text"
                            value="{{ $str->deliveryLocation->unit_floor_number . ', ' . $str->deliveryLocation->street . ', ' . $str->deliveryLocation->barangay->name . ', ' . $str->deliveryLocation->city->name . ', ' . $str->deliveryLocation->province->name . ', ' . $str->deliveryLocation->region->name }}"
                            readonly
                            class="form-control"
                        />
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-12 mb-5">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" readonly>{{ $str->remarks }}</textarea>
                    </div>
                </div>

                <div class="mt-7">
                    <h2>Items</h2>
                    @php $grandtotal = 0; @endphp
                    @foreach($str->items as $item)
                        @php $grandtotal += $item->product->cost * $item->quantity @endphp
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
                            <input value="{{ $item->quantity }}" type="text" readonly class="form-control"/>
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
                                <h2>TOTAL: <span class="grandtotal"> {{ $grandtotal }}</span></h2>
                            </div>
                        </div>
                    </div>

                    @if($str->status == 'pending')
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