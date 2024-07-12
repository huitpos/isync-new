<x-default-layout>

    @section('title')
        Vat Sales Report
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
                            <th>Machine Number</th>
                            <th>Sales Invoice Number</th>
                            <th>Sub Total</th>
                            <th>Total Amount Due</th>
                            <th>Vat Amount</th>
                            <th>Vatable Sales</th>
                            <th>Vat Exempt Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            @php
                                $vatableSales = $transaction->vatable_sales;
                                $vatAmount = $transaction->vat_amount;
                                $amountDue = $vatableSales + $vatAmount;
                            @endphp
                            <tr>
                                <th>{{ $transaction->treg }}</th>
                                <th>{{ $transaction->machine->machine_number }}</th>
                                <th>{{ $transaction->receipt_number }}</th>
                                <th>{{ number_format($amountDue, 2) }}</th>
                                <th>{{ number_format($amountDue, 2) }}</th>
                                <th>{{ number_format($vatAmount, 2) }}</th>
                                <th>{{ number_format($vatableSales, 2) }}</th>
                                <th>{{ number_format($transaction->vat_exempt_sales, 2) }}</th>
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

