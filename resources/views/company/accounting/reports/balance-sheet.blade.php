<x-default-layout>

    @section('title')
        Balance Sheet
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.gl.balanceSheet', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            @include('company.accounting.reports._filters', ['showAsOf' => true])
            @foreach (['asset' => 'Assets', 'liability' => 'Liabilities', 'equity' => 'Equity'] as $type => $heading)
                <h3 class="mt-4">{{ $heading }}</h3>
                <table class="table align-middle">
                    <tbody>
                        @foreach ($report['rows']->where('type', $type) as $row)
                            <tr>
                                <td>{{ $row->code }}</td>
                                <td>{{ $row->account_name }}</td>
                                <td class="text-end">{{ number_format($row->amount, 2) }}</td>
                            </tr>
                        @endforeach
                        @if ($type === 'equity')
                            <tr>
                                <td></td>
                                <td>Current earnings</td>
                                <td class="text-end">{{ number_format($report['earnings'], 2) }}</td>
                            </tr>
                        @endif
                        <tr class="fw-bold">
                            <td colspan="2">Total {{ strtolower($heading) }}</td>
                            <td class="text-end">
                                {{ number_format($type === 'asset' ? $report['assets'] : ($type === 'liability' ? $report['liabilities'] : $report['equity'] + $report['earnings']), 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        </div>
    </div>

</x-default-layout>
