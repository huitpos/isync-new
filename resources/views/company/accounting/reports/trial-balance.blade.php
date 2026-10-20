<x-default-layout>

    @section('title')
        Trial Balance
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.gl.trialBalance', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            @include('company.accounting.reports._filters', ['showAsOf' => true])
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Account</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ $row->code }}</td>
                            <td>{{ $row->account_name }}</td>
                            <td class="text-end">{{ $row->debit_balance > 0 ? number_format($row->debit_balance, 2) : '' }}</td>
                            <td class="text-end">{{ $row->credit_balance > 0 ? number_format($row->credit_balance, 2) : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="2">Total</td>
                        <td class="text-end">{{ number_format($debitTotal, 2) }}</td>
                        <td class="text-end">{{ number_format($creditTotal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</x-default-layout>
