<x-default-layout>

    @section('title')
        BIR SALES SUMMARY REPORT
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

                        @error('branch')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
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
                <table id="kt_datatable_zero_configuration" class="table table-striped table-row-bordered gy-5">
                    <thead>
                        <tr class="fw-semibold fs-6 text-muted">
                            <th>Date</th>
                            <th>Beginning SI/OR No.</th>
                            <th>Ending SI/OR No.</th>
                            <th>Grand Accum. Sales Ending Balance</th>
                            <th>Grand Accum. Beg.Balance</th>
                            <th>Sales Issued w/ Manual SI/OR (per RR 16-2018)</th>
                            <th>VATable Sales</th>
                            <th>VATable Amount</th>
                            <th>VAT-Exempt Sales</th>
                            <th>Zero-Rated Sales</th>
                            <th>Deductions - SC</th>
                            <th>Deductions - PWD</th>
                            <th>Deductions - NAAC</th>
                            <th>Deductions - Solo Parent</th>
                            <th>Deductions - Others</th>
                            <th>Returns</th>
                            <th>Voids</th>
                            <th>Total Deductions</th>
                            <th>Adjustment on VAT - SC</th>
                            <th>Adjustment on VAT - PWD</th>
                            <th>Adjustment on VAT - NAAC</th>
                            <th>Adjustment on VAT - Solo Parent</th>
                            <th>Adjustment on VAT - Others</th>
                            <th>Total VAT Adjustment</th>
                            <th>Vat Payable</th>
                            <th>Net Sales</th>
                            <th>Sales Overrun / Overflow</th>
                            <th>Total Income</th>
                            <th>Reset Counter</th>
                            <th>Z -Counter</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($endOfDays as $endOfDay)
                            @php
                                $cutOffs = App\Models\CutOff::where([
                                        'end_of_day_id' => $endOfDay->end_of_day_id,
                                        'branch_id' => $branchId,
                                    ])
                                    ->get();

                                $cutOffIds = $cutOffs->pluck('cut_off_id')->unique()->toArray();
                            @endphp
                            
                            <tr>
                                <td>{{ $endOfDay->treg }}</td>
                                <td>{{ $endOfDay->beginning_or }}</td>
                                <td>{{ $endOfDay->ending_or }}</td>
                                <td>{{ $endOfDay->ending_amount }}</td>
                                <td>{{ $endOfDay->beginning_amount }}</td>
                                <td>0</td>
                                <td>{{ $endOfDay->vatable_sales }}</td>
                                <td>{{ $endOfDay->vat_amount }}</td>
                                <td>{{ $endOfDay->vat_exempt_sales }}</td>
                                <td>{{ number_format($endOfDay->total_zero_rated_amount, 2) }}</td>
                                <td>
                                    @php
                                        $discounts = App\Models\Discount::where([
                                            'discount_type_id' => 4,
                                            'branch_id' => $branchId,
                                            'is_void' => false
                                        ])
                                        ->whereIn('cut_off_id', $cutOffIds)
                                        ->get();
                                    @endphp

                                    {{ number_format($discounts->sum('discount_amount'), 2) }}
                                </td>
                                <td>
                                    @php
                                        $discounts = App\Models\Discount::where([
                                            'discount_type_id' => 5,
                                            'branch_id' => $branchId,
                                            'is_void' => false
                                        ])
                                        ->whereIn('cut_off_id', $cutOffIds)
                                        ->get();
                                    @endphp

                                    {{ number_format($discounts->sum('discount_amount'), 2) }}
                                </td>
                                <td>
                                    @php
                                        $discounts = App\Models\Discount::where([
                                            'discount_type_id' => 29,
                                            'branch_id' => $branchId,
                                            'is_void' => false
                                        ])
                                        ->whereIn('cut_off_id', $cutOffIds)
                                        ->get();
                                    @endphp

                                    {{ number_format($discounts->sum('discount_amount'), 2) }}
                                </td>
                                <td>
                                    @php
                                        $discounts = App\Models\Discount::where([
                                            'discount_type_id' => 11,
                                            'branch_id' => $branchId,
                                            'is_void' => false
                                        ])
                                        ->whereIn('cut_off_id', $cutOffIds)
                                        ->get();
                                    @endphp

                                    {{ number_format($discounts->sum('discount_amount'), 2) }}
                                </td>
                                <td>
                                    @php
                                        $discounts = App\Models\Discount::where([
                                            'branch_id' => $branchId,
                                            'is_void' => false
                                        ])
                                        ->whereNotIn('discount_type_id', [4, 5, 29, 11])
                                        ->whereIn('cut_off_id', $cutOffIds)
                                        ->get();
                                    @endphp

                                    {{ number_format($discounts->sum('discount_amount'), 2) }}
                                </td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>{{ $endOfDay->net_sales }}</td>
                                <td>{{ $endOfDay->total_short_over }}</td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>{{ $endOfDay->reading_number }}</td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $("#kt_datatable_zero_configuration").DataTable();

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
                    updateURLAndRefresh()
                });
            });
        </script>
    @endpush
</x-default-layout>

