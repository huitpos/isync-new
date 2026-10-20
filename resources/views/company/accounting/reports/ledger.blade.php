<x-default-layout>

    @section('title')
        Account Ledger
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.gl.ledger', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            @include('company.accounting.reports._filters', ['showAccount' => true, 'showRange' => true])

            @if ($ledger)
                <h3 class="mb-4">{{ $ledger['account']->code }} — {{ $ledger['account']->name }}</h3>
                <p>Opening balance: <strong>{{ number_format($ledger['opening'], 2) }}</strong></p>
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Journal</th>
                            <th>Description</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ledger['rows'] as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->entry_date)->format('Y-m-d') }}</td>
                                <td><a href="{{ route('company.journals.show', ['companySlug' => $company->slug, 'id' => $row->journal_entry_id]) }}">{{ $row->number }}</a></td>
                                <td>{{ $row->description }}</td>
                                <td class="text-end">{{ $row->debit > 0 ? number_format($row->debit, 2) : '' }}</td>
                                <td class="text-end">{{ $row->credit > 0 ? number_format($row->credit, 2) : '' }}</td>
                                <td class="text-end">{{ number_format($row->balance, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p>Closing balance: <strong>{{ number_format($ledger['closing'], 2) }}</strong></p>
            @else
                <p class="text-muted">Select an account to see its ledger.</p>
            @endif
        </div>
    </div>

</x-default-layout>
