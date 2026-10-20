<x-default-layout>

    @section('title')
        Debit Memos
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.debitMemos.index', $company) }}
    @endsection

    <div class="card">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <form method="GET" class="d-flex flex-wrap align-items-center gap-3 w-100">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-solid w-250px" placeholder="Search">
                <select name="branch_id" class="form-select form-select-solid w-200px">
                    <option value="">Branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (string) request('branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select form-select-solid w-150px">
                    <option value="">Status</option>
                    <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Issued</option>
                    <option value="void" {{ request('status') === 'void' ? 'selected' : '' }}>Void</option>
                </select>
                <button type="submit" class="btn btn-light">Filter</button>
                <a href="{{ route('company.debit-memos.create', ['companySlug' => $company->slug]) }}" class="btn btn-primary ms-auto">Add Debit Memo</a>
            </form>
        </div>
        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-bordered fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase">
                            <th>Memo Number</th>
                            <th>Invoice</th>
                            <th>Supplier</th>
                            <th>Branch</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($memos as $memo)
                            <tr>
                                <td>
                                    <a href="{{ route('company.debit-memos.show', ['companySlug' => $company->slug, 'id' => $memo->id]) }}">{{ $memo->memo_number }}</a>
                                </td>
                                <td>{{ $memo->supplierInvoice->invoice_number }}</td>
                                <td>{{ $memo->supplier->name }}</td>
                                <td>{{ $memo->branch->name }}</td>
                                <td class="text-end">{{ $memo->total_amount }}</td>
                                <td>{{ ucfirst($memo->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-gray-600">No debit memos yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $memos->links() }}
        </div>
    </div>

</x-default-layout>
