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


    <div id="dashboard-widgets" class="row g-1 g-xl-5 mb-1 mb-xl-5">
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
                            'valueId' => 'today-transaction-count',
                            'text' => '...',
                            'subText' => 'Transaction Count',
                            'class' => 'border-primary border-2',
                            'style' => 'box-shadow: 5px 5px 5px rgba(var(--bs-primary-rgb), var(--bs-border-opacity)) !important;'
                        ])
                    </div>

                    <div class="col-3">
                        @include('partials/widgets/small_card', [
                            'valueId' => 'today-gross-amount',
                            'text' => '...',
                            'subText' => 'Gross sales',
                            'class' => 'border-success border-2 shadow',
                            'style' => 'box-shadow: 5px 5px 5px rgba(var(--bs-success-rgb), var(--bs-border-opacity)) !important;'
                        ])
                    </div>

                    <div class="col-3">
                        @include('partials/widgets/small_card', [
                            'valueId' => 'today-net-amount',
                            'text' => '...',
                            'subText' => 'Net Sales',
                            'class' => 'border-info border-2',
                            'style' => 'box-shadow: 5px 5px 5px rgba(var(--bs-info-rgb), var(--bs-border-opacity)) !important;'
                        ])
                    </div>

                    <div class="col-3">
                        @include('partials/widgets/small_card', [
                            'valueId' => 'today-profit',
                            'text' => '...',
                            'subText' => 'Profit',
                            'class' => 'border-warning border-2',
                            'style' => 'box-shadow: 5px 5px 5px rgba(var(--bs-warning-rgb), var(--bs-border-opacity)) !important;'
                        ])
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="border-0 p-1">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800" id="range-sales-title">{{ $selectedRangeParam }} Sales Summary</span>
                </h3>
            </div>
            <div class="card-body p-1">
                <div class="row g-1 g-xl-5">
                    <div class="col-3">
                        @include('partials/widgets/small_card', [
                            'valueId' => 'range-transaction-count',
                            'text' => '...',
                            'subText' => 'Transaction Count',
                            'class' => 'border-primary border-2',
                            'style' => 'box-shadow: 5px 5px 5px rgba(var(--bs-primary-rgb), var(--bs-border-opacity)) !important;'
                        ])
                    </div>

                    <div class="col-3">
                        @include('partials/widgets/small_card', [
                            'valueId' => 'range-gross-amount',
                            'text' => '...',
                            'subText' => 'Gross sales',
                            'class' => 'border-success border-2',
                            'style' => 'box-shadow: 5px 5px 5px rgba(var(--bs-success-rgb), var(--bs-border-opacity)) !important;'
                        ])
                    </div>

                    <div class="col-3">
                        @include('partials/widgets/small_card', [
                            'valueId' => 'range-net-amount',
                            'text' => '...',
                            'subText' => 'Net Sales',
                            'class' => 'border-info border-2',
                            'style' => 'box-shadow: 5px 5px 5px rgba(var(--bs-info-rgb), var(--bs-border-opacity)) !important;'
                        ])
                    </div>

                    <div class="col-3">
                        @include('partials/widgets/small_card', [
                            'valueId' => 'range-profit',
                            'text' => '...',
                            'subText' => 'Profit',
                            'class' => 'border-warning border-2',
                            'style' => 'box-shadow: 5px 5px 5px rgba(var(--bs-warning-rgb), var(--bs-border-opacity)) !important;'
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

        <div class="col-12 mt-10  ">
            <div id="kt_docs_google_chart_pie" style="height: 300px;"></div>
        </div>

        <div class="col-12 mt-10">
            <div class="table-responsive">
                <h2 id="sales_breakdown_title"></h2>
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
        const dashboardDataUrl = @json(route('company.dashboard.data', ['companySlug' => $company->slug]));
        const departmentProductsUrl = @json(route('company.dashboard.department-products', ['company' => $company->id]));

        let chartLibReady = false;
        let dashboardPayload = null;
        let dashboardRequest = null;
        let departmentData = null;
        let departmentChart = null;
        let salesTable = null;

        const summaryIds = [
            'today-transaction-count',
            'today-gross-amount',
            'today-net-amount',
            'today-profit',
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

            ['date_range', 'branch_id', 'selectedRange', 'startDate', 'endDate'].forEach(function (key) {
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
                return null;
            }

            const table = new google.visualization.DataTable();
            table.addColumn('string', 'Item');
            table.addColumn('number', 'Value');
            table.addRows(rows);

            const chart = new google.visualization.PieChart(document.getElementById(elementId));
            chart.draw(table, {
                title: title,
                pieHole: 0,
                pieSliceText: 'percentage',
                sliceVisibilityThreshold: 0,
                is3D: true,
                chartArea: {width: '100%', height: '85%'}
            });

            return {chart: chart, table: table};
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

            const department = drawPie('kt_docs_google_chart_pie', 'Department Sales', dashboardPayload.departmentSales);
            departmentChart = department ? department.chart : null;
            departmentData = department ? department.table : null;

            if (departmentChart) {
                google.visualization.events.addListener(departmentChart, 'select', function () {
                    const selection = departmentChart.getSelection();
                    if (!selection.length || !departmentData) {
                        return;
                    }

                    const item = departmentData.getValue(selection[0].row, 0);
                    const params = dashboardParams();

                    salesTable.clear().draw();
                    $('#kt_datatable_zero_configuration').addClass('opacity-50');

                    $.ajax({
                        url: departmentProductsUrl,
                        type: 'GET',
                        cache: false,
                        data: {
                            department: item,
                            branch_id: params.branch_id,
                            selectedRange: params.selectedRange,
                            startDate: params.startDate,
                            endDate: params.endDate
                        },
                        success: function (response) {
                            salesTable.clear();
                            if (response.data && response.data.length > 0) {
                                salesTable.rows.add(response.data);
                            }
                            salesTable.draw();
                            $('#kt_datatable_zero_configuration').removeClass('opacity-50');
                            $('#sales_breakdown_title').text('Sales Breakdown for ' + item);
                        },
                        error: function () {
                            $('#kt_datatable_zero_configuration').removeClass('opacity-50');
                            if (typeof toastr !== 'undefined') {
                                toastr.error('Failed to load department products.');
                            }
                        }
                    });
                });
            }

            drawPie('kt_docs_google_chart_pie2', 'Top Sold Items', dashboardPayload.itemSales);
            drawPie('kt_docs_google_chart_pie3', 'Top Payment Type', dashboardPayload.paymentTypeSales);
        }

        function applyDashboard(payload) {
            setText('today-transaction-count', payload.today.transactionCount);
            setText('today-gross-amount', payload.today.grossAmount);
            setText('today-net-amount', payload.today.netAmount);
            setText('today-profit', payload.today.profit);
            setText('range-transaction-count', payload.range.transactionCount);
            setText('range-gross-amount', payload.range.grossAmount);
            setText('range-net-amount', payload.range.netAmount);
            setText('range-profit', payload.range.profit);
            $('#range-sales-title').text((payload.selectedRange || 'Year to Date') + ' Sales Summary');

            dashboardPayload = payload;
            renderCharts();
        }

        function loadDashboard() {
            if (dashboardRequest) {
                dashboardRequest.abort();
            }

            const params = dashboardParams();
            setLoading(true);
            salesTable.clear().draw();
            $('#sales_breakdown_title').text('');

            dashboardRequest = $.ajax({
                url: dashboardDataUrl,
                type: 'GET',
                cache: false,
                data: params,
                success: function (response) {
                    applyDashboard(response);
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
            salesTable = $('#kt_datatable_zero_configuration').DataTable({
                order: []
            });

            $('#search-btn').on('click', function () {
                loadDashboard();
            });

            loadDashboard();
        });
    </script>
    @endpush
</x-default-layout>
