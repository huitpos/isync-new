<x-default-layout>

    @section('title')
        Fiscal Periods
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.fiscalPeriods.index', $company) }}
    @endsection

    <div class="card mb-5">
        <div class="card-body py-4">
            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('company.fiscal-periods.store', ['companySlug' => $company->slug]) }}" class="row align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Starts</label>
                    <input type="date" name="starts_on" value="{{ old('starts_on') }}" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ends</label>
                    <input type="date" name="ends_on" value="{{ old('ends_on') }}" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" type="submit">Add open period</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body py-4">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Starts</th>
                        <th>Ends</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($periods as $period)
                        <tr>
                            <td>{{ $period->starts_on->format('Y-m-d') }}</td>
                            <td>{{ $period->ends_on->format('Y-m-d') }}</td>
                            <td>{{ ucfirst($period->status) }}</td>
                            <td class="text-end">
                                @if ($period->status === 'open')
                                    <form method="POST" action="{{ route('company.fiscal-periods.close', ['companySlug' => $company->slug, 'id' => $period->id]) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-light-danger" type="submit">Close</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('company.fiscal-periods.reopen', ['companySlug' => $company->slug, 'id' => $period->id]) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-light" type="submit">Reopen</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No fiscal periods yet. Dates outside a closed period can still be posted.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-default-layout>
