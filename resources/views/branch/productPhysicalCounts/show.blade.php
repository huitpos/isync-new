<x-default-layout>

    @section('title')
        Product Physical Count #{{ $count->pcount_number }}
    @endsection

    @section('breadcrumbs')
        {{-- {{ Breadcrumbs::render('company.stockTransferRequests.show', $company, $count) }} --}}
    @endsection

    <div class="card">
        <form class="mt-3" action="{{ route('branch.product-physical-counts.update', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug, 'product_physical_count' => $count->id]) }}" method="POST" novalidate enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card-body py-4">
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <input value="{{ ucfirst($count->status) }}" type="text" readonly class="form-control"/>
                    </div>

                    @if ($count->status != 'pending')
                    <div class="col-md-6">
                        <label class="form-label">Approved/Rejected By</label>
                        <input value="{{ ucfirst($count->actionBy?->name) }}" type="text" readonly class="form-control"/>
                    </div>
                    @endif
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Requested By</label>
                        <input value="{{ $count->createdBy->name }}" type="text" readonly class="form-control"/>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <input value="{{ ($count->department_id == 'all' || empty($count->department_id)) ? "All" : $count->department->name }}" type="text" readonly class="form-control"/>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-12 mb-5">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" readonly>{{ $count->remarks }}</textarea>
                    </div>
                </div>

                <div class="mt-7">
                    <h2>Items</h2>
                    @foreach($count->items as $item)
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
                            <label class="form-label">Counted:</label>
                            <input value="{{ $item->quantity }}" type="text" readonly class="form-control"/>
                        </div>

                        @php
                            $systemQty = (float) ($item->product->branches->firstWhere('id', $branch->id)?->pivot->stock ?? 0);
                            $countedQty = $item->uom_id == $item->product->uom_id
                                ? (float) $item->quantity
                                : app(\App\Services\FifoCostService::class)->stockQty($item->product, $item->quantity, $item->uom_id);
                            $variance = $countedQty - $systemQty;
                            $unitCost = (float) ($item->product->branches->firstWhere('id', $branch->id)?->pivot->cost ?? 0);
                        @endphp
                        <div class="col-md-3 mt-3">
                            <label class="form-label">System stock</label>
                            <input value="{{ number_format($systemQty, 2) }}" type="text" readonly class="form-control"/>
                        </div>
                        <div class="col-md-3 mt-3">
                            <label class="form-label">Variance</label>
                            <input value="{{ number_format($variance, 2) }}" type="text" readonly class="form-control"/>
                        </div>
                        <div class="col-md-3 mt-3">
                            <label class="form-label">Variance value</label>
                            <input value="{{ number_format($variance * $unitCost, 2) }}" type="text" readonly class="form-control"/>
                        </div>

                        <div class="col-md-12 mt-3">
                            <label class="form-label">Remarks:</label>
                            <textarea readonly class="form-control">{{ $item->remarks }}</textarea>
                        </div>
                    </div>
                    @endforeach

                    @if($count->status == 'pending')
                    <div class="mt-8">
                        <input type="hidden" name="status" id="status">
                        <button type="submit" class="btn btn-success disable-on-click" data-button-link="#status" value="approved" data-confirm="Approve this count for {{ $count->items->count() }} item(s)? Stock will be replaced by the counted quantity. Check the variance column first.">Approve</button>
                        <button type="submit" class="btn btn-danger disable-on-click ms-4" data-button-link="#status" value="rejected" data-confirm="Reject this physical count? Stock will not change.">Reject</button>
                    </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</x-default-layout>