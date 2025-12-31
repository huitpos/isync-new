<x-default-layout>

    @section('title')
        Monthly Sales Summary
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" method="POST" novalidate>
                @csrf

                <div class="row mb-5">
                    <div class="col-md-4">
                        <label class="form-label">Branch</label>

                        <select id="branch_id" name="branch_id" class="form-select @error('branch') is-invalid @enderror" required>
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
                        />
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary mt-8">Export</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-row-bordered gy-5 table-bordered">
                    <thead>
                        <tr class="fw-bold fs-6 text-gray-800 border-bottom border-gray-200">
                            <th>Year</th>
                            <th>Month</th>
                            <th>No. of Transactions</th>
                            <th>Gross Sales</th>
                            <th>Net Sales</th>
                            <th>Discounts Amount</th>
                            <th>VAT Sales</th>
                            <th>VAT Exempts Sales</th>
                            <th>VAT Amount</th>
                            <th>Cash Sales</th>
                            <th>Card Sales</th>
                            <th>Mobile Sales</th>
                            <th>AR Sales</th>
                            <th>Online Sales</th>
                            <th>Unit Cost</th>
                            <th>Service Charge</th>
                            <th>Gross Profit</th>
                            <th>Gross Profit %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($monthlySales as $sales)
                            <tr>
                                <td>{{ $sales['year'] }}</td>
                                <td>{{ $sales['month_name'] }}</td>
                                <td>{{ number_format($sales['transactions']) }}</td>
                                <td>{{ number_format($sales['gross_sales'], 2) }}</td>
                                <td>{{ number_format($sales['net_sales'], 2) }}</td>
                                <td>{{ number_format($sales['discounts'], 2) }}</td>
                                <td>{{ number_format($sales['vat_sales'], 2) }}</td>
                                <td>{{ number_format($sales['vat_exempt_sales'], 2) }}</td>
                                <td>{{ number_format($sales['vat_amount'], 2) }}</td>
                                <td>{{ number_format($sales['cash_sales'], 2) }}</td>
                                <td>{{ number_format($sales['card_sales'], 2) }}</td>
                                <td>{{ number_format($sales['mobile_sales'], 2) }}</td>
                                <td>{{ number_format($sales['ar_sales'], 2) }}</td>
                                <td>{{ number_format($sales['online_sales'], 2) }}</td>
                                <td>{{ number_format($sales['unit_cost'], 2) }}</td>
                                <td>{{ number_format($sales['service_charge'], 2) }}</td>
                                <td>{{ number_format($sales['gross_profit'], 2) }}</td>
                                <td>{{ number_format($sales['gross_profit_percentage'], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold fs-6 text-gray-800">
                            <td colspan="2">Total</td>
                            <td>{{ number_format($totals['transactions'] ?? 0) }}</td>
                            <td>{{ number_format($totals['gross_sales'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['net_sales'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['discounts'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['vat_sales'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['vat_exempt_sales'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['vat_amount'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['cash_sales'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['card_sales'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['mobile_sales'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['ar_sales'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['online_sales'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['unit_cost'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['service_charge'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['gross_profit'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['gross_profit_percentage'] ?? 0, 2) }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', (event) => {
                const dateRange = document.getElementById('date_range');
                const branchId = document.getElementById('branch_id');

                function updateURLAndRefresh() {
                    const dateValue = dateRange.value;
                    const branchValue = branchId.value;

                    const selectedRange = $("#date_range").attr("data-selected-range");
                    const startDate = $("#date_range").attr("data-start-date");
                    const endDate = $("#date_range").attr("data-end-date");

                    const url = new URL(window.location.href);
                    if (dateValue) {
                        url.searchParams.set('date_range', dateValue);
                    } else {
                        url.searchParams.delete('date_range');
                    }
                    if (branchValue) {
                        url.searchParams.set('branch_id', branchValue);
                    } else {
                        url.searchParams.delete('branch_id');
                    }

                    //use selectedRange, startDate, endDate in searchParams
                    url.searchParams.set('selectedRange', selectedRange);
                    url.searchParams.set('startDate', startDate);
                    url.searchParams.set('endDate', endDate);   

                    window.location.href = url.toString();
                }

                dateRange.addEventListener('change', updateURLAndRefresh);
                branchId.addEventListener('change', updateURLAndRefresh);

                $("#date_range").on("change.datetimepicker", ({date, oldDate}) => {
                    updateURLAndRefresh();
                });
            });
        </script>
    @endpush

</x-default-layout>
