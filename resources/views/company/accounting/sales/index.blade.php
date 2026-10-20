<x-default-layout>

    @section('title')
        Post Sales Day
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.salesPosting.index', $company) }}
    @endsection

    <div class="card mb-5">
        <div class="card-body py-4">
            @if (!$enabled)
                <div class="alert alert-warning">Load a chart of accounts and map the sales accounts before posting.</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="GET" action="{{ route('company.sales-posting.index', ['companySlug' => $company->slug]) }}" class="row align-items-end">
                <input type="hidden" name="preview" value="1">
                <div class="col-md-4">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select" required>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('branch_id', request('branch_id')) === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Business date</label>
                    <input type="date" name="date" value="{{ old('date', request('date', now()->toDateString())) }}" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" type="submit">Preview</button>
                </div>
            </form>
        </div>
    </div>

    @if ($preview)
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Sales</h3></div>
            <div class="card-body py-4">
                @if ($preview['existing'])
                    <div class="alert alert-info">Already posted as <a href="{{ route('company.journals.show', ['companySlug' => $company->slug, 'id' => $preview['existing']->id]) }}">{{ $preview['existing']->number }}</a>. Void that journal before posting this day again.</div>
                @endif
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Memo</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($preview['lines'] as $line)
                            @php $account = $preview['accounts'][$line['account_id']] ?? null; @endphp
                            <tr>
                                <td>{{ $account ? $account->code.' — '.$account->name : $line['account_id'] }}</td>
                                <td>{{ $line['memo'] }}</td>
                                <td class="text-end">{{ $line['debit'] > 0 ? number_format($line['debit'], 2) : '' }}</td>
                                <td class="text-end">{{ $line['credit'] > 0 ? number_format($line['credit'], 2) : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if (!$preview['existing'])
                    <form method="POST" action="{{ route('company.sales-posting.sales', ['companySlug' => $company->slug]) }}">
                        @csrf
                        <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                        <input type="hidden" name="date" value="{{ request('date') }}">
                        <button class="btn btn-primary" type="submit">Post sales</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">COGS</h3></div>
            <div class="card-body py-4">
                <p>End-of-day FIFO cost: <strong>{{ number_format($cogsAmount, 2) }}</strong></p>
                @if ($cogsExisting)
                    <div class="alert alert-info">Already posted as <a href="{{ route('company.journals.show', ['companySlug' => $company->slug, 'id' => $cogsExisting->id]) }}">{{ $cogsExisting->number }}</a>.</div>
                @elseif ($cogsAmount > 0)
                    <form method="POST" action="{{ route('company.sales-posting.cogs', ['companySlug' => $company->slug]) }}">
                        @csrf
                        <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                        <input type="hidden" name="date" value="{{ request('date') }}">
                        <button class="btn btn-primary" type="submit">Post COGS</button>
                    </form>
                @else
                    <p class="text-muted">No end-of-day cost consumption for this date. Sales can still be posted.</p>
                @endif
            </div>
        </div>
    @endif

</x-default-layout>
