<x-default-layout>

    @section('title')
        Top Performing Products Report
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
                    <tbody>
                        @if(isset($topProducts) && count($topProducts) > 0)
                            @foreach($topProducts as $product)
                                <tr>
                                    <td>{{ $product->description }}</td>
                                    <td>{{ $product->sku }}</td>
                                    <td>{{ $product->department }}</td>
                                    <td>{{ $product->category }}</td>
                                    <td>{{ $product->sub_category }}</td>
                                    <td class="text-end">{{ number_format($product->quantity_sold, 0) }}</td>
                                    <td class="text-end">{{ number_format($product->ar_unpaid_quantity, 0) }}</td>
                                    <td class="text-end">{{ number_format($product->total_unit_cost, 2) }}</td>
                                    <td class="text-end">{{ number_format($product->discount_sales, 2) }}</td>
                                    <td class="text-end">{{ number_format($product->regular_sales, 2) }}</td>
                                    <td class="text-end">{{ number_format($product->sales_percentage, 0) }}%</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="11" class="text-center">No data available</td>
                            </tr>
                        @endif
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
