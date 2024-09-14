<x-default-layout>

    @section('title')
        Discounts Report
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" method="POST" novalidate>
                @csrf

                <div class="row mb-5">
                    <div class="col-md-3">
                        <label class="form-label">Discount Type</label>

                        <select multiple data-control="select2" name="discount_types[]" class="form-select @error('branch') is-invalid @enderror" required>
                            @foreach ($discountTypes as $discountType)
                                <option value="{{ $discountType->id }}" {{ in_array($discountType->id, $filterDiscountTypes) ? 'selected' : ''}}>{{ $discountType->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Date</label>
                        <input id="start_date" data-month-select-only="true" value="{{ old('start_date') ?? $dateParam }}" name="start_date" type="text" class="form-control @error('start_date') is-invalid @enderror flatpack-picker" placeholder="Date From" required/>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" value="search" name="search" class="btn btn-primary mt-8">Search</button>

                        <button type="submit" value="export" name="export" class="btn btn-primary mt-8">Export</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table id="kt_datatable_zero_configuration" class="table table-striped table-row-bordered gy-5">
                    <thead>
                        <tr class="fw-semibold fs-6 text-muted">
                            <td>Date</td>
                            <td>Machine NUmber</td>
                            <td>Sales Invoice No.</td>
                            <td>Name</td>
                            <td>OSCA/SC/PWD ID</td>
                            <td>Gross Sales</td>
                            <td>Sales Discount Granted</td>
                            <td>Cashier Name</td>
                            <td>Net Sales</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($discounts as $discount)
                            <tr>
                                <td>{{ $discount->date }}</td>
                                <td>{{ $discount->machine_number }}</td>
                                <td>{{ $discount->receipt_number }}</td>
                                <td>{{ $discount->discount_name }}</td>
                                <td>
                                    @foreach ($discount->otherInfo as $otherInfo)
                                        {{ $otherInfo->name }}: {{ $otherInfo->value }}<br>
                                    @endforeach
                                </td>
                                <td>{{ number_format($discount->gross_sales, 2) }}</td>
                                <td>{{ $discount->discount_amount }}</td>
                                <td>{{ $discount->cashier_name }}</td>
                                <td>{{ number_format($discount->net_sales, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-default-layout>