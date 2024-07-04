<x-default-layout>

    @section('title')
        Sales Transaction Report
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" method="POST" novalidate>
                @csrf

                <div class="row mb-5">
                    <div class="col-md-4">
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

                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input data-month-select-only="true" value="{{ old('start_date') ?? date('F Y') }}" name="start_date" type="text" class="form-control @error('start_date') is-invalid @enderror flatpack-picker" placeholder="Date From" required/>

                        @error('start_date')
                            <div class="invalid-feedback"> {{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary mt-5">Generate</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-default-layout>
