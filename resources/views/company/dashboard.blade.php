<x-default-layout>

    @section('title')
        Dashboard
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.dashboard', $company) }}
    @endsection

    <div class="row g-1 g-xl-5 mb-1 mb-xl-5">
        <div class="col-3">
            @include('partials/widgets/small_card', [
                'text' => $transactionCount,
                'subText' => 'Transaction Count',
            ])
        </div>

        <div class="col-3">
            @include('partials/widgets/small_card', [
                'text' => number_format($transactionAmount, 2),
                'subText' => 'Total Net Amount',
            ])
        </div>

        <div class="col-3">
            @include('partials/widgets/small_card', [
                'text' => number_format($costAmount, 2),
                'subText' => 'Total Cost Amount',
            ])
        </div>

        <div class="col-3">
            @include('partials/widgets/small_card', [
                'text' => number_format($transactionAmount - $costAmount, 2),
                'subText' => 'Profit',
            ])
        </div>

        <div class="col-12">
            @include('partials/widgets/transactions_table', [
                'completedTransactions' => $completedTransactions,
                'pendingTransactions' => $pendingTransactions ?? [],
                'addBranch' => true
            ])
        </div>
    </div>
</x-default-layout>
