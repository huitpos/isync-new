<x-default-layout>

    @section('title')
        Audit Trail Report
    @endsection

    <div class="card" data-dt-card>
        <div class="card-body py-4">
            <form class="mt-3" method="POST" novalidate>
                @csrf

                <div class="row mb-5">
                    <div class="col-md-5">
                        <label class="form-label">Machine</label>

                        <select id="machine_id" name="machine_id" class="form-select @error('branch') is-invalid @enderror" required data-dt-filter="machine_id">
                            @foreach ($machines as $machine)
                                <option value="{{ $machine->id }}" {{ $machine->id == $machineId ? 'selected' : '' }}>{{ $machine->branch->name . ' - ' . $machine->name }}</option>
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
                         data-dt-filter="date_range">
                    </div>
                    
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary mt-8">Export</button>
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

