<x-default-layout>

    @section('title')
        Account Receivables
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" method="POST" novalidate>
                @csrf
            </form>

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
                        @foreach($accountReceivables as $ar)
                            @php
                                $totalSales += $ar->total_sales;
                                $totalRedeemedSales += $ar->redeemed_sales;
                                $totalNotRedeemedSales += $ar->not_redeemed_sales;
                            @endphp
                            <tr>
                                <td>
                                    {{ $ar->name }}
                                </td>
                                <td>{{ $ar->address }}</td>
                                <td>{{ number_format($ar->total_sales, 2) }}</td>
                                <td>{{ number_format($ar->redeemed_sales, 2) }}</td>
                                <td>{{ number_format($ar->not_redeemed_sales) }}</td>
                                <td>{{ number_format($ar->not_redeemed_sales / $ar->total_sales * 100) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td></td>
                            <td><strong>Total</strong></td>
                            <td>{{ number_format($totalSales, 2) }}</td>
                            <td>{{ number_format($totalRedeemedSales, 2) }}</td>
                            <td>{{ number_format($totalNotRedeemedSales, 2) }}</td>
                            <td>{{ number_format($totalNotRedeemedSales / $totalSales * 100, 2) }}</td>
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
                            <th>Item Description</th>
                            <th>Qty</th>
                            <th>UOM</th>
                            <th>Amount</th>
                            <th>Discount</th>
                            <th>Cashier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td>
                                    <a target="_blank" href="{{ route('branch.reports.view-transaction', [
                                            'companySlug' => $company->slug,
                                            'transactionId' => $transaction->id,
                                            'branchSlug' => $branch->slug,
                                        ]) }}"
                                    >
                                        {{ $transaction->id }}
                                    </a>
                                </td>
                                <td>{{ $transaction->receipt_number }}</td>
                                <td>{{ $transaction->completed_at }}</td>
                                <td>{{ $transaction->item_description }}</td>
                                <td>{{ $transaction->qty }}</td>
                                <td>{{ $transaction->uom }}</td>
                                <td>{{ $transaction->gross }}</td>
                                <td>{{ $transaction->discount_amount }}</td>
                                <td>{{ $transaction->cashier_name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-default-layout>