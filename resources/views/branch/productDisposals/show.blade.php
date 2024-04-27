<x-default-layout>

    @section('title')
        Product Disposal #{{ $disposal->id }}
    @endsection

    @section('breadcrumbs')
        {{-- {{ Breadcrumbs::render('company.stockTransferRequests.show', $company, $disposal) }} --}}
    @endsection

    <div class="card">
        <form class="mt-3" action="{{ route('branch.product-disposals.update', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug, 'product_disposal' => $disposal->id]) }}" method="POST" novalidate enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card-body py-4">
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                    <input value="{{ ucfirst($disposal->status) }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Requested By</label>
                        <input value="{{ $disposal->createdBy->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <input value="{{ $disposal->department->name }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-12 mb-5">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" readonly>{{ $disposal->remarks }}</textarea>
                    </div>
                </div>

                <div class="mt-7">
                    <h2>Items</h2>
                    @foreach($disposal->items as $item)
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
                                <h2>TOTAL: <span class="grandtotal"> {{ $disposal->total }}</span></h2>
                            </div>
                        </div>
                    </div>

                    @if($disposal->status == 'pending')
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