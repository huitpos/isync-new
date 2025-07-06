<x-default-layout>

    @section('title')
        Hourly Sales Summary
    @endsection

    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title">Hourly Sales Summary</h3>
        </div>
        <div class="card-body py-4">
            <div class="d-flex flex-column">
                <div class="fs-4 fw-bold text-gray-800">{{ $branchName ?? 'All Branches' }}</div>
                <div class="fs-6 text-gray-600">Date Range: {{ $startDateParam }} to {{ $endDateParam }}</div>
                <div class="fs-7 text-gray-600">Generated: {{ now()->format('Y-m-d H:i:s') }}</div>
                <div class="fs-7 text-gray-600">Created by: {{ auth()->user()->name ?? 'System' }}</div>
            </div>
        </div>
    </div>

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
                            <th>Date</th>
                            <th>Days</th>
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
                            @foreach ($timeSlots as $slot)
                                <th>{{ $slot }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($days as $day)
                            <tr>
                                <td>{{ $day['formatted_date'] }}</td>
                                <td>{{ $day['day_name'] }}</td>
                                <td>{{ isset($daySummary[$day['date']]['transactions']) ? number_format($daySummary[$day['date']]['transactions']) : '0' }}</td>
                                <td>{{ isset($daySummary[$day['date']]['gross_sales']) ? number_format($daySummary[$day['date']]['gross_sales'], 2) : '0.00' }}</td>
                                <td>{{ isset($daySummary[$day['date']]['net_sales']) ? number_format($daySummary[$day['date']]['net_sales'], 2) : '0.00' }}</td>
                                <td>{{ isset($daySummary[$day['date']]['discounts']) ? number_format($daySummary[$day['date']]['discounts'], 2) : '0.00' }}</td>
                                <td>{{ isset($daySummary[$day['date']]['vat_sales']) ? number_format($daySummary[$day['date']]['vat_sales'], 2) : '0.00' }}</td>
                                <td>{{ isset($daySummary[$day['date']]['vat_exempt_sales']) ? number_format($daySummary[$day['date']]['vat_exempt_sales'], 2) : '0.00' }}</td>
                                <td>{{ isset($daySummary[$day['date']]['vat_amount']) ? number_format($daySummary[$day['date']]['vat_amount'], 2) : '0.00' }}</td>
                                <td>{{ isset($daySummary[$day['date']]['cash_sales']) ? number_format($daySummary[$day['date']]['cash_sales'], 2) : '0.00' }}</td>
                                <td>{{ isset($daySummary[$day['date']]['card_sales']) ? number_format($daySummary[$day['date']]['card_sales'], 2) : '0.00' }}</td>
                                <td>{{ isset($daySummary[$day['date']]['mobile_sales']) ? number_format($daySummary[$day['date']]['mobile_sales'], 2) : '0.00' }}</td>
                                <td>{{ isset($daySummary[$day['date']]['ar_sales']) ? number_format($daySummary[$day['date']]['ar_sales'], 2) : '0.00' }}</td>
                                <td>{{ isset($daySummary[$day['date']]['online_sales']) ? number_format($daySummary[$day['date']]['online_sales'], 2) : '0.00' }}</td>
                                <td>{{ isset($daySummary[$day['date']]['unit_cost']) ? number_format($daySummary[$day['date']]['unit_cost'], 2) : '0.00' }}</td>
                                <td>{{ isset($daySummary[$day['date']]['service_charge']) ? number_format($daySummary[$day['date']]['service_charge'], 2) : '0.00' }}</td>
                                <td>{{ isset($daySummary[$day['date']]['gross_profit']) ? number_format($daySummary[$day['date']]['gross_profit'], 2) : '0.00' }}</td>
                                <td>{{ isset($daySummary[$day['date']]['gross_profit_percentage']) ? number_format($daySummary[$day['date']]['gross_profit_percentage'], 2) . '%' : '0.00%' }}</td>
                                @for ($hour = 0; $hour < 24; $hour++)
                                    <td>
                                        @if (isset($salesByHour[$day['date']][$hour]) && $salesByHour[$day['date']][$hour] > 0)
                                            {{ number_format($salesByHour[$day['date']][$hour], 2) }}
                                        @endif
                                    </td>
                                @endfor
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
                            @for ($hour = 0; $hour < 24; $hour++)
                                <td>
                                    @if (isset($hourlyTotals[$hour]) && $hourlyTotals[$hour] > 0)
                                        {{ number_format($hourlyTotals[$hour], 2) }}
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="mt-5">
                <h4 class="mb-3">Formulas:</h4>
                <div class="mb-2">Gross Sales = VATable Sales + VAT Exempt Sales + Zero Rated Sales + VAT + SC/PWD Discount</div>
                <div class="mb-2">Net Sales = Gross Sales - VAT - SC/PWD Discount</div>
                <div class="mb-2">Gross Profit = Net Sales - Unit Cost</div>
                <div class="mb-2">Gross Profit % = (Gross Profit / Net Sales) * 100</div>
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
