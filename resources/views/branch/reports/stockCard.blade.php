<x-default-layout>

    @section('title')
        Stock Card
    @endsection

    <div class="card mb-5">
        <div class="card-body py-4">
            <form class="mt-3" method="POST" novalidate>
                @csrf

                <div class="row mb-10">
                    <div class="col-md-12">
                        <label class="form-label">Product</label>
                        
                        <input type="hidden" id="company_id" value="{{ $company->id }}">
                        <select
                            name="product_id"
                            data-control="select2"
                            data-ajax-url="/ajax/get-products"
                            data-placeholder="{{ $product ? $product->name : 'Select a product' }}"
                            class="form-control @error('company_id') is-invalid @enderror select2-ajax pr_product_id"
                            data-param-name="company_id"
                            data-param-link="#company_id"
                            data-minimum-input="3"
                            required
                            id="product_id"
                        >
                            @if ($product)
                                <option value="{{ $product->id }}" selected>{{ $product->name }}</option>
                            @endif
                        </select>
                    </div>
                </div>
            </form>

            <div class="row mb-5">
                <div class="col-md-10">
                    <div class="row mb-5">
                        <div class="col-md-12">
                            <span class="fw-bold">Product Name:</span> {{ $product ? $product->name : '' }}
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-12">
                            <span class="fw-bold">Product Description:</span> {{ $product ? $product->description : '' }}
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-3">
                            <span class="fw-bold">Item Code:</span> {{ $product ? $product->code : '' }}
                        </div>

                        <div class="col-md-3">
                            <span class="fw-bold">SKU:</span> {{ $product ? $product->sku : '' }}
                        </div>

                        <div class="col-md-3">
                            <span class="fw-bold">Barcode:</span> {{ $product ? $product->barcode : '' }}
                        </div>

                        <div class="col-md-3">
                            <span class="fw-bold">UOM:</span> {{ $product ? $product->uom?->name : '' }}
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-3">
                            <span class="fw-bold">Department:</span> {{ $product ? $product->department?->name : '' }}
                        </div>

                        <div class="col-md-3">
                            <span class="fw-bold">Category:</span> {{ $product ? $product->category?->name : '' }}
                        </div>

                        <div class="col-md-3">
                            <span class="fw-bold">Sub-category:</span> {{ $product ? $product->subcategory?->name : '' }}
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-3">
                            <span class="fw-bold">SRP:</span> {{ $product ? $product->srp : '' }}
                        </div>

                        <div class="col-md-3">
                            <span class="fw-bold">Cost:</span> {{ $product ? $product->cost : '' }}
                        </div>

                        <div class="col-md-3">
                            <span class="fw-bold">Markup:</span> {{ $product ? $product->markup : '' }}
                        </div>

                        <div class="col-md-3">
                            <span class="fw-bold">Markup type:</span> {{ $product ? ucfirst($product->markup_type) : '' }}
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-3">
                            <span class="fw-bold">Min. Stock Level:</span> {{ $product ? $product->minimum_stock_level : '' }}
                        </div>

                        <div class="col-md-3">
                            <span class="fw-bold">Max Stock Level:</span> {{ $product ? $product->maximum_stock_level : '' }}
                        </div>

                        <div class="col-md-3">
                            <span class="fw-bold">Actual Stock:</span> {{ $pivotData ? number_format($pivotData->stock, 2) : 0 }}
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    @if($product && $product->image)
                    <div class="pb-1 fs-6">
                        <div class="text-gray-600">
                            <div class="image-input-wrapper h-250px" style="border: 1px dashed #92A0B3; background-size:contain; background-repeat: no-repeat; background-image: url('{{ Storage::disk('s3')->url($product->image) }}'); background-position: center;"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body py-4">
            <div class="row mb-5">
                <div class="col-md-6">
                    <label class="form-label">Select Date Range:</label>
                    <input id="date_range" 
                            data-selected-range="{{ $selectedRangeParam }}" 
                            data-kt-daterangepicker="true" 
                            data-start-date="{{ $startDateParam }}" 
                            data-end-date="{{ $endDateParam }}" 
                            name="date_range" 
                            type="text" 
                            class="form-control"
                            data-kt-daterangepicker-opens="right"
                        />
                </div>
            </div>
            <ul class="nav nav-pills nav-pills-custom mb-3">
                <li class="nav-item mb-3 me-3 me-lg-6">
                    <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden  pt-5 pb-5 active" id="kt_stats_widget_16_tab_link_1" data-bs-toggle="pill" href="#physical_count_div">
                        <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Physical Count</span>
                        <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                    </a>
                </li>

                <li class="nav-item mb-3 me-3 me-lg-6">
                    <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden  pt-5 pb-5" id="kt_stats_widget_16_tab_link_1" data-bs-toggle="pill" href="#transactions_div">
                        <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Transactions</span>
                        <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                    </a>
                </li>

                <li class="nav-item mb-3 me-3 me-lg-6">
                    <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden  pt-5 pb-5" id="kt_stats_widget_16_tab_link_1" data-bs-toggle="pill" href="#incoming_stocks_div">
                        <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Incoming Stocks</span>
                        <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                    </a>
                </li>

                <li class="nav-item mb-3 me-3 me-lg-6">
                    <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden  pt-5 pb-5" id="kt_stats_widget_16_tab_link_1" data-bs-toggle="pill" href="#transfer_stock_in_div">
                        <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Transfer Stocks (In)</span>
                        <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                    </a>
                </li>

                <li class="nav-item mb-3 me-3 me-lg-6">
                    <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden  pt-5 pb-5" id="kt_stats_widget_16_tab_link_1" data-bs-toggle="pill" href="#transfer_stock_out_div">
                        <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Transfer Stocks (Out)</span>
                        <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                    </a>
                </li>

                <li class="nav-item mb-3 me-3 me-lg-6">
                    <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden  pt-5 pb-5" id="kt_stats_widget_16_tab_link_1" data-bs-toggle="pill" href="#disposal_div">
                        <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Disposal</span>
                        <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="physical_count_div">
                    <table id="physical_count_table" class="table table-striped table-row-bordered gy-5">
                        <thead>
                            <tr class="fw-semibold fs-6 text-muted">
                                <th>Date Count</th>
                                <th>Pcount Number</th>
                                <th>Pcount</th>
                                <th>UOM</th>
                                <th>System Count</th>
                                <th>Variance</th>
                                <th>Count By</th>
                                <th>Approved By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($physicalCounts as $pc)
                                <tr>
                                    <td>{{ $pc->physical_count_date }}</td>
                                    <td>{{ $pc->pcount_number }}</td>
                                    <td>{{ $pc->quantity }}</td>
                                    <td>{{ $pc->uom }}</td>
                                    <td>{{ $pc->old_quantity }}</td>
                                    <td>{{ $pc->quantity - $pc->old_quantity }}</td>
                                    <td>{{ $pc->created_by }}</td>
                                    <td>{{ $pc->action_by }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="transactions_div">
                    <table id="transactions_table" class="table table-striped table-row-bordered gy-5">
                        <thead>
                            <tr class="fw-semibold fs-6 text-muted">
                                <th>Transaction Date</th>
                                <th>Sales Invoice</th>
                                <th>Qty</th>
                                <th>UOM</th>
                                <th>Gross Sales</th>
                                <th>Cost per Unit</th>
                                <th>Net Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $item)
                                <tr>
                                    <td>{{ $item->transaction_date }}</td>
                                    <td>{{ $item->receipt_number }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td>{{ $item->gross }}</td>
                                    <td>{{ $item->cost }}</td>
                                    <td>{{ $item->profit }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="incoming_stocks_div">
                    <table id="incoming_stocks_table" class="table table-striped table-row-bordered gy-5">
                        <thead>
                            <tr class="fw-semibold fs-6 text-muted">
                                <th>Date Received</th>
                                <th>PO No.</th>
                                <th>DP No.</th>
                                <th>Supplier</th>
                                <th>Sales Invoice</th>
                                <th>Qty</th>
                                <th>Cost per Unit</th>
                                <th>Total Cost</th>
                                <th>Received By</th>
                                <th>Approved By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($incomingStocks as $item)
                                <tr>
                                    <td>{{ $item->delivery_date }}</td>
                                    <td>{{ $item->po_number }}</td>
                                    <td>{{ $item->pd_number }}</td>
                                    <td>{{ $item->supplier }}</td>
                                    <td>{{ $item->sales_invoice_number }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>{{ $item->unit_price }}</td>
                                    <td>{{ $item->unit_price * $item->qty }}</td>
                                    <td>{{ $item->created_by }}</td>
                                    <td>{{ $item->action_by }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="transfer_stock_in_div">
                    <table id="stock_transfer_in_table" class="table table-striped table-row-bordered gy-5">
                        <thead>
                            <tr class="fw-semibold fs-6 text-muted">
                                <th>Date Received</th>
                                <th>STO No.</th>
                                <th>STD No.</th>
                                <th>Branch Source</th>
                                <th>Qty</th>
                                <th>UOM</th>
                                <th>Cost per Unit</th>
                                <th>Total Cost</th>
                                <th>Request By</th>
                                <th>Approved By</th>
                                <th>Received By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockTransferIn as $item)
                                <tr>
                                    <td>{{ $item->delivery_date }}</td>
                                    <td>{{ $item->sto_number }}</td>
                                    <td>{{ $item->std_number }}</td>
                                    <td>{{ $item->source_branch }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>{{ $item->uom }}</td>
                                    <td>{{ $item->cost }}</td>
                                    <td>{{ $item->cost * $item->qty }}</td>
                                    <td>{{ $item->requested_by }}</td>
                                    <td>{{ $item->approved_by }}</td>
                                    <td>{{ $item->received_by }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="transfer_stock_out_div">
                    <table id="stock_transfer_out_table" class="table table-striped table-row-bordered gy-5">
                        <thead>
                            <tr class="fw-semibold fs-6 text-muted">
                                <th>Date Received</th>
                                <th>STO No.</th>
                                <th>STD No.</th>
                                <th>Branch Destination</th>
                                <th>Qty</th>
                                <th>UOM</th>
                                <th>Cost per Unit</th>
                                <th>Total Cost</th>
                                <th>Request By</th>
                                <th>Approved By</th>
                                <th>Received By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockTransferOut as $item)
                                <tr>
                                    <td>{{ $item->delivery_date }}</td>
                                    <td>{{ $item->sto_number }}</td>
                                    <td>{{ $item->std_number }}</td>
                                    <td>{{ $item->destination_branch }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>{{ $item->uom }}</td>
                                    <td>{{ $item->cost }}</td>
                                    <td>{{ $item->cost * $item->qty }}</td>
                                    <td>{{ $item->requested_by }}</td>
                                    <td>{{ $item->approved_by }}</td>
                                    <td>{{ $item->received_by }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="disposal_div">
                    <table id="disposal_table" class="table table-striped table-row-bordered gy-5">
                        <thead>
                            <tr class="fw-semibold fs-6 text-muted">
                                <th>Date</th>
                                <th>Disposal No.</th>
                                <th>Qty</th>
                                <th>UOM</th>
                                <th>Cost per Unit</th>
                                <th>Total Cost</th>
                                <th>Reason</th>
                                <th>Request By</th>
                                <th>Approved By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($disposals as $item)
                                <tr>
                                    <td>{{ $item->date }}</td>
                                    <td>{{ $item->pdis_number }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->uom }}</td>
                                    <td>{{ $item->cost }}</td>
                                    <td>{{ $item->cost * $item->quantity }}</td>
                                    <td>{{ $item->reason }}</td>
                                    <td>{{ $item->created_by }}</td>
                                    <td>{{ $item->action_by }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $("#physical_count_table").DataTable();
            $("#transactions_table").DataTable();
            $("#incoming_stocks_table").DataTable();
            $("#stock_transfer_in_table").DataTable();
            $("#stock_transfer_out_table").DataTable();
            $("#disposal_table").DataTable();

            $("#product_id").on('change', function(e) {
                reload();
            });

            function reload()
            {
                let product_id = document.getElementById('product_id').value;

                if (!product_id) {
                    return;
                }

                const loadingEl = document.createElement("div");
                document.body.prepend(loadingEl);
                loadingEl.classList.add("page-loader");
                loadingEl.classList.add("flex-column");
                loadingEl.classList.add("bg-dark");
                loadingEl.classList.add("bg-opacity-50");
                loadingEl.innerHTML = `
                    <span class="spinner-border text-primary" role="status"></span>
                    <span class="text-white-800 fs-6 fw-semibold mt-5">Loading...</span>
                `;

                // Show page loading
                KTApp.showPageLoading();

                const selectedRange = $("#date_range").attr("data-selected-range");
                const startDate = $("#date_range").attr("data-start-date");
                const endDate = $("#date_range").attr("data-end-date");

                var url = new URL(window.location.href);

                url.searchParams.set('product_id', product_id);

                url.searchParams.set('selectedRange', selectedRange);
                url.searchParams.set('startDate', startDate);
                url.searchParams.set('endDate', endDate); 

                const dateRange = document.getElementById('date_range');
                const dateValue = dateRange.value;
                if (dateValue) {
                    url.searchParams.set('date_range', dateValue);
                } else {
                    url.searchParams.delete('date_range');
                }

                window.location.href = url.toString();
            }

            document.addEventListener('DOMContentLoaded', (event) => {
                $("#date_range").on("change.datetimepicker", ({date, oldDate}) => {
                    reload()
                });
            });
        </script>
    @endpush
</x-default-layout>

