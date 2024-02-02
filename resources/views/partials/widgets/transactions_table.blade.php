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
			<li class="nav-item mb-3 me-3 me-lg-6">
				<a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden  pt-5 pb-5 active" id="kt_stats_widget_16_tab_link_1" data-bs-toggle="pill" href="#kt_stats_widget_16_tab_1">
					<span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Completed Transactions</span>
					<span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
				</a>
			</li>

			<li class="nav-item mb-3 me-3 me-lg-6">
				<a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden pt-5 pb-5" id="kt_stats_widget_16_tab_link_2" data-bs-toggle="pill" href="#pending_transactions_tab">
					<span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Pending Transactions</span>

					<span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
				</a>
			</li>

			<li class="nav-item mb-3 me-3 me-lg-6">
				<a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden pt-5 pb-5" id="kt_stats_widget_16_tab_link_2" data-bs-toggle="pill" href="#kt_stats_widget_16_tab_2">
					<span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Voided Transactions</span>

					<span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
				</a>
			</li>

		</ul>

		<div class="tab-content">
			<div class="tab-pane fade show active" id="kt_stats_widget_16_tab_1">
				<div class="table-responsive">
					<table class="table table-bordered align-middle gs-0 gy-3 my-0">
						<thead>
							<tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
								<th class="p-0 pb-3 text-start">Date</th>
								<th class="p-0 pb-3 text-start">Machine No.</th>
								<th class="p-0 pb-3 text-start">OR No.</th>
								<th class="p-0 pb-3 text-start">Cashier</th>
								<th class="p-0 pb-3 text-start">Shift</th>
								<th class="p-0 pb-3 text-start">Gross Sales</th>
								<th class="p-0 pb-3 text-start">Net Sales</th>
								<th class="p-0 pb-3 text-start">Vat Sales</th>
								<th class="p-0 pb-3 text-start">Vat Amount</th>
								<th class="p-0 pb-3 text-start">Vat Exempt</th>
								<th class="p-0 pb-3 text-start">Discount</th>
								<th class="p-0 pb-3 text-start">Type of Payment</th>
								<th class="p-0 pb-3 text-start">Paid Amount</th>
								<th class="p-0 pb-3 text-start">Change</th>
							</tr>
						</thead>

						<tbody>
							@foreach ($completedTransactions as $completedTransaction)
							<tr>
								<td class="text-start pe-13">
									<span class="text-gray-600">{{ $completedTransaction->completed_at }}</span>
								</td>

								<td class="text-start pe-13">
									<span class="text-gray-600">{{ $completedTransaction->pos_machine_id }}</span>
								</td>

								<td class="text-start pe-13">
									<span class="text-gray-600">{{ $completedTransaction->receipt_number }} </span>
								</td>

								<td class="text-start pe-13">
									<span class="text-gray-600">{{ $completedTransaction->cashier_name }} </span>
								</td>

								<td class="text-start pe-13">
									<span class="text-gray-600">{{ $completedTransaction->shift_number }} </span>
								</td>

								<td class="text-start pe-13">
									<span class="text-gray-600">{{ $completedTransaction->gross_sales }} </span>
								</td>

								<td class="text-start pe-13">
									<span class="text-gray-600">{{ $completedTransaction->net_sales }} </span>
								</td>

								<td class="text-start pe-13">
									<span class="text-gray-600">{{ $completedTransaction->vat_amount }} </span>
								</td>

								<td class="text-start pe-13">
									<span class="text-gray-600">{{ $completedTransaction->vatable_sales }} </span>
								</td>

								<td class="text-start pe-13">
									<span class="text-gray-600">{{ $completedTransaction->vat_excempt_sales }} </span>
								</td>

								<td class="text-start pe-13">
									<span class="text-gray-600">{{ $completedTransaction->discount_amount ?? 0 }} </span>
								</td>

								<td class="text-start pe-13">
									<span class="text-gray-600">Cash</span>
								</td>

								<td class="text-start pe-13">
									<span class="text-gray-600">{{ $completedTransaction->tender_amount ?? 0 }} </span>
								</td>

								<td class="text-start pe-13">
									<span class="text-gray-600">{{ $completedTransaction->change ?? 0 }} </span>
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>

			<div class="tab-pane fade" id="pending_transactions_tab">
				@if (isset($pendingTransactions) && $pendingTransactions->count() > 0)
					<div class="table-responsive">
						<table class="table table-bordered align-middle gs-0 gy-3 my-0">
							<thead>
								<tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
									<th class="p-0 pb-3 text-start">Date</th>
									<th class="p-0 pb-3 text-start">Machine No.</th>
									<th class="p-0 pb-3 text-start">OR No.</th>
									<th class="p-0 pb-3 text-start">Cashier</th>
									<th class="p-0 pb-3 text-start">Shift</th>
									<th class="p-0 pb-3 text-start">Gross Sales</th>
									<th class="p-0 pb-3 text-start">Net Sales</th>
									<th class="p-0 pb-3 text-start">Vat Sales</th>
									<th class="p-0 pb-3 text-start">Vat Amount</th>
									<th class="p-0 pb-3 text-start">Vat Exempt</th>
									<th class="p-0 pb-3 text-start">Discount</th>
									<th class="p-0 pb-3 text-start">Type of Payment</th>
									<th class="p-0 pb-3 text-start">Paid Amount</th>
									<th class="p-0 pb-3 text-start">Change</th>
								</tr>
							</thead>

							<tbody>
								@foreach ($pendingTransactions as $pendingTransaction)
								<tr>
									<td class="text-start pe-13">
										<span class="text-gray-600">{{ $pendingTransaction->completed_at }}</span>
									</td>

									<td class="text-start pe-13">
										<span class="text-gray-600">{{ $pendingTransaction->pos_machine_id }}</span>
									</td>

									<td class="text-start pe-13">
										<span class="text-gray-600">{{ $pendingTransaction->receipt_number }} </span>
									</td>

									<td class="text-start pe-13">
										<span class="text-gray-600">{{ $pendingTransaction->cashier_name }} </span>
									</td>

									<td class="text-start pe-13">
										<span class="text-gray-600">{{ $pendingTransaction->shift_number }} </span>
									</td>

									<td class="text-start pe-13">
										<span class="text-gray-600">{{ $pendingTransaction->gross_sales }} </span>
									</td>

									<td class="text-start pe-13">
										<span class="text-gray-600">{{ $pendingTransaction->net_sales }} </span>
									</td>

									<td class="text-start pe-13">
										<span class="text-gray-600">{{ $pendingTransaction->vat_amount }} </span>
									</td>

									<td class="text-start pe-13">
										<span class="text-gray-600">{{ $pendingTransaction->vatable_sales }} </span>
									</td>

									<td class="text-start pe-13">
										<span class="text-gray-600">{{ $pendingTransaction->vat_excempt_sales }} </span>
									</td>

									<td class="text-start pe-13">
										<span class="text-gray-600">{{ $pendingTransaction->discount_amount ?? 0 }} </span>
									</td>

									<td class="text-start pe-13">
										<span class="text-gray-600">Cash</span>
									</td>

									<td class="text-start pe-13">
										<span class="text-gray-600">{{ $pendingTransaction->tender_amount ?? 0 }} </span>
									</td>

									<td class="text-start pe-13">
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

			<div class="tab-pane fade" id="kt_stats_widget_16_tab_2">
				<div class="d-flex justify-content-center align-items-center">
					<div class="d-flex flex-column justify-content-center align-items-center">
						<span class="text-gray-600 fw-bold fs-6">Voided transactions goes here</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
