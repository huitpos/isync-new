<x-default-layout>

    @section('title')
        Account Receivables Report
    @endsection

    <div class="card">
        <div class="card-body py-4">
            @include('company.reports.partials.ar-branch-filter', [
                'action' => route('company.reports.account-receivables', ['companySlug' => $company->slug]),
                'branches' => $branches,
                'branchId' => $branchId,
            ])

            <div class="table-responsive">
                <table class="table table-striped table-row-bordered gy-5">
                    <thead>
                        <tr class="fw-semibold fs-6 text-muted">
                            <th>Customer Name</th>
                            <th>Address</th>
                            <th>Sales</th>
                            <th>Collected</th>
                            <th>Uncollected</th>
                            <th>Uncollected %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accountReceivables as $ar)
                            @php
                                $detailParams = [
                                    'companySlug' => $company->slug,
                                    'customerId' => $ar->charge_account_id,
                                ];
                                if ($branchId) {
                                    $detailParams['branch_id'] = $branchId;
                                }
                            @endphp
                            <tr>
                                <td>
                                    <a target="_blank" href="{{ route('company.reports.account-receivable-details', $detailParams) }}">
                                        {{ $ar->name }}
                                    </a>
                                </td>
                                <td>{{ $ar->address }}</td>
                                <td>{{ number_format($ar->total_sales, 2) }}</td>
                                <td>{{ number_format($ar->redeemed_sales, 2) }}</td>
                                <td>{{ number_format($ar->not_redeemed_sales, 2) }}</td>
                                <td>{{ number_format($ar->total_sales > 0 ? ($ar->not_redeemed_sales / $ar->total_sales * 100) : 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No account receivables found.</td>
                            </tr>
                        @endforelse
                        <tr>
                            <td></td>
                            <td><strong>Grand Total</strong></td>
                            <td>{{ number_format($totals->total_sales, 2) }}</td>
                            <td>{{ number_format($totals->redeemed_sales, 2) }}</td>
                            <td>{{ number_format($totals->not_redeemed_sales, 2) }}</td>
                            <td>{{ number_format($totals->total_sales > 0 ? ($totals->not_redeemed_sales / $totals->total_sales * 100) : 0, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @include('reports.partials.pagination', ['paginator' => $accountReceivables])
        </div>
    </div>
</x-default-layout>
