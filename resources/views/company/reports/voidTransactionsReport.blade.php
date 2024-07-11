<x-default-layout>

    @section('title')
        Void Transactions Report
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <th>{{ $transaction->void_counter }}</th>
                                <th>{{ $transaction->treg }}</th>
                                <th>{{ $transaction->receipt_number }}</th>
                                <th>{{ $transaction->machine->machine_number }}</th>
                                <th>{{ $transaction->discount_amount }}</th>
                                <th>{{ $transaction->gross_sales }}</th>
                                <th>{{ $transaction->net_sales }}</th>
                                <th>{{ $transaction->void_remarks }}</th>
                                <th>{{ $transaction->cashier_name }}</th>
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

