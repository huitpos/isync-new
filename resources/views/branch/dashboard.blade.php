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
        <input type="hidden" name="branch_id" id="branch_id" value="{{ $branchId }}" />
        <div class="row mb-5">
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

    <div id="dashboard-widgets" class="row g-1 g-xl-5 mb-1 mb-xl-5">
        <div class="col-3">
            @include('partials/widgets/small_card', [
                'valueId' => 'range-transaction-count',
                'text' => '...',
                'subText' => 'Transaction Count',
            ])
        </div>

        <div class="col-3">
            @include('partials/widgets/small_card', [
                'valueId' => 'range-gross-amount',
                'text' => '...',
                'subText' => 'Gross sales',
            ])
        </div>

        <div class="col-3">
            @include('partials/widgets/small_card', [
                'valueId' => 'range-net-amount',
                'text' => '...',
                'subText' => 'Net Sales',
            ])
        </div>

        <div class="col-3">
            @include('partials/widgets/small_card', [
                'valueId' => 'range-profit',
                'text' => '...',
                'subText' => 'Profit',
            ])
        </div>

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
        const dashboardDataUrl = @json(route('branch.dashboard.data', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]));

        let chartLibReady = false;
        let dashboardPayload = null;
        let dashboardRequest = null;

        const summaryIds = [
            'range-transaction-count',
            'range-gross-amount',
            'range-net-amount',
            'range-profit'
        ];

        google.charts.load('current', {
            packages: ['corechart']
        });

        google.charts.setOnLoadCallback(function () {
            chartLibReady = true;
            renderCharts();
        });

        function setText(id, value) {
            const el = document.getElementById(id);
            if (el) {
                el.textContent = value;
            }
        }

        function dashboardParams() {
            const $date = $('#date_range');

            return {
                branch_id: $('#branch_id').val(),
                date_range: $date.val(),
                selectedRange: $date.attr('data-selected-range'),
                startDate: $date.attr('data-start-date'),
                endDate: $date.attr('data-end-date')
            };
        }

        function syncUrl(params) {
            const url = new URL(window.location.href);

            ['date_range', 'selectedRange', 'startDate', 'endDate'].forEach(function (key) {
                if (params[key]) {
                    url.searchParams.set(key, params[key]);
                } else {
                    url.searchParams.delete(key);
                }
            });

            history.replaceState({}, '', url.toString());
        }

        function setLoading(isLoading) {
            $('#search-btn').prop('disabled', isLoading);
            $('#dashboard-widgets').toggleClass('opacity-50', isLoading);

            if (isLoading) {
                summaryIds.forEach(function (id) {
                    setText(id, '...');
                });
            }
        }

        function emptyChart(elementId, message) {
            const el = document.getElementById(elementId);
            if (el) {
                el.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted">' + message + '</div>';
            }
        }

        function drawPie(elementId, title, rows) {
            if (!rows || !rows.length) {
                emptyChart(elementId, 'No data');
                return;
            }

            const table = new google.visualization.DataTable();
            table.addColumn('string', 'Item');
            table.addColumn('number', 'Value');
            table.addRows(rows);

            new google.visualization.PieChart(document.getElementById(elementId)).draw(table, {
                title: title,
                pieHole: 0,
                pieSliceText: 'percentage',
                sliceVisibilityThreshold: 0
            });
        }

        function renderCharts() {
            if (!chartLibReady || !dashboardPayload) {
                return;
            }

            const salesOptions = {
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

            if (!dashboardPayload.salesData || !dashboardPayload.salesData.length) {
                emptyChart('kt_docs_google_chart_column', 'No sales data');
                emptyChart('kt_docs_google_chart_line', 'No sales data');
            } else {
                const salesTableData = new google.visualization.DataTable();
                salesTableData.addColumn('string', 'Month');
                (dashboardPayload.branches || []).forEach(function (branch) {
                    salesTableData.addColumn('number', branch);
                });
                salesTableData.addRows(dashboardPayload.salesData);

                new google.visualization.ColumnChart(document.getElementById('kt_docs_google_chart_column')).draw(salesTableData, salesOptions);
                new google.visualization.LineChart(document.getElementById('kt_docs_google_chart_line')).draw(salesTableData, salesOptions);
            }

            drawPie('kt_docs_google_chart_pie', 'Department Sales', dashboardPayload.departmentSales);
            drawPie('kt_docs_google_chart_pie2', 'Top Sold Items', dashboardPayload.itemSales);
            drawPie('kt_docs_google_chart_pie3', 'Top Payment Type', dashboardPayload.paymentTypeSales);
        }

        function loadDashboard() {
            if (dashboardRequest) {
                dashboardRequest.abort();
            }

            const params = dashboardParams();
            setLoading(true);

            dashboardRequest = $.ajax({
                url: dashboardDataUrl,
                type: 'GET',
                cache: false,
                data: params,
                success: function (response) {
                    setText('range-transaction-count', response.range.transactionCount);
                    setText('range-gross-amount', response.range.grossAmount);
                    setText('range-net-amount', response.range.netAmount);
                    setText('range-profit', response.range.profit);

                    dashboardPayload = response;
                    renderCharts();
                    syncUrl(params);
                    setLoading(false);
                },
                error: function (xhr) {
                    if (xhr.statusText === 'abort') {
                        return;
                    }

                    setLoading(false);
                    summaryIds.forEach(function (id) {
                        setText(id, '-');
                    });

                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to load dashboard data.');
                    }
                }
            });
        }

        $(function () {
            $('#search-btn').on('click', function () {
                loadDashboard();
            });

            loadDashboard();
        });
    </script>
    @endpush
</x-default-layout>
