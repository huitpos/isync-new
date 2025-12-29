<x-default-layout>

    @section('title')
        Department Sales Report
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
                <table class="table table-striped table-row-bordered gy-5">
                    <thead>
                        <tr class="fw-semibold fs-6 text-gray-800">
                            <th>Department</th>
                            <th>Quantity Sold</th>
                            <th>Gross Sales</th>
                            <th>Discount Sales</th>
                            <th>Net Sales</th>
                            <th>Department Percentage Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($departmentData as $dept)
                            <tr>
                                <td>{{ $dept->department }}</td>
                                <td>{{ number_format($dept->quantity_sold, 0) }}</td>
                                <td>{{ number_format($dept->gross_sales, 2) }}</td>
                                <td>{{ number_format($dept->discount_sales, 2) }}</td>
                                <td>{{ number_format($dept->net_sales, 2) }}</td>
                                <td>{{ $dept->percentage }}%</td>
                            </tr>
                        @endforeach
                        <tr class="fw-bold">
                            <td>TOTAL</td>
                            <td>{{ number_format($departmentData->sum('quantity_sold'), 0) }}</td>
                            <td>{{ number_format($totalGrossSales, 2) }}</td>
                            <td>{{ number_format($totalDiscountSales, 2) }}</td>
                            <td>{{ number_format($totalNetSales, 2) }}</td>
                            <td>100%</td>
                        </tr>
                    </tbody>
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
