@php
	$permissions = request()->attributes->get('permissionNames');
@endphp

<!--begin::Tables widget 16-->
<div class="card card-flush h-xl-100">
	<!--begin::Header-->
	<div class="card-header pt-5">
		<!--begin::Title-->
		<h3 class="card-title align-items-start flex-column">
			<span class="card-label fw-bold text-gray-800">Transactions</span>
		</h3>
	</div>

	<div class="card-body pt-1">
		<ul class="nav nav-pills nav-pills-custom mb-3">
			@if (in_array('Main Dashboard/Transaction/Completed Transactions', $permissions))
			<li class="nav-item mb-3 me-3 me-lg-6">
				<a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden  pt-5 pb-5 active" id="kt_stats_widget_16_tab_link_1" data-bs-toggle="pill" href="#kt_stats_widget_16_tab_1">
					<span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Completed Transactions</span>
					<span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
				</a>
			</li>
			@endif

			@if (in_array('Main Dashboard/Transaction/Pending Transactions', $permissions))
				<li class="nav-item mb-3 me-3 me-lg-6">
					<a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden pt-5 pb-5 {{ (!in_array('Main Dashboard/Transaction/Completed Transactions', $permissions)) ? 'active' : '' }}" id="kt_stats_widget_16_tab_link_2" data-bs-toggle="pill" href="#pending_transactions_tab">
						<span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Pending Transactions</span>

						<span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
					</a>
				</li>
			@endif

			@if (in_array('Main Dashboard/Transaction/Voided Transactions', $permissions))
				<li class="nav-item mb-3 me-3 me-lg-6">
					<a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden pt-5 pb-5 {{ (!in_array('Main Dashboard/Transaction/Completed Transactions', $permissions) && !in_array('Main Dashboard/Transaction/Pending Transactions', $permissions)) ? 'active' : '' }}" id="kt_stats_widget_16_tab_link_2" data-bs-toggle="pill" href="#kt_stats_widget_16_tab_2">
						<span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Voided Transactions</span>

						<span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
					</a>
				</li>
			@endif

		</ul>

		<div class="tab-content">
			@if (in_array('Main Dashboard/Transaction/Completed Transactions', $permissions))
				<div class="tab-pane fade show active" id="kt_stats_widget_16_tab_1">
					<div class="table-responsive">
						<table class="table table-bordered align-middle gs-0 gy-3 my-0">
							<thead>
								<tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
									<th class="text-start">Date</th>
									@if (isset($addBranch) && $addBranch)
										<th class="text-start">Branch</th>
									@endif
									<th class="text-start">Machine No.</th>
									<th class="text-start">OR No.</th>
									<th class="text-start">Cashier</th>
									<th class="text-start">Shift</th>
									<th class="text-end">Gross Sales</th>
									<th class="text-end">Net Sales</th>
									<th class="text-end">Vat Amount</th>
									<th class="text-end">Vat Sales</th>
									<th class="text-end">Vat Exempt</th>
									<th class="text-end">Discount</th>
									<th class="text-start">Type of Payment</th>
									<th class="text-end">Paid Amount</th>
									<th class="text-end">Change</th>
								</tr>
							</thead>

							<tbody>
								@foreach ($completedTransactions as $completedTransaction)
								<tr>
									<td class="text-start">
										<span class="text-gray-600">{{ $completedTransaction->treg }}</span>
									</td>

									@if (isset($addBranch) && $addBranch)
										<td class="text-start">
											<span class="text-gray-600">{{ $completedTransaction->branch->name }}</span>
										</td>
									@endif

									<td class="text-start">
										<span class="text-gray-600">{{ $completedTransaction->machine?->machine_number }}</span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">
											<a target="_blank" href="{{ !empty($transactionRoute) ? route($transactionRoute, [
													'companySlug' => $completedTransaction->branch->company->slug,
													'transactionId' => $completedTransaction->id,
													'branchSlug' => $completedTransaction->branch->slug,
												]) : '#' }}"
											>
												{{ $completedTransaction->receipt_number }}
											</a>
										</span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">{{ $completedTransaction->cashier_name }} </span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">{{ $completedTransaction->shift_number }} </span>
									</td>

									<td class="text-end">
										<span class="text-gray-600">{{ number_format($completedTransaction->gross_sales, 4) }} </span>
									</td>

									<td class="text-end">
										<span class="text-gray-600">{{ number_format($completedTransaction->net_sales, 4) }} </span>
									</td>

									<td class="text-end">
										<span class="text-gray-600">{{ number_format($completedTransaction->vat_amount, 4) }} </span>
									</td>

									<td class="text-end">
										<span class="text-gray-600">{{ number_format($completedTransaction->vatable_sales, 4) }} </span>
									</td>

									<td class="text-end">
										<span class="text-gray-600">{{ number_format($completedTransaction->vat_exempt_sales, 4) }} </span>
									</td>

									<td class="text-end">
										<span class="text-gray-600">{{ number_format($completedTransaction->discount_amount ?? 0, 4) }} </span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">Cash</span>
									</td>

									<td class="text-end">
										<span class="text-gray-600">{{ number_format($completedTransaction->tender_amount ?? 0, 4) }} </span>
									</td>

									<td class="text-end">
										<span class="text-gray-600">{{ number_format($completedTransaction->change ?? 0, 4) }} </span>
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			@endif

			@if (in_array('Main Dashboard/Transaction/Pending Transactions', $permissions))
			<div class="tab-pane fade {{ (!in_array('Main Dashboard/Transaction/Completed Transactions', $permissions)) ? 'show active' : '' }}" id="pending_transactions_tab">
				@if (isset($pendingTransactions) && $pendingTransactions->count() > 0)
					<div class="table-responsive">
						<table class="table table-bordered align-middle gs-0 gy-3 my-0">
							<thead>
								<tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
									<th class="text-start">Date</th>
									<th class="text-start">Machine No.</th>
									<th class="text-start">OR No.</th>
									<th class="text-start">Cashier</th>
									<th class="text-start">Shift</th>
									<th class="text-start">Gross Sales</th>
									<th class="text-start">Net Sales</th>
									<th class="text-start">Vat Sales</th>
									<th class="text-start">Vat Amount</th>
									<th class="text-start">Vat Exempt</th>
									<th class="text-start">Discount</th>
									<th class="text-start">Type of Payment</th>
									<th class="text-start">Paid Amount</th>
									<th class="text-start">Change</th>
								</tr>
							</thead>

							<tbody>
								@foreach ($pendingTransactions as $pendingTransaction)
								<tr>
									<td class="text-start">
										<span class="text-gray-600">{{ $pendingTransaction->treg }}</span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">{{ $pendingTransaction->machine?->machine_number }}</span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">{{ $pendingTransaction->receipt_number }} </span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">{{ $pendingTransaction->cashier_name }} </span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">{{ $pendingTransaction->shift_number }} </span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">{{ $pendingTransaction->gross_sales }} </span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">{{ $pendingTransaction->net_sales }} </span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">{{ $pendingTransaction->vat_amount }} </span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">{{ $pendingTransaction->vatable_sales }} </span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">{{ $pendingTransaction->vat_exempt_sales }} </span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">{{ $pendingTransaction->discount_amount ?? 0 }} </span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">Cash</span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">{{ $pendingTransaction->tender_amount ?? 0 }} </span>
									</td>

									<td class="text-start">
										<span class="text-gray-600">{{ $pendingTransaction->change ?? 0 }} </span>
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				@else
				<div class="d-flex justify-content-center align-items-center">
					<div class="d-flex flex-column justify-content-center align-items-center">
						<span class="text-gray-600 fw-bold fs-6">No Pending Transactions</span>
					</div>
				</div>
				@endif
			</div>
			@endif

			@if (in_array('Main Dashboard/Transaction/Voided Transactions', $permissions))
			<div class="tab-pane fade {{ (!in_array('Main Dashboard/Transaction/Completed Transactions', $permissions) && !in_array('Main Dashboard/Transaction/Pending Transactions', $permissions)) ? 'show active' : '' }}" id="kt_stats_widget_16_tab_2">
				<div class="d-flex justify-content-center align-items-center">
					<div class="d-flex flex-column justify-content-center align-items-center">
						<span class="text-gray-600 fw-bold fs-6">Voided transactions goes here</span>
					</div>
				</div>
			</div>
			@endif
		</div>
	</div>
</div>
