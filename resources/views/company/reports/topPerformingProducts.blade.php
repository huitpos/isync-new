<x-default-layout>

    @section('title')
        Top Performing Products Report
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" method="POST" novalidate>
                @csrf

                <div class="row mb-5">
                    <div class="col-md-3">
                        <label class="form-label">Branch</label>

                        <select id="branch_id" name="branch_id" class="form-select @error('branch') is-invalid @enderror" required>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $branch->id == $branchId ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
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

                    <div class="col-md-3">
                        <label class="form-label">Products</label>
                        <select id="limit" name="limit" class="form-select">
                            <option value="100" {{ $limit !== 'all' ? 'selected' : '' }}>Top 100</option>
                            <option value="all" {{ $limit === 'all' ? 'selected' : '' }}>Show all</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary mt-8">Export</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">

                <table id="kt_datatable_zero_configuration" class="table table-striped table-row-bordered gy-5 table-bordered">
                    <thead>
                        <tr class="fw-semibold fs-6 text-gray-800">
                            <th>Description</th>
                            <th>SKU</th>
                            <th>Department</th>
                            <th>Category</th>
                            <th>Sub Category</th>
                            <th>Quantity Sold</th>
                            <th>AR Unpaid Quantity</th>
                            <th>Total Unit Cost</th>
                            <th>Discount Sales</th>
                            <th>Regular Sales</th>
                            <th>Sales Percentage</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', (event) => {
                const dateRange = document.getElementById('date_range');
                const branchId = document.getElementById('branch_id');
                const limit = document.getElementById('limit');

                const table = $('#kt_datatable_zero_configuration').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: window.location.pathname,
                        data: function (d) {
                            d.branch_id = branchId.value;
                            d.date_range = dateRange.value;
                            d.limit = limit.value;
                        }
                    },
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100],
                    order: [[9, 'desc']],
                    columns: [
                        { data: 'description' },
                        { data: 'sku' },
                        { data: 'department' },
                        { data: 'category' },
                        { data: 'sub_category' },
                        { data: 'quantity_sold', className: 'text-end' },
                        { data: 'ar_unpaid_quantity', className: 'text-end' },
                        { data: 'total_unit_cost', className: 'text-end' },
                        { data: 'discount_sales', className: 'text-end' },
                        { data: 'regular_sales', className: 'text-end' },
                        { data: 'sales_percentage', className: 'text-end' }
                    ]
                });

                function syncUrlAndReload() {
                    const url = new URL(window.location.href);
                    const selectedRange = $('#date_range').attr('data-selected-range');
                    const startDate = $('#date_range').attr('data-start-date');
                    const endDate = $('#date_range').attr('data-end-date');

                    if (dateRange.value) {
                        url.searchParams.set('date_range', dateRange.value);
                    } else {
                        url.searchParams.delete('date_range');
                    }

                    if (branchId.value) {
                        url.searchParams.set('branch_id', branchId.value);
                    } else {
                        url.searchParams.delete('branch_id');
                    }

                    url.searchParams.set('limit', limit.value);

                    if (selectedRange) {
                        url.searchParams.set('selectedRange', selectedRange);
                    }
                    if (startDate) {
                        url.searchParams.set('startDate', startDate);
                    }
                    if (endDate) {
                        url.searchParams.set('endDate', endDate);
                    }

                    history.replaceState({}, '', url);
                    table.ajax.reload();
                }

                dateRange.addEventListener('change', syncUrlAndReload);
                branchId.addEventListener('change', syncUrlAndReload);
                limit.addEventListener('change', syncUrlAndReload);

                $('#date_range').on('change.datetimepicker', function () {
                    syncUrlAndReload();
                });
            });
        </script>
    @endpush

</x-default-layout>
