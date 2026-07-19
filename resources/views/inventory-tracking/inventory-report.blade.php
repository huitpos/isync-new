<x-default-layout>

    @section('title')
        Inventory Report
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('inventory-tracking.report', $company) }}
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2>Inventory Report</h2>
            </div>

            <div class="card-toolbar">
                <a href="{{ route('inventory-tracking.index') }}" class="btn btn-light">
                    {!! getIcon('arrow-left', 'fs-2', '', 'i') !!}
                    Back to Tracking
                </a>
            </div>
        </div>

        <div class="card-body py-4">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label" for="view">Report View</label>
                    <select id="view" class="form-select form-select-solid">
                        <option value="most_stock" {{ $view === 'most_stock' ? 'selected' : '' }}>Most Stock on Hand</option>
                        <option value="least_stock" {{ $view === 'least_stock' ? 'selected' : '' }}>Least Stock on Hand</option>
                        <option value="best_selling" {{ $view === 'best_selling' ? 'selected' : '' }}>Best Selling</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="branch_id">Branch</label>
                    <select id="branch_id" class="form-select form-select-solid">
                        @if ($branches->count() > 1)
                            <option value="" {{ empty($branchId) ? 'selected' : '' }}>All Branches</option>
                        @endif
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ (string) $branchId === (string) $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4" id="date_range_wrapper" style="{{ $view === 'best_selling' ? '' : 'display:none;' }}">
                    <label class="form-label" for="date_range">Date Range</label>
                    <input id="date_range"
                        data-selected-range="{{ $selectedRangeParam }}"
                        data-kt-daterangepicker="true"
                        data-start-date="{{ $startDateParam }}"
                        data-end-date="{{ $endDateParam }}"
                        name="date_range"
                        type="text"
                        class="form-control form-control-solid"
                        data-kt-daterangepicker-opens="right"
                    />
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="clearFilters" class="btn btn-light w-100">
                        {!! getIcon('arrows-circle', 'fs-2', '', 'i') !!}
                        Clear Filters
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-row-bordered gy-5 table-bordered">
                    <thead>
                        <tr class="fw-semibold fs-6 text-gray-800">
                            <th>#</th>
                            <th>Product</th>
                            @if (!$aggregateBranches)
                                <th>Branch</th>
                            @endif
                            <th class="text-end">Stock on Hand</th>
                            @if ($view === 'best_selling')
                                <th class="text-end">Total Outbound</th>
                                <th class="text-end">Sales</th>
                                <th class="text-end">Transfer Out</th>
                                <th class="text-end">Disposal</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $index => $product)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $product->product_name }}</td>
                                @if (!$aggregateBranches)
                                    <td>{{ $product->branch_name ?? 'N/A' }}</td>
                                @endif
                                <td class="text-end">{{ number_format($product->stock, 2) }}</td>
                                @if ($view === 'best_selling')
                                    <td class="text-end">{{ number_format($product->total_outbound_qty, 2) }}</td>
                                    <td class="text-end">{{ number_format($product->sales_qty, 2) }}</td>
                                    <td class="text-end">{{ number_format($product->transfer_out_qty, 2) }}</td>
                                    <td class="text-end">{{ number_format($product->disposal_qty, 2) }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $view === 'best_selling' ? ($aggregateBranches ? 8 : 9) : ($aggregateBranches ? 4 : 5) }}" class="text-center">
                                    No data available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const viewSelect = document.getElementById('view');
            const branchSelect = document.getElementById('branch_id');
            const dateRangeWrapper = document.getElementById('date_range_wrapper');
            const dateRange = document.getElementById('date_range');

            function toggleDateRange() {
                if (viewSelect.value === 'best_selling') {
                    dateRangeWrapper.style.display = '';
                } else {
                    dateRangeWrapper.style.display = 'none';
                }
            }

            function reloadReport() {
                const url = new URL(window.location.href);

                url.searchParams.set('view', viewSelect.value);

                if (branchSelect.value) {
                    url.searchParams.set('branch_id', branchSelect.value);
                } else {
                    url.searchParams.delete('branch_id');
                }

                if (viewSelect.value === 'best_selling' && dateRange.value) {
                    url.searchParams.set('date_range', dateRange.value);
                    url.searchParams.set('selectedRange', $('#date_range').attr('data-selected-range') || 'Today');
                    url.searchParams.set('startDate', $('#date_range').attr('data-start-date') || '');
                    url.searchParams.set('endDate', $('#date_range').attr('data-end-date') || '');
                } else {
                    url.searchParams.delete('date_range');
                    url.searchParams.delete('selectedRange');
                    url.searchParams.delete('startDate');
                    url.searchParams.delete('endDate');
                }

                window.location.href = url.toString();
            }

            viewSelect.addEventListener('change', function () {
                toggleDateRange();
                reloadReport();
            });

            branchSelect.addEventListener('change', reloadReport);

            if (dateRange) {
                $('#date_range').on('change.datetimepicker', reloadReport);
            }

            document.getElementById('clearFilters').addEventListener('click', function () {
                viewSelect.value = 'most_stock';
                branchSelect.value = '';
                toggleDateRange();
                window.location.href = '{{ route('inventory-tracking.report') }}';
            });

            toggleDateRange();
        });
    </script>
    @endpush

</x-default-layout>
