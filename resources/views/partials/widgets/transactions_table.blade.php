<!--begin::Tables widget 16-->
<div class="card card-flush h-xl-100">
	<!--begin::Header-->
	<div class="card-header pt-5">
		<!--begin::Title-->
		<h3 class="card-title align-items-start flex-column">
			<span class="card-label fw-bold text-gray-800">Transactions</span>
		</h3>
		<!--end::Title-->
		<!--begin::Toolbar-->
		<div class="card-toolbar">
			<!--begin::Menu-->
			<button class="btn btn-icon btn-color-gray-500 btn-active-color-primary justify-content-end" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-overflow="true">{!! getIcon('dots-square', 'fs-1 text-gray-300 me-n1') !!}</button>
			<!--begin::Menu 2-->
			<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px" data-kt-menu="true">
				<!--begin::Menu item-->
				<div class="menu-item px-3">
					<div class="menu-content fs-6 text-gray-900 fw-bold px-3 py-4">Quick Actions</div>
				</div>
				<!--end::Menu item-->
				<!--begin::Menu separator-->
				<div class="separator mb-3 opacity-75"></div>
				<!--end::Menu separator-->
				<!--begin::Menu item-->
				<div class="menu-item px-3">
					<a href="#" class="menu-link px-3">New Ticket</a>
				</div>
				<!--end::Menu item-->
				<!--begin::Menu item-->
				<div class="menu-item px-3">
					<a href="#" class="menu-link px-3">New Customer</a>
				</div>
				<!--end::Menu item-->
				<!--begin::Menu item-->
				<div class="menu-item px-3" data-kt-menu-trigger="hover" data-kt-menu-placement="right-start">
					<!--begin::Menu item-->
					<a href="#" class="menu-link px-3">
						<span class="menu-title">New Group</span>
						<span class="menu-arrow"></span>
					</a>
					<!--end::Menu item-->
					<!--begin::Menu sub-->
					<div class="menu-sub menu-sub-dropdown w-175px py-4">
						<!--begin::Menu item-->
						<div class="menu-item px-3">
							<a href="#" class="menu-link px-3">Admin Group</a>
						</div>
						<!--end::Menu item-->
						<!--begin::Menu item-->
						<div class="menu-item px-3">
							<a href="#" class="menu-link px-3">Staff Group</a>
						</div>
						<!--end::Menu item-->
						<!--begin::Menu item-->
						<div class="menu-item px-3">
							<a href="#" class="menu-link px-3">Member Group</a>
						</div>
						<!--end::Menu item-->
					</div>
					<!--end::Menu sub-->
				</div>
				<!--end::Menu item-->
				<!--begin::Menu item-->
				<div class="menu-item px-3">
					<a href="#" class="menu-link px-3">New Contact</a>
				</div>
				<!--end::Menu item-->
				<!--begin::Menu separator-->
				<div class="separator mt-3 opacity-75"></div>
				<!--end::Menu separator-->
				<!--begin::Menu item-->
				<div class="menu-item px-3">
					<div class="menu-content px-3 py-3">
						<a class="btn btn-primary btn-sm px-4" href="#">Generate Reports</a>
					</div>
				</div>
				<!--end::Menu item-->
			</div>
			<!--end::Menu 2-->
			<!--end::Menu-->
		</div>
		<!--end::Toolbar-->
	</div>
	<!--end::Header-->
	<!--begin::Body-->
	<div class="card-body pt-6">
		<!--begin::Nav-->
		<ul class="nav nav-pills nav-pills-custom mb-3">
			<!--begin::Item-->
			<li class="nav-item mb-3 me-3 me-lg-6">
				<!--begin::Link-->
				<a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden  pt-5 pb-5 active" id="kt_stats_widget_16_tab_link_1" data-bs-toggle="pill" href="#kt_stats_widget_16_tab_1">
					<!--begin::Title-->
					<span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Completed Transactions</span>
					<!--end::Title-->
					<!--begin::Bullet-->
					<span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
					<!--end::Bullet-->
				</a>
				<!--end::Link-->
			</li>
			<!--end::Item-->
			<!--begin::Item-->
			<li class="nav-item mb-3 me-3 me-lg-6">
				<!--begin::Link-->
				<a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden pt-5 pb-5" id="kt_stats_widget_16_tab_link_2" data-bs-toggle="pill" href="#kt_stats_widget_16_tab_2">
					<!--begin::Title-->
					<span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Voided Transactions</span>
					<!--end::Title-->
					<!--begin::Bullet-->
					<span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
					<!--end::Bullet-->
				</a>
				<!--end::Link-->
			</li>
			<!--end::Item-->

		</ul>
		<!--end::Nav-->
		<!--begin::Tab Content-->
		<div class="tab-content">
			<!--begin::Tap pane-->
			<div class="tab-pane fade show active" id="kt_stats_widget_16_tab_1">
				<!--begin::Table container-->
				<div class="table-responsive">
					<!--begin::Table-->
					<table class="table table-bordered align-middle gs-0 gy-3 my-0">
						<!--begin::Table head-->
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
						<!--end::Table head-->
						<!--begin::Table body-->
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
						<!--end::Table body-->
					</table>
					<!--end::Table-->
				</div>
				<!--end::Table container-->
			</div>
			<!--end::Tap pane-->
			<!--begin::Tap pane-->
			<div class="tab-pane fade" id="kt_stats_widget_16_tab_2">
				Voided Transactions goes here
			</div>
		</div>
	</div>
</div>
