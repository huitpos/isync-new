<x-default-layout>

    @section('title')
        Void Transactions Report
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

                        @error('branch')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Payment Type</label>

                        <select id="payment_type" name="payment_type" class="form-select @error('branch') is-invalid @enderror" required>
                            <option value="">All</option>
                            @foreach ($paymentTypes as $paymentType)
                                <option value="{{ $paymentType->id }}" {{ $paymentType->id == $paymentTypeId ? 'selected' : '' }}>{{ $paymentType->name }}</option>
                            @endforeach
                        </select>

                        @error('branch')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
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
                        <button type="submit" class="btn btn-primary mt-8">Export</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table id="kt_datatable_zero_configuration" class="table table-striped table-row-bordered gy-5">
                    <thead>
                        <tr class="fw-semibold fs-6 text-muted">
                            <th>Void No.</th>
                            <th>Date</th>
                            <th>Sales Invoice #</th>
                            <th>Machine #</th>
                            <th>Discount Amount</th>
                            <th>Gross Sales</th>
                            <th>Net Sales</th>
                            <th>Remarks</th>
                            <th>Cashier</th>
                            <th>Approved By</th>
                            <th>Payment Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            @php
                                $uniquePaymentTypes = $transaction->payments
                                    ->pluck('payment_type_name')  // Extract payment_type_name
                                    ->unique()                    // Get unique values
                                    ->implode(','); 
                            @endphp
                            <tr>
                                <th>{{ $transaction->void_counter }}</th>
                                <th>{{ $transaction->treg }}</th>
                                <th>{{ $transaction->receipt_number }}</th>
                                <th>{{ $transaction->machine->machine_number }}</th>
                                <th>{{ number_format($transaction->discount_amount, 2) }}</th>
                                <th>{{ number_format($transaction->gross_sales, 2) }}</th>
                                <th>{{ number_format($transaction->net_sales - $transaction->vat_amount, 2) }}</th>
                                <th>{{ $transaction->void_remarks }}</th>
                                <th>{{ $transaction->cashier_name }}</th>
                                <th></th>
                                <th>{{ $uniquePaymentTypes }}</th>
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
                const paymentType = document.getElementById('payment_type');

                function updateURLAndRefresh() {
                    const dateValue = dateRange.value;
                    const branchValue = branchId.value;
                    const paymentTypeValue = paymentType.value;

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

                    if (paymentTypeValue) {
                        url.searchParams.set('payment_type_id', paymentTypeValue);
                    } else {
                        url.searchParams.delete('payment_type_id');
                    }

                    //use selectedRange, startDate, endDate in searchParams
                    url.searchParams.set('selectedRange', selectedRange);
                    url.searchParams.set('startDate', startDate);
                    url.searchParams.set('endDate', endDate);   

                    window.location.href = url.toString();
                }

                dateRange.addEventListener('change', updateURLAndRefresh);
                branchId.addEventListener('change', updateURLAndRefresh);
                paymentType.addEventListener('change', updateURLAndRefresh);

                $("#date_range").on("change.datetimepicker", ({date, oldDate}) => {
                    updateURLAndRefresh()
                });
            });
        </script>
    @endpush
</x-default-layout>

