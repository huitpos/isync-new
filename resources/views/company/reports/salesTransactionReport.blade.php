<x-default-layout>

    @section('title')
        Sales Transaction Report
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
                            <th>Machine No</th>
                            <th>SI No.</th>
                            <th>Table No.</th>
                            <th>Customer Count</th>
                            <th>Cashier Details</th>
                            <th>Shift</th>
                            <th>Gross Sales</th>
                            <th>Net Sales</th>
                            <th>VAT Sales</th>
                            <th>VAT Amount</th>
                            <th>VAT Exempt</th>
                            <th>VAT Expense</th>
                            <th>Discount</th>
                            <th>Type Of Payment</th>
                            <th>Quantity</th>
                            <th>Amount Paid</th>
                            <th>Service Charge</th>
                            <th>Transaction Type</th>
                            <th>Change</th>
                            <th>Approver</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            @php
                                $paymentTypeNames = $transaction->payments->pluck('payment_type_name');
                            @endphp
                            <tr>
                                <td>{{ $transaction->completed_at }}</td>
                                <th>{{ $transaction->machine->machine_number }}</th>
                                <th>
                                    <a href="{{ route('company.reports.view-transaction', [
                                            'transactionId' => $transaction->id,
                                            'companySlug' => $company->slug,
                                        ]) }}"
                                        target="_blank"
                                    >
                                        {{ $transaction->transaction_number }}
                                        {{ $transaction->receipt_number }}
                                    </a>
                                </th>
                                <th></th>
                                <th>1</th>
                                <th>{{ $transaction->cashier_name }}</th>
                                <th>{{ $transaction->shift_number }}</th>
                                <th>{{ number_format($transaction->gross_sales, 2) }}</th>
                                <th>{{ number_format($transaction->net_sales - $transaction->vat_amount, 2) }}</th>
                                <th>{{ number_format($transaction->vatable_sales, 2) }}</th>
                                <th>{{ number_format($transaction->vat_amount, 2) }}</th>
                                <th>{{ number_format($transaction->vat_exempt_sales, 2) }}</th>
                                <th>{{ number_format($transaction->vat_expense, 2) }}</th>
                                <th>{{ number_format($transaction->discount_amount, 2) }}</th>
                                <th>{{ $paymentTypeNames->join(', ') }}</th>
                                <th>{{ $transaction->total_quantity }}</th>
                                <th>{{ number_format($transaction->tender_amount, 2) }}</th>
                                <th>{{ number_format($transaction->service_charge, 2) }}</th>
                                <th>{{ $transaction->type }}</th>
                                <th>{{ number_format($transaction->change, 2) }}</th>
                                <th></th>
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

