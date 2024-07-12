<x-default-layout>

    @section('title')
        X Reading Report
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
                            <th>Machine #</th>
                            <th>Shift No</th>
                            <th>X-reading #</th>
                            <th>Beginning SI #</th>
                            <th>Ending SI #</th>
                            <th>Cut Off Date</th>
                            <th>Gross Sales</th>
                            <th>Net Sales</th>
                            <th>Vatable Sales</th>
                            <th>Vat Exempt Sales</th>
                            <th>Vat Amount</th>
                            <th>Vat Discount</th>

                            @foreach ($paymentTypes as $paymentType)
                                <th>{{ $paymentType->name }}</th>
                            @endforeach

                            <th>Service Charge</th>
                            <th>Short/Over</th>
                            <th>Void</th>

                            @foreach ($discountTypes as $discountType)
                                <th>{{ $discountType->name }}</th>
                            @endforeach

                            <th>Cashier Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cutoffs as $cutoff)
                            @php
                                $transactions = \App\Models\Transaction::where([
                                    'cut_off_id' => $cutoff->cut_off_id,
                                    'branch_id' => $cutoff->branch_id,
                                ])
                                ->get();

                                $transactionIds = $transactions->pluck('transaction_id')->unique()->toArray();
                            @endphp
                            <tr>
                                <th>{{ $cutoff->machine->machine_number }}</th>
                                <th>{{ $cutoff->shift_number }}</th>
                                <th>{{ $cutoff->reading_number }}</th>
                                <th>{{ $cutoff->beginning_or }}</th>
                                <th>{{ $cutoff->ending_or }}</th>
                                <th>{{ $cutoff->treg }}</th>
                                <th>{{ number_format($cutoff->gross_sales, 2) }}</th>
                                <th>{{ number_format($cutoff->net_sales, 2) }}</th>
                                <th>{{ number_format($cutoff->vatable_sales, 2) }}</th>
                                <th>{{ number_format($cutoff->vat_exempt_sales, 2) }}</th>
                                <th>{{ number_format($cutoff->vat_amount, 2) }}</th>
                                <th>{{ number_format($cutoff->vat_expense, 2) }}</th>

                                @foreach ($paymentTypes as $paymentType)
                                    @php
                                        $payments = \App\Models\Payment::where([
                                            'cut_off_id' => $cutoff->cut_off_id,
                                            'payment_type_id' => $paymentType->id,
                                            'branch_id' => $cutoff->branch_id,
                                        ])
                                        ->get();
                                    @endphp
                                    <th>{{ number_format($payments->sum('amount'), 2) }}</th>
                                @endforeach

                                <th>{{ number_format($cutoff->total_service_charge, 2) }}</th>
                                <th>{{ number_format($cutoff->total_short_over, 2) }}</th>
                                <th>{{ number_format($cutoff->void_amount, 2) }}</th>

                                @foreach ($discountTypes as $discountType)
                                    @php
                                        $discounts = \App\Models\Discount::where([
                                            'discount_type_id' => $discountType->id,
                                            'branch_id' => $cutoff->branch_id,
                                        ])
                                        ->whereIn('transaction_id', $transactionIds)
                                        ->get();

                                    @endphp
                                    <th>{{ number_format($discounts->sum('discount_amount'), 2) }}</th>
                                @endforeach

                                <th>{{ $cutoff->cashier_name }}</th>
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