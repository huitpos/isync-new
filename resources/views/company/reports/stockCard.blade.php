<x-default-layout>

    @section('title')
        Sales Invoices Report
    @endsection

    <div class="card mb-5">
        <div class="card-body py-4">
            <form class="mt-3" method="POST" novalidate>
                @csrf

                <div class="row mb-10">
                    <div class="col-md-6">
                        <label class="form-label">Branch</label>

                        <select id="branch_id" name="branch_id" class="form-select @error('branch') is-invalid @enderror" required>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $branch->id == $branchId ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
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
                            <span class="fw-bold">Actual Stock:</span> {{ $pivotData ? $pivotData->stock : 0 }}
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    @if($product && $product->image)
                    <div class="pb-1 fs-6">
                        <div class="text-gray-600">
                            <div class="image-input-wrapper w-250px h-250px" style="border: 1px dashed #92A0B3; background-size:contain; background-repeat: no-repeat; background-image: url('{{ Storage::disk('s3')->url($product->image) }}'); background-position: center;"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body py-4">
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
                    physicl count1
                </div>

                <div class="tab-pane fade" id="transactions_div">
                    physicl count2
                </div>

                <div class="tab-pane fade" id="incoming_stocks_div">
                    physicl count3
                </div>

                <div class="tab-pane fade" id="transfer_stock_in_div">
                    physicl count4
                </div>

                <div class="tab-pane fade" id="transfer_stock_out_div">
                    physicl count5
                </div>

                <div class="tab-pane fade" id="disposal_div">
                    physicl count6
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            //if branch_id is changed and product_id, reload the page with those values. same url
            document.getElementById('branch_id').addEventListener('change', function() {
                reload();
            });

            $("#product_id").on('change', function(e) {
                reload();
            });

            function reload()
            {
                let branch_id = document.getElementById('branch_id').value;
                let product_id = document.getElementById('product_id').value;

                if (!branch_id || !product_id) {
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

                var url = new URL(window.location.href);

                url.searchParams.set('branch_id', branch_id);
                url.searchParams.set('product_id', product_id);
                window.location.href = url.toString();
            }
        </script>
    @endpush
</x-default-layout>

