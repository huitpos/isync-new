<x-default-layout>

    @section('title')
        Void Transactions Report
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" method="POST" novalidate>
                @csrf

                <div class="mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Branch</label>

                        <select id="branch_id" name="branch_id" class="form-select @error('branch') is-invalid @enderror" required>
                            @foreach ($company->activeBranches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>

                        @error('branch')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Date From</label>
                        <input data-min-date="today" value="{{ old('start_date') ?? date('Y-m-d', strtotime('-30 days')) }}" name="start_date" type="text" class="form-control @error('start_date') is-invalid @enderror flatpack-picker" placeholder="Date From" required/>

                        @error('start_date')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Date To</label>
                        <input data-min-date="today" value="{{ old('end_date') ?? date('Y-m-d') }}" name="end_date" type="text" class="form-control @error('end_date') is-invalid @enderror flatpack-picker" placeholder="Date To" required/>

                        @error('end_date')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-5">Generate</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
