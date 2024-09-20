<x-default-layout>

    @section('title')
        Audit Trail Report
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" method="POST" novalidate>
                @csrf

                <div class="row mb-5">
                    <div class="col-md-5">
                        <label class="form-label">Machine</label>

                        <select id="machine_id" name="machine_id" class="form-select @error('branch') is-invalid @enderror" required>
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
                        />
                    </div>
                    
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary mt-8">Export</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table id="kt_datatable_zero_configuration" class="table table-striped table-row-bordered gy-5">
                    <thead>
                        <tr class="fw-semibold fs-6 text-muted">
                            <th>Created At</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Authorized By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trails as $trail)
                            <tr>
                                <td>{{ $trail->treg }}</td>
                                <td>{{ $trail->action }}</td>
                                <td>{{ $trail->description }}</td>
                                <td>{{ $trail->authorize_name }}</td>
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
                const dateRange = document.getElementById('date_range');
                const machineId = document.getElementById('machine_id');

                function updateURLAndRefresh() {
                    const dateValue = dateRange.value;
                    const machineValue = machineId.value;

                    const selectedRange = $("#date_range").attr("data-selected-range");
                    const startDate = $("#date_range").attr("data-start-date");
                    const endDate = $("#date_range").attr("data-end-date");

                    const url = new URL(window.location.href);
                    if (dateValue) {
                        url.searchParams.set('date_range', dateValue);
                    } else {
                        url.searchParams.delete('date_range');
                    }

                    if (machineValue) {
                        url.searchParams.set('machine_id', machineValue);
                    } else {
                        url.searchParams.delete('machine_id');
                    }

                    //use selectedRange, startDate, endDate in searchParams
                    url.searchParams.set('selectedRange', selectedRange);
                    url.searchParams.set('startDate', startDate);
                    url.searchParams.set('endDate', endDate);   

                    window.location.href = url.toString();
                }

                dateRange.addEventListener('change', updateURLAndRefresh);
                machineId.addEventListener('change', updateURLAndRefresh);

                $("#date_range").on("change.datetimepicker", ({date, oldDate}) => {
                    updateURLAndRefresh()
                });
            });
        </script>
    @endpush
</x-default-layout>

