<x-default-layout>

    @section('title')
        Account Receivables
    @endsection

    <div class="card">
        <div class="card-body py-4">
            @include('company.reports.partials.ar-branch-filter', [
                'action' => route('company.reports.account-receivable-details', [
                    'companySlug' => $company->slug,
                    'customerId' => $customerId,
                ]),
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
                        @php
                            $totalSales = 0;
                            $totalRedeemedSales = 0;
                            $totalNotRedeemedSales = 0;
                        @endphp
                        @forelse($accountReceivables as $ar)
                            @php
                                $totalSales += $ar->total_sales;
                                $totalRedeemedSales += $ar->redeemed_sales;
                                $totalNotRedeemedSales += $ar->not_redeemed_sales;
                            @endphp
                            <tr>
                                <td>{{ $ar->name }}</td>
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
                            <td><strong>Total</strong></td>
                            <td>{{ number_format($totalSales, 2) }}</td>
                            <td>{{ number_format($totalRedeemedSales, 2) }}</td>
                            <td>{{ number_format($totalNotRedeemedSales, 2) }}</td>
                            <td>{{ number_format($totalSales > 0 ? ($totalNotRedeemedSales / $totalSales * 100) : 0, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-5">
        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table table-striped table-row-bordered gy-5">
                    <thead>
                        <tr class="fw-semibold fs-6 text-muted">
                            <th>Transaction ID</th>
                            <th>SI #</th>
                            <th>Datetime</th>
                            <th>Branch</th>
                            <th>Item Description</th>
                            <th>Qty</th>
                            <th>UOM</th>
                            <th>Amount</th>
                            <th>Discount</th>
                            <th>Cashier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>
                                    <a target="_blank" href="{{ route('company.reports.view-transaction', [
                                            'companySlug' => $company->slug,
                                            'transactionId' => $transaction->id,
                                        ]) }}">
                                        {{ $transaction->id }}
                                    </a>
                                </td>
                                <td>{{ $transaction->receipt_number }}</td>
                                <td>{{ $transaction->completed_at }}</td>
                                <td>{{ $transaction->branch_name }}</td>
                                <td>{{ $transaction->item_description }}</td>
                                <td>{{ $transaction->qty }}</td>
                                <td>{{ $transaction->uom }}</td>
                                <td>{{ $transaction->gross }}</td>
                                <td>{{ $transaction->discount_amount }}</td>
                                <td>{{ $transaction->cashier_name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('reports.partials.pagination', ['paginator' => $transactions])
        </div>
    </div>
</x-default-layout>
