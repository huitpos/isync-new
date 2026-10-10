<x-default-layout>

    @section('title')
        Payment Summary Report
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

            @php
                $totalQty = $payments->sum('qty');
                $totalAmount = $payments->sum('amount');
            @endphp

            <div class="table-responsive">
                <table class="table table-striped table-row-bordered gy-5">
                    <thead>
                        <tr class="fw-semibold fs-6 text-muted">
                            <th>Payment Type</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>
                                    <a href="{{ route('company.reports.payment-summary-details', array_filter([
                                            'companySlug' => $company->slug,
                                            'branch_id' => $branchId,
                                            'payment_type' => $payment->payment_type ?? '',
                                            'date_range' => $dateParam,
                                        ], fn ($value) => $value !== null)) }}"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        {{ $payment->payment_type }}
                                    </a>
                                </td>
                                <td class="text-end">{{ number_format($payment->qty) }}</td>
                                <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                                <td class="text-end">{{ $totalAmount > 0 ? number_format(($payment->amount / $totalAmount) * 100, 2) : '0.00' }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No payments for the selected branch and date range.</td>
                            </tr>
                        @endforelse
                        @if ($payments->isNotEmpty())
                            <tr class="fw-bold">
                                <td>TOTAL</td>
                                <td class="text-end">{{ number_format($totalQty) }}</td>
                                <td class="text-end">{{ number_format($totalAmount, 2) }}</td>
                                <td class="text-end">100.00%</td>
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
