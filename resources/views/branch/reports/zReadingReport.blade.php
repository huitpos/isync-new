<x-default-layout>

    @section('title')
        Z Reading Report
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
                            <td>Machine #</td>
                            <td>Date</td>
                            <td>Z-Read No.</td>
                            <td>Beginning Official Receipt</td>
                            <td>Ending Official Receipt</td>
                            <td>Beginning Balance</td>
                            <td>Ending Balance</td>
                            <td>Gross Sales</td>
                            <td>Net Sales</td>
                            <td>Vatable Sales</td>
                            <td>Vat Discount</td>
                            <td>Vat Amount</td>
                            <td>Service Charge</td>

                            @foreach ($paymentTypes as $paymentType)
                                <td>{{ $paymentType->name }}</td>
                            @endforeach

                            <td>Void</td>

                            @foreach ($discountTypes as $discountType)
                                <td>{{ $discountType->name }}</td>
                            @endforeach

                            <td>Cashier Name</td>
                            <td>X-reading beginning #</td>
                            <td>X-reading ending #</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($endOfDays as $data)
                            @php
                                $cutOffs = App\Models\CutOff::where('branch_id', $branchId)
                                    ->where([
                                        'end_of_day_id' => $data->end_of_day_id
                                    ])
                                    ->get();

                                $cutOffIds = $cutOffs->pluck('cut_off_id')->unique()->toArray();
                            @endphp
                            <tr>
                                <td>{{ $data->machine->machine_number }}</td>
                                <td>{{ $data->treg }}</td>
                                <td>{{ $data->reading_number }}</td>
                                <td>{{ $data->beginning_or }}</td>
                                <td>{{ $data->ending_or }}</td>
                                <td>{{ number_format($data->beginning_amount, 2) }}</td>
                                <td>{{ number_format($data->ending_amount, 2) }}</td>
                                <td>{{ number_format($data->gross_sales, 2) }}</td>
                                <td>{{ number_format($data->net_sales, 2) }}</td>
                                <td>{{ number_format($data->vatable_sales, 2) }}</td>
                                <td>{{ number_format($data->vat_exempt_sales, 2) }}</td>
                                <td>{{ number_format($data->vat_expense, 2) }}</td>
                                <td>{{ number_format($data->vat_amount, 2) }}</td>
                                <td>{{ number_format($data->total_service_charge, 2) }}</td>

                                @foreach ($paymentTypes as $paymentType)
                                    @php
                                        $payments = App\Models\Payment::where([
                                            'payment_type_id' => $paymentType->id,
                                            'branch_id' => $branchId,
                                            'is_void' => false
                                        ])
                                        ->whereIn('cut_off_id', $cutOffIds)
                                        ->get();
                                    @endphp

                                    <td>{{ number_format($payments->sum('amount'), 2) }}</td>
                                @endforeach

                                <td>{{ number_format($data->void_amount, 2) }}</td>

                                @foreach ($discountTypes as $discountType)
                                    @php
                                        $discounts = App\Models\Discount::where([
                                            'discount_type_id' => $discountType->id,
                                            'branch_id' => $branchId,
                                            'is_void' => false
                                        ])
                                        ->whereIn('cut_off_id', $cutOffIds)
                                        ->get();
                                    @endphp
                                    <td>{{ number_format($discounts->sum('discount_amount'), 2) }}</td>
                                @endforeach

                                <td>{{ $data->cashier_name }}</td>
                                <td></td>
                                <td></td>
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