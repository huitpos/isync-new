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
                        <label class="form-label">Discount Type</label>

                        <select multiple data-control="select2" name="discount_types[]" class="form-select @error('branch') is-invalid @enderror" required data-dt-filter="discount_types">
                            @foreach ($discountTypes as $discountType)
                                <option value="{{ $discountType->id }}" {{ in_array($discountType->id, $filterDiscountTypes) ? 'selected' : ''}}>{{ $discountType->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Date</label>
                        <input id="start_date" data-dt-filter="start_date" data-month-select-only="true" value="{{ old('start_date') ?? $dateParam }}" name="start_date" type="text" class="form-control @error('start_date') is-invalid @enderror flatpack-picker" placeholder="Date From" required/>
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