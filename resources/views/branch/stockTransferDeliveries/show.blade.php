<x-default-layout>

    @section('title')
        Stock Tranfer Request {{ $std->str_number }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.stockTransferDeliveries.show', $company, $branch, $std) }}
    @endsection

    <div class="card">
        <form class="mt-3" action="{{ route('branch.stock-transfer-deliveries.update', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug , 'stock_transfer_delivery' => $std->id]) }}" method="POST" novalidate enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card-body py-4">
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Control Number</label>
                        <input value="{{ $std->std_number }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <input value="{{ ucfirst($std->status) }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                @if ($std->status != 'pending')
                <div class="row mb-5">
                    <div class="col-md-12">
                        <label class="form-label">Approved/Rejected By</label>
                        <input value="{{ ucfirst($std->actionBy?->name) }}" type="text" readonly class="form-control"/>
                    </div>
                </div>
                @endif

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Requested By</label>
                        <input value="{{ $std->createdBy->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6 mb-5">
                        <label class="form-label">Source Branch</label>
                        <input value="{{ $std->sourceBranch->name }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="mt-7">
                    <h2>Items</h2>
                    @foreach($std->items as $item)
                    <hr>
                    <div class="form-group row mb-5 bg-light-dark p-2">
                        <div class="col-md-4">
                            <label class="form-label">Product:</label>
                            <input value="{{ $item->product->name }}" type="text" readonly class="form-control"/>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">UOM:</label>
                            <input value="{{ $item->uom->name }}" type="text" readonly class="form-control"/>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Barcode:</label>
                            <input value="{{ $item->product->barcode }}" type="text" readonly class="form-control"/>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Quantity:</label>
                            <input name="item_id[]" value="{{ $item->id }}" type="hidden">
                            <input name="qty[]" value="{{ $item->qty }}" type="number" class="form-control"/>
                        </div>

                        <div class="col-md-12 mt-3">
                            <label class="form-label">Remarks:</label>
                            <textarea readonly class="form-control">{{ $item->remarks }}</textarea>
                        </div>
                    </div>
                    @endforeach

                    @if($std->status == 'pending')
                    <div class="mt-8">
                        <input type="hidden" name="status" id="status">
                        <button type="submit" class="btn btn-success disable-on-click" data-button-link="#status" value="for_review">Submit For Approval</button>
                    </div>
                    @endif

                    @if($std->status == 'for_review')
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