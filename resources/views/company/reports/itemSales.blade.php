<x-default-layout>

    @section('title')
        Item Sales Report
    @endsection

    <div class="card" data-dt-card>
        <div class="card-body py-4">
            <form class="mt-3" method="POST" novalidate>
                @csrf

                <div class="row mb-5">
                    <div class="col-md-4">
                        <label class="form-label">Branch</label>

                        <select id="branch_id" name="branch_id" class="form-select @error('branch') is-invalid @enderror" required data-dt-filter="branch_id">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $branch->id == $branchId ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input id="date_range" 
                            data-selected-range="{{ $selectedRangeParam }}" 
                            data-kt-daterangepicker="true" 
                            data-start-date="{{ $startDateParam }}" 
                            data-end-date="{{ $endDateParam }}" 
                            name="date_range" 
                            type="text" 
                            class="form-control"
                            data-kt-daterangepicker-opens="right"
                         data-dt-filter="date_range">
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary mt-8">Export</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-row-bordered gy-5">
                    <thead>
                        <tr class="fw-semibold fs-6 text-muted">
                            <th></th>
                            <th>Qty Sold</th>
                            <th>Item Cost</th>
                            <th>Selling Price</th>
                            <th>Gross Amount</th>
                            <th>Discounts</th>
                            <th>Net Sales</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalNetSales = $itemTotals->net ?? 0;
                        @endphp
                        <tr>
                            <td>Total</td>
                            <td>{{ number_format($itemTotals->qty ?? 0, 2) }}</td>
                            <td>{{ number_format($itemTotals->cost ?? 0, 2) }}</td>
                            <td>{{ number_format($itemTotals->srp ?? 0, 2) }}</td>
                            <td>{{ number_format($itemTotals->gross ?? 0, 2) }}</td>
                            <td>{{ number_format($itemTotals->discount ?? 0, 2) }}</td>
                            <td>{{ number_format($totalNetSales, 2) }}</td>
                            <td>100</td>
                        </tr>
                    </tbody>
                </table>

                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts() }}
    @endpush
</x-default-layout>