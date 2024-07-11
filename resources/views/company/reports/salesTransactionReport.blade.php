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
                                <td>{{ $transaction->treg }}</td>
                                <th>{{ $transaction->machine->machine_number }}</th>
                                <th>{{ $transaction->receipt_number }}</th>
                                <th></th>
                                <th>1</th>
                                <th>{{ $transaction->cashier_name }}</th>
                                <th>{{ $transaction->shift_number }}</th>
                                <th>{{ $transaction->gross_sales }}</th>
                                <th>{{ $transaction->net_sales }}</th>
                                <th>{{ $transaction->vatable_sales }}</th>
                                <th>{{ $transaction->vat_amount }}</th>
                                <th>{{ $transaction->vat_exempt_sales }}</th>
                                <th>{{ $transaction->discount_amount }}</th>
                                <th>{{ $paymentTypeNames->join(', ') }}</th>
                                <th>{{ $transaction->is_void ? 'Yes' : 'No' }}</th>
                                <th>{{ $transaction->nonVoiditems->sum('qty') }}</th>
                                <th>{{ $transaction->tender_amount }}</th>
                                <th>{{ $transaction->service_charge }}</th>
                                <th>{{ $transaction->type }}</th>
                                <th>{{ $transaction->change }}</th>
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
                const branchId = document.getElementById('branch_id');

                function updateURLAndRefresh() {
                    const dateValue = startDate.value;
                    const branchValue = branchId.value;
                    const url = new URL(window.location.href);
                    if (dateValue) {
                        url.searchParams.set('start_date', dateValue);
                    } else {
                        url.searchParams.delete('start_date');
                    }
                    if (branchValue) {
                        url.searchParams.set('branch_id', branchValue);
                    } else {
                        url.searchParams.delete('branch_id');
                    }
                    window.location.href = url.toString();
                }

                startDate.addEventListener('change', updateURLAndRefresh);
                branchId.addEventListener('change', updateURLAndRefresh);
            });
        </script>
    @endpush
</x-default-layout>

