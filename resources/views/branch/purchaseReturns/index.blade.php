<x-default-layout>

    @section('title')
        Purchase Returns
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.purchaseReturns.index', $company, $branch) }}
    @endsection

    <div class="card">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <form method="GET" class="d-flex flex-wrap align-items-center gap-3 w-100">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-solid w-250px" placeholder="Search">
                <select name="status" class="form-select form-select-solid w-150px">
                    <option value="">Status</option>
                    @foreach (['pending', 'approved', 'rejected', 'void'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-light">Filter</button>
                <a href="{{ route('branch.purchase-returns.create', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]) }}" class="btn btn-primary ms-auto">Add Purchase Return</a>
            </form>
        </div>
        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-bordered fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase">
                            <th>Return Number</th>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($returns as $purchaseReturn)
                            <tr>
                                <td>
                                    <a href="{{ route('branch.purchase-returns.show', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug, 'id' => $purchaseReturn->id]) }}">{{ $purchaseReturn->return_number }}</a>
                                </td>
                                <td>{{ $purchaseReturn->purchaseOrder->po_number }}</td>
                                <td>{{ $purchaseReturn->supplier->name }}</td>
                                <td>{{ $purchaseReturn->return_date->format('Y-m-d') }}</td>
                                <td>{{ ucfirst($purchaseReturn->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-gray-600">No purchase returns yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $returns->links() }}
        </div>
    </div>

</x-default-layout>
