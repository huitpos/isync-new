<x-default-layout>
    @php
        $permissions = request()->attributes->get('permissionNames');
    @endphp
 
    <div class="row g-1 g-xl-5 mb-1 mb-xl-5">
        <div class="col-3">
            <h1>{{ $company->trade_name }}</h1>
        </div>

        <div class="col-9 d-flex justify-content-end">
            <form method="POST" novalidate>
                @csrf
                <div class="row mb-5">
                    <div class="col-md-4">
                        <label class="form-label">Branch</label>

                        <select id="branch_id" name="branch_id" class="form-select @error('branch') is-invalid @enderror" required>
                            <option value="">All</option>
                            @foreach ($activebranches as $branch)
                                <option value="{{ $branch->id }}" {{ $branch->id == $branchId ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
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

                    <div class="col-md-2">
                        <button type="button" id="search-btn" class="btn btn-primary mt-8">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="row g-1 g-xl-5 mb-1 mb-xl-5">
        <div class="card">
            <div class="border-0 p-1">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Today Sales Summary</span>
                </h3>
            </div>
            <div class="card-body p-1">
                <div class="row g-1 g-xl-5">
                    <div class="col-3">
                        @include('partials/widgets/small_card', [
                            'text' => $todayTransactionCount,
                            'subText' => 'Transaction Count',
                        ])
                    </div>

                    <div class="col-3">
                        @include('partials/widgets/small_card', [
                            'text' => number_format($todayGrossAmount, 2),
                            'subText' => 'Gross sales',
                        ])
                    </div>

                    <div class="col-3">
                        @include('partials/widgets/small_card', [
                            'text' => number_format($todayNetAmount, 2),
                            'subText' => 'Net Sales',
                        ])
                    </div>

                    <div class="col-3">
                        @include('partials/widgets/small_card', [
                            'text' => number_format($todayGrossAmount - $todayCostAmount, 2),
                            'subText' => 'Profit',
                        ])
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="border-0 p-1">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">{{ $selectedRangeParam }} Sales Summary</span>
                </h3>
            </div>
            <div class="card-body p-1">
                <div class="row g-1 g-xl-5">
                    <div class="col-3">
                        @include('partials/widgets/small_card', [
                            'text' => $transactionCount,
                            'subText' => 'Transaction Count',
                        ])
                    </div>

                    <div class="col-3">
                        @include('partials/widgets/small_card', [
                            'text' => number_format($grossAmount, 2),
                            'subText' => 'Gross sales',
                        ])
                    </div>

                    <div class="col-3">
                        @include('partials/widgets/small_card', [
                            'text' => number_format($netAmount, 2),
                            'subText' => 'Net Sales',
                        ])
                    </div>

                    <div class="col-3">
                        @include('partials/widgets/small_card', [
                            'text' => number_format($grossAmount - $costAmount, 2),
                            'subText' => 'Profit',
                        ])
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12">
            <div id="kt_docs_google_chart_column" style="height: 300px;"></div>
        </div>

        <div class="col-12 mt-10  ">
            <div id="kt_docs_google_chart_line" style="height: 300px;"></div>
        </div>

        <div class="col-6 mt-10  ">
            <div id="kt_docs_google_chart_pie" style="height: 300px;"></div>
        </div>

        <div class="col-6 mt-10">
            <div class="table-responsive">
                <table id="kt_datatable_zero_configuration" class="table table-striped table-row-bordered gy-5">
                    <thead>
                        <tr class="fw-semibold fs-6 text-muted">
                            <td>Product</td>
                            <td>Qty</td>
                            <td>Net Sales</td>
                            <td>%</td>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-6 mt-10">
            <div id="kt_docs_google_chart_pie2" style="height: 300px;"></div>
        </div>

        <div class="col-6 mt-10">
            <div id="kt_docs_google_chart_pie3" style="height: 300px;"></div>
        </div>
    </div>

    @push('scripts')
    <script type="text/javascript">
        google.charts.load('current', {
            packages: ['corechart']
        });

        google.charts.setOnLoadCallback(function () {
            var data = new google.visualization.DataTable();
            
            // Add the first column for Month
            data.addColumn('string', 'Month');
            
            // Dynamically add columns for each branch
            @foreach($branches as $branch)
                data.addColumn('number', '{{ $branch }}');
            @endforeach

            // Insert dynamic data from the controller
            data.addRows(@json($salesData));

            var options = {
                title: 'Sales Per Branch Per Month',
                vAxis: {
                    title: 'Sales Amount',
                    minValue: 0
                },
                legend: {
                    position: 'top'
                },
                bars: 'vertical',
                isStacked: false,
                logScale: true,
                pointSize: 5,
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('kt_docs_google_chart_column'));
            chart.draw(data, options);

            var line = new google.visualization.LineChart(document.getElementById('kt_docs_google_chart_line'));
            line.draw(data, options);

            var departmentData = new google.visualization.DataTable();
            departmentData.addColumn('string', 'Item');
            departmentData.addColumn('number', 'Value');
            departmentData.addRows(@json($departmentSales));

            var departmentOptions = {
                title: 'Department Sales',
                pieHole: 0,
                pieSliceText: 'percentage',
                sliceVisibilityThreshold : 0
            };

            var departmentChart = new google.visualization.PieChart(document.getElementById('kt_docs_google_chart_pie'));
            departmentChart.draw(departmentData, departmentOptions);

            google.visualization.events.addListener(departmentChart, 'select', function () {
                var selection = departmentChart.getSelection();
                if (selection.length > 0) {
                    var row = selection[0].row;
                    var item = departmentData.getValue(row, 0);
                    
                    // Get current filters
                    const branchValue = document.getElementById('branch_id').value;
                    const selectedRange = $("#date_range").attr("data-selected-range");
                    const startDate = $("#date_range").attr("data-start-date");
                    const endDate = $("#date_range").attr("data-end-date");
                    
                    // Show loading indicator
                    const datatable = $("#kt_datatable_zero_configuration").DataTable();
                    datatable.clear().draw();
                    $("#kt_datatable_zero_configuration").addClass("opacity-50");
                    
                    // Make AJAX request
                    $.ajax({
                        url: "{{ route('company.dashboard.department-products', ['company' => $company->id]) }}",
                        type: "GET",
                        data: {
                            department: item,
                            branch_id: branchValue,
                            selectedRange: selectedRange,
                            startDate: startDate,
                            endDate: endDate
                        },
                        success: function(response) {
                            datatable.clear();
                            if (response.data && response.data.length > 0) {
                                datatable.rows.add(response.data);
                            }
                            datatable.draw();
                            $("#kt_datatable_zero_configuration").removeClass("opacity-50");
                        },
                        error: function(xhr) {
                            console.error("Error loading department products:", xhr);
                            $("#kt_datatable_zero_configuration").removeClass("opacity-50");
                        }
                    });
                }
            });

            $("#kt_datatable_zero_configuration").DataTable({
                order: []  // No default sorting
            });

            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Item');
            data.addColumn('number', 'Value');
            data.addRows(@json($itemSales));

            var options = {
                title: 'Top Sold Items',
                pieHole: 0,
                pieSliceText: 'percentage',
                sliceVisibilityThreshold : 0
            };

            var chart = new google.visualization.PieChart(document.getElementById('kt_docs_google_chart_pie2'));
            chart.draw(data, options);

            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Item');
            data.addColumn('number', 'Value');
            data.addRows(@json($paymentTypeSales));

            var options = {
                title: 'Top Payment Type',
                pieHole: 0,
                pieSliceText: 'percentage',
                sliceVisibilityThreshold : 0
            };

            var chart = new google.visualization.PieChart(document.getElementById('kt_docs_google_chart_pie3'));
            chart.draw(data, options);
        });

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

        $("#search-btn").on("click", ({date, oldDate}) => {
            updateURLAndRefresh()
        });
    </script>
    @endpush
</x-default-layout>
