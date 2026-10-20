<x-default-layout>

    @section('title')
        Discounts Report
    @endsection

    <div class="card" data-dt-card>
        <div class="card-body py-4">
            <form class="mt-3" method="POST" novalidate>
                @csrf

                <div class="row mb-5">
                    <div class="col-md-3">
                        <label class="form-label">Branch</label>

                        <select id="branch_id" name="branch_id" class="form-select @error('branch') is-invalid @enderror" required data-dt-filter="branch_id">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $branch->id == $branchId ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Discount Type</label>

                        <select multiple data-control="select2" name="discount_types[]" class="form-select @error('branch') is-invalid @enderror" required data-dt-filter="discount_types">
                            @foreach ($discountTypes as $discountType)
                                <option value="{{ $discountType->id }}" {{ in_array($discountType->id, $filterDiscountTypes) ? 'selected' : ''}}>{{ $discountType->name }}</option>
                            @endforeach
                        </select>
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
                         data-dt-filter="date_range">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" value="search" name="search" class="btn btn-primary mt-8">Search</button>

                        <button type="submit" value="export" name="export" class="btn btn-primary mt-8">Export</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>
    @push('scripts')
        {{ $dataTable->scripts() }}
    @endpush
</x-default-layout>