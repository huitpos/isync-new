<x-default-layout>

    @section('title')
        Sales Invoices Report
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" method="POST" novalidate>
                @csrf

                <div class="row mb-5">
                    <div class="col-md-2">
                        <label class="form-label">Date</label>
                        <input id="start_date" data-month-select-only="true" value="{{ old('start_date') ?? $dateParam }}" name="start_date" type="text" class="form-control @error('start_date') is-invalid @enderror flatpack-picker" placeholder="Date From" required/>

                        @error('start_date')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
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
                            <th>Void</th>
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
                                <th>{{ $transaction->receipt_number }}</th>
                                <th></th>
                                <th>1</th>
                                <th>{{ $transaction->cashier_name }}</th>
                                <th>{{ $transaction->shift_number }}</th>
                                <th>{{ number_format($transaction->gross_sales, 2) }}</th>
                                <th>{{ number_format($transaction->net_sales, 2) }}</th>
                                <th>{{ number_format($transaction->vatable_sales, 2) }}</th>
                                <th>{{ number_format($transaction->vat_amount, 2) }}</th>
                                <th>{{ number_format($transaction->vat_exempt_sales, 2) }}</th>
                                <th>{{ number_format($transaction->vat_expense, 2) }}</th>
                                <th>{{ number_format($transaction->discount_amount, 2) }}</th>
                                <th>{{ $paymentTypeNames->join(', ') }}</th>
                                <th>{{ $transaction->is_void ? 'Yes' : 'No' }}</th>
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
                const startDate = document.getElementById('start_date');

                function updateURLAndRefresh() {
                    const dateValue = startDate.value;
                    const url = new URL(window.location.href);
                    if (dateValue) {
                        url.searchParams.set('start_date', dateValue);
                    } else {
                        url.searchParams.delete('start_date');
                    }
                    window.location.href = url.toString();
                }

                startDate.addEventListener('change', updateURLAndRefresh);
                branchId.addEventListener('change', updateURLAndRefresh);
            });
        </script>
    @endpush
</x-default-layout>

