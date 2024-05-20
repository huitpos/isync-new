<x-default-layout>
    @php
        $permissions = request()->attributes->get('permissionNames');
    @endphp

    @section('title')
        Dashboard
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.dashboard', $company) }}
    @endsection

    <div class="row g-1 g-xl-5 mb-1 mb-xl-5">
        @if (in_array('Main Dashboard/Transaction Count', $permissions))
        <div class="col-3">
            @include('partials/widgets/small_card', [
                'text' => $transactionCount,
                'subText' => 'Transaction Count',
            ])
        </div>
        @endif

        @if (in_array('Main Dashboard/Total Net Amount', $permissions))
        <div class="col-3">
            @include('partials/widgets/small_card', [
                'text' => number_format($transactionAmount, 2),
                'subText' => 'Total Net Amount',
            ])
        </div>
        @endif

        @if (in_array('Main Dashboard/Total Cost Amount', $permissions))
        <div class="col-3">
            @include('partials/widgets/small_card', [
                'text' => number_format($costAmount, 2),
                'subText' => 'Total Cost Amount',
            ])
        </div>
        @endif

        @if (in_array('Main Dashboard/Profit', $permissions))
        <div class="col-3">
            @include('partials/widgets/small_card', [
                'text' => number_format($transactionAmount - $costAmount, 2),
                'subText' => 'Profit',
            ])
        </div>
        @endif

        <div class="col-12">
            @include('partials/widgets/transactions_table', [
                'completedTransactions' => $completedTransactions,
                'pendingTransactions' => $pendingTransactions ?? [],
                'addBranch' => true,
                'transactionRoute' => 'company.reports.view-transaction'
            ])
        </div>
    </div>
</x-default-layout>
