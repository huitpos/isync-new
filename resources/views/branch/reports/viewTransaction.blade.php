<x-default-layout>

    @section('title')
        Transaction {{ $transaction->receipt_number }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('branch.reports.viewTransaction', $company, $branch, $transaction) }}
    @endsection

    <div class="row g-1 g-xl-5 mb-1 mb-xl-5">
        <div class="d-flex flex-column gap-7 gap-lg-10">
            <div class="card card-flush py-4 flex-row-fluid overflow-hidden">
                <div class="card-body pt-0">
                    <div class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold">
                        <div class="flex-root d-flex flex-column">
                            <span class="text-muted">Receipt Number</span>
                            <span class="fs-5">{{ $transaction->receipt_number }}</span>
                        </div>

                        <div class="flex-root d-flex flex-column">
                            <span class="text-muted">Control Number</span>
                            <span class="fs-5">{{ $transaction->control_number }}</span>
                        </div>

                        <div class="flex-root d-flex flex-column">
                            <span class="text-muted">Date</span>
                            <span class="fs-5">{{ $transaction->created_at }}</span>
                        </div>

                        <div class="flex-root d-flex flex-column">
                            <span class="text-muted">Cashier</span>
                            <span class="fs-5">{{ $transaction->cashier_name }}</span>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold mt-5">
                        <div class="flex-root d-flex flex-column">
                            <span class="text-muted">Customer Name</span>
                            <span class="fs-5">{{ $transaction->guest_name }}</span>
                        </div>

                        <div class="flex-root d-flex flex-column">
                            <span class="text-muted">Branch</span>
                            <span class="fs-5">{{ $transaction->branch->name }}</span>
                        </div>

                        <div class="flex-root d-flex flex-column">
                            <span class="text-muted">Type</span>
                            <span class="fs-5">{{ $transaction->type }}</span>
                        </div>

                        <div class="flex-root d-flex flex-column">
                            <span class="text-muted">Machine No.</span>
                            <span class="fs-5">{{ $transaction->machine->id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-flush py-4 flex-row-fluid overflow-hidden">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Products</h2>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <!--begin::Table-->
                        <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                            <thead>
                                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                    <th>Product</th>
                                    <th class="text-end">Qty</th>
                                    <th>Uom</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">
                                @foreach($transaction->items as $item)
                                <tr>
                                    <td>
                                        {{ $item->name }}
                                    </td>
                                    <td class="text-end">
                                        {{ $item->qty }}
                                    </td>
                                    <td>
                                        {{ $item->uom->name }}
                                    </td>
                                    <td class="text-end">
                                        ₱ {{ number_format($item->amount, 2) }}
                                    </td>
                                    <td class="text-end">
                                        ₱ {{ number_format($item->gross, 2) }}
                                    </td>
                                </tr>
                                @endforeach

                                <tr>
                                    <td colspan="4" class="fs-3 text-gray-900 text-end">
                                        Total
                                    </td>
                                    <td class="text-gray-900 fs-3 fw-bolder text-end">
                                        ₱ {{ number_format($transaction->gross_sales, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!--end::Table-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-default-layout>
