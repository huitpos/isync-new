<x-default-layout>

    @section('title')
        Income Statement
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.gl.incomeStatement', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            @include('company.accounting.reports._filters', ['showRange' => true])
            @foreach (['income' => 'Income', 'expense' => 'Expenses'] as $type => $heading)
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
                        <tr class="fw-bold">
                            <td colspan="2">Total {{ strtolower($heading) }}</td>
                            <td class="text-end">{{ number_format($type === 'income' ? $report['income'] : $report['expense'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
            <h3 class="mt-4">Net income {{ number_format($report['net'], 2) }}</h3>
        </div>
    </div>

</x-default-layout>
