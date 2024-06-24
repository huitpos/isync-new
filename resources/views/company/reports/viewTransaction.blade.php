<x-default-layout>

    @section('title')
        Transaction {{ $transaction->receipt_number }}
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.reports.viewTransaction', $company, $transaction) }}
    @endsection

    <div class="row g-1 g-xl-5 mb-1 mb-xl-5">
        <div class="d-flex flex-column">
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

            <ul class="nav nav-pills nav-pills-custom mb-1 mt-7">
                <li class="nav-item mb-3 me-3 me-lg-6">
                    <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden  pt-5 pb-5 active" id="kt_stats_widget_16_tab_link_1" data-bs-toggle="pill" href="#products_tab">
                        <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Products</span>
                        <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                    </a>
                </li>

                <li class="nav-item mb-3 me-3 me-lg-6">
                    <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden pt-5 pb-5" id="kt_stats_widget_16_tab_link_2" data-bs-toggle="pill" href="#payments_tab">
                        <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Payments</span>
                        <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                    </a>
                </li>
            </ul>

            <div class="tab-content mt-0">
                <div class="tab-pane fade show active" id="products_tab">
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
                                        @foreach($transaction->nonVoiditems as $item)
                                        <tr>
                                            <td>
                                                {{ $item->name }}
                                            </td>
                                            <td class="text-end">
                                                {{ $item->qty }}
                                            </td>
                                            <td>
                                                {{ $item->uom?->name }}
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

                <div class="tab-pane fade show" id="payments_tab">
                    <div class="card card-flush py-4 flex-row-fluid overflow-hidden">
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Payments</h2>
                            </div>
                        </div>

                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <!--begin::Table-->
                                <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                                    <thead>
                                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                            <th>Type</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600">
                                        @php
                                            $totalPayment = 0;
                                        @endphp
                                        @foreach($transaction->payments as $payment)
                                        @php
                                            $totalPayment += $payment->amount;
                                        @endphp
                                        <tr>
                                            <td>
                                                {{ $payment->payment_type_name }}
                                            </td>
                                            <td class="text-end">
                                                ₱ {{ number_format($payment->amount, 2)}}
                                            </td>
                                        </tr>
                                        @endforeach

                                        <tr>
                                            <td colspan="3" class="fs-3 text-gray-900 text-end">
                                                Total

                                                ₱ {{ number_format($totalPayment, 2) }}
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
        </div>
    </div>
</x-default-layout>
