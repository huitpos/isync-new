<x-default-layout>
    @php
        $permissions = request()->attributes->get('permissionNames');
    @endphp

    @section('title')
        Dashboard
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.dashboard', $company) }}
    @endsection

    <form method="POST" novalidate>
        @csrf
        <div class="row mb-5">
            <div class="col-md-2">
                <label class="form-label">Branch</label>

                <select id="branch_id" name="branch_id" class="form-select @error('branch') is-invalid @enderror" required>
                    <option value="">All</option>
                    @foreach ($activebranches as $branch)
                        <option value="{{ $branch->id }}" {{ $branch->id == $branchId ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
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

    <div class="row g-1 g-xl-5 mb-1 mb-xl-5">
        @if (in_array('Main Dashboard/Transaction Count', $permissions))
        <div class="col-4">
            @include('partials/widgets/small_card', [
                'text' => $transactionCount,
                'subText' => 'Transaction Count',
            ])
        </div>
        @endif

        @if (in_array('Main Dashboard/Total Net Amount', $permissions))
        <div class="col-4">
            @include('partials/widgets/small_card', [
                'text' => number_format($netAmount, 2),
                'subText' => 'Total Net Amount',
            ])
        </div>
        @endif

        @if (in_array('Main Dashboard/Total Cost Amount', $permissions))
        <div class="col-4">
            @include('partials/widgets/small_card', [
                'text' => number_format($grossAmount, 2),
                'subText' => 'Total Gross Amount',
            ])
        </div>
        @endif

        <div class="col-12 border ">
            <div id="kt_docs_google_chart_column" style="height: 300px;"></div>
        </div>

        <div class="col-12 mt-10 border ">
            <div id="kt_docs_google_chart_line" style="height: 300px;"></div>
        </div>

        <div class="col-4 mt-10 border ">
            <div id="kt_docs_google_chart_pie" style="height: 300px;"></div>
        </div>

        <div class="col-4 mt-10">
            <div id="kt_docs_google_chart_pie2" style="height: 300px;"></div>
        </div>

        <div class="col-4 mt-10">
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

            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Item');
            data.addColumn('number', 'Value');
            data.addRows(@json($departmentSales));

            var options = {
                title: 'Department Sales',
                pieHole: 0,
                pieSliceText: 'percentage',
                sliceVisibilityThreshold : 0
            };

            var chart = new google.visualization.PieChart(document.getElementById('kt_docs_google_chart_pie'));
            chart.draw(data, options);

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
