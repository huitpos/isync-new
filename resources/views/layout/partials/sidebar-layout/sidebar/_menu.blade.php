@php
	$permissions = request()->attributes->get('permissionNames');
	$companyPermissionCount = request()->attributes->get('companyPermissionCount');
	$branchPermissionCount = request()->attributes->get('branchPermissionCount');
@endphp



<div class="app-sidebar-menu overflow-hidden flex-column-fluid">
	<div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper hover-scroll-overlay-y my-5" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer" data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">
		<div class="menu menu-column menu-rounded menu-sub-indention px-3 fw-semibold fs-6" id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">
			@if (request()->segment(1) == 'admin')
				<div class="menu-item">
					<a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
						<span class="menu-icon">
							<i class="fa-solid fa-chart-line fs-2"></i>
						</span>
						<span class="menu-title">Dashboard</span>
					</a>
				</div>

				<div class="menu-item">
					<a class="menu-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}" href="{{ route('admin.clients.index') }}">
						<span class="menu-icon">
							<i class="fa-solid fa-address-card fs-2"></i>
						</span>
						<span class="menu-title">Clients</span>
					</a>
				</div>

				<div class="menu-item">
					<a class="menu-link {{ request()->routeIs('admin.clusters.*') ? 'active' : '' }}" href="{{ route('admin.clusters.index') }}">
						<span class="menu-icon">
							<i class="fa-solid fa-circle-nodes fs-2"></i>
						</span>
						<span class="menu-title">Clusters</span>
					</a>
				</div>

				<div class="menu-item">
					<a class="menu-link {{ request()->routeIs('admin.branches.*', 'admin.machines.*') ? 'active' : '' }}" href="{{ route('admin.branches.index') }}">
						<span class="menu-icon">
							<i class="fa-solid fa-shop fs-2"></i>
						</span>
						<span class="menu-title">Branches</span>
					</a>
				</div>
			@endif

			@if (request()->attributes->get('branch') || request()->attributes->get('company'))
			<div class="mb-5">
				<button type="button" class="btn btn-primary rotate w-100 btn-trim-end" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start" data-kt-menu-offset="10px, 10px">
					{{ request()->attributes->get('branch') ? request()->attributes->get('branch')->name : request()->attributes->get('company')->company_name }}
					<i class="ki-duotone ki-down fs-3 rotate-180 ms-3 me-0"></i>
				</button>

				<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-primary fw-semibold w-auto min-w-300px mw-300px" data-kt-menu="true">
					@if ($companyPermissionCount > 0)
					<div class="menu-item mt-2">
						<a href="{{ route('company.dashboard', ['companySlug' => request()->attributes->get('company')->slug]) }}" class="menu-link p-2">
							{{ request()->attributes->get('company')->company_name }}
						</a>
					</div>

					<div class="separator mb-3 opacity-75"></div>
					@endif

					@php
						$branches = auth()->user()->activeBranches;
					@endphp

					@if ($branches->count() > 1)
						<label class="form-label fw-semibold p-2">Branches:</label>
					@endif

					@foreach($branches as $branch)
						<div class="menu-item p-0">
							<a href="{{ route('branch.dashboard', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => $branch->slug]) }}" class="menu-link p-2 mb-1">
								{{ $branch->name }}
							</a>
						</div>
					@endforeach
				</div>
			</div>
			@endif

			@if (request()->attributes->get('branch'))
				<div class="menu-item">
					<a class="menu-link {{ request()->routeIs('branch.dashboard') ? 'active' : '' }}" href="{{ route('branch.dashboard', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
						<span class="menu-icon">
							<i class="fa-solid fa-chart-line fs-2"></i>
						</span>
						<span class="menu-title">Dashboard</span>
					</a>
				</div>

				<div class="menu-item">
					<a class="menu-link {{ request()->routeIs('branch.users.*') ? 'active' : '' }}" href="{{ route('branch.users.index', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
						<span class="menu-icon">
							<i class="fa-solid fa-users fs-2"></i>
						</span>
						<span class="menu-title">Users</span>
					</a>
				</div>

				<div class="menu-item">
					<a class="menu-link {{ request()->routeIs('branch.delivery-locations.*') ? 'active' : '' }}" href="{{ route('branch.delivery-locations.index', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
						<span class="menu-icon">
							<i class="fa-solid fa-map-location-dot fs-2"></i>
						</span>
						<span class="menu-title">Delivery Locations</span>
					</a>
				</div>

				<div data-kt-menu-trigger="click" class="menu-item menu-accordion
					{{ request()->routeIs(
						'branch.purchase-requests.*',
						'branch.purchase-orders.*',
						'branch.purchase-deliveries.*',
						'branch.purchase-delivery.*',
					) ? 'here show' : '' }}"
				>
					<span class="menu-link">
						<span class="menu-icon"><i class="fa-solid fa-boxes-stacked fs-2"></i></span>
						<span class="menu-title">Procurement</span>
						<span class="menu-arrow"></span>
					</span>

					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.purchase-requests.*') ? 'active' : '' }}" href="{{ route('branch.purchase-requests.index', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Purchase Requests</span>
							</a>
						</div>

						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.purchase-orders.*') ? 'active' : '' }}" href="{{ route('branch.purchase-orders.index', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Purchase Orders</span>
							</a>
						</div>

						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.purchase-deliveries.*') || request()->routeIs('branch.purchase-delivery.*') ? 'active' : '' }}" href="{{ route('branch.purchase-deliveries.index', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Purchase Deliveries</span>
							</a>
						</div>
					</div>
				</div>

				<div data-kt-menu-trigger="click" class="menu-item menu-accordion
					{{ request()->routeIs(
						'branch.stock-transfer-requests.*',
						'branch.stock-transfer-orders.*',
						'branch.stock-transfer-deliveries.*',
						'branch.product-physical-counts.*',
						'branch.product-disposals.*',
					) ? 'here show' : '' }}"
				>
					<span class="menu-link">
						<span class="menu-icon"><i class="fa-solid fa-truck-moving fs-2"></i></span>
						<span class="menu-title">Inventory</span>
						<span class="menu-arrow"></span>
					</span>

					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.stock-transfer-requests.*') ? 'active' : '' }}" href="{{ route('branch.stock-transfer-requests.index', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Stock Transfer Requests</span>
							</a>
						</div>

						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.stock-transfer-orders.*') ? 'active' : '' }}" href="{{ route('branch.stock-transfer-orders.index', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Stock Transfer Orders</span>
							</a>
						</div>

						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.stock-transfer-deliveries.*') ? 'active' : '' }}" href="{{ route('branch.stock-transfer-deliveries.index', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Stock Transfer Deliveries</span>
							</a>
						</div>

						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.product-physical-counts.*') ? 'active' : '' }}" href="{{ route('branch.product-physical-counts.index', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Product Physical Count</span>
							</a>
						</div>

						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.product-disposals.*') ? 'active' : '' }}" href="{{ route('branch.product-disposals.index', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Product Disposals</span>
							</a>
						</div>
					</div>
				</div>

				<div class="menu-item">
					<a class="menu-link {{ request()->routeIs('branch.products.*') ? 'active' : '' }}" href="{{ route('branch.products.index', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
						<span class="menu-icon">
							<i class="fa-solid fa-table-list fs-2"></i>
						</span>
						<span class="menu-title">Products</span>
					</a>
				</div>

				<div data-kt-menu-trigger="click" class="menu-item menu-accordion
					{{ request()->routeIs(
						'branch.reports.*',
					) ? 'here show' : '' }}"
				>
					<span class="menu-link">
						<span class="menu-icon"><i class="fa-solid fa-chart-simple fs-2"></i></span>
						<span class="menu-title">Reports</span>
						<span class="menu-arrow"></span>
					</span>

					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.reports.sales-invoices-report') ? 'active' : '' }}" href="{{ route('branch.reports.sales-invoices-report', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Sales Invoices Report</span>
							</a>
						</div>
					</div>

					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.reports.sales-transaction-report') ? 'active' : '' }}" href="{{ route('branch.reports.sales-transaction-report', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Sales Transaction Report</span>
							</a>
						</div>
					</div>

					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.reports.void-transactions-report') ? 'active' : '' }}" href="{{ route('branch.reports.void-transactions-report', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Void Transactions Report</span>
							</a>
						</div>
					</div>

					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.reports.vat-sales-report') ? 'active' : '' }}" href="{{ route('branch.reports.vat-sales-report', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Vat Sales Report</span>
							</a>
						</div>
					</div>

					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.reports.x-reading-report') ? 'active' : '' }}" href="{{ route('branch.reports.x-reading-report', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">X Reading Report</span>
							</a>
						</div>
					</div>

					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.reports.z-reading-report') ? 'active' : '' }}" href="{{ route('branch.reports.z-reading-report', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Z Reading Report</span>
							</a>
						</div>
					</div>

					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.reports.discounts-report') ? 'active' : '' }}" href="{{ route('branch.reports.discounts-report', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Discounts Report</span>
							</a>
						</div>

						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.reports.item-sales-report') ? 'active' : '' }}" href="{{ route('branch.reports.item-sales-report', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Item Sales Report</span>
							</a>
						</div>

						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('branch.reports.stock-card') ? 'active' : '' }}" href="{{ route('branch.reports.stock-card', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
								<span class="menu-title">Stock Card</span>
							</a>
						</div>
					</div>
				</div>

				<div class="menu-item">
					<a class="menu-link {{ request()->routeIs('branch.charge-accounts.*') ? 'active' : '' }}" href="{{ route('branch.charge-accounts.index', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
						<span class="menu-title">Charge Accounts</span>
					</a>
				</div>

			@elseif (request()->attributes->get('company'))
				@if (in_array('Main Dashboard', $permissions))
				<div class="menu-item">
					<a class="menu-link {{ request()->routeIs('company.dashboard') ? 'active' : '' }}" href="{{ route('company.dashboard', ['companySlug' => request()->attributes->get('company')->slug]) }}">
						<span class="menu-icon">
							<i class="fa-solid fa-chart-line fs-2"></i>
						</span>
						<span class="menu-title">Dashboard</span>
					</a>
				</div>
				@endif

				@if (in_array('Inventory', $permissions))
				<div data-kt-menu-trigger="click" class="menu-item menu-accordion
					{{ request()->routeIs(
						'company.branch-inventory.*',
						'company.product-physical-counts.*',
						'company.product-disposals.*',
					) ? 'here show' : '' }}"
				>
					<span class="menu-link">
						<span class="menu-icon"><i class="fa-solid fa-warehouse fs-2"></i></span>
						<span class="menu-title">Inventory</span>
						<span class="menu-arrow"></span>
					</span>

					<!--products-->
					@if (isset($branches[0]['id']))
						<div class="menu-sub menu-sub-accordion">
							@if (in_array('Inventory/Products', $permissions))
							<div class="menu-item">
								<a class="menu-link {{ request()->routeIs('company.branch-inventory.index') ? 'active' : '' }}" href="{{ route('company.branch-inventory.index', ['companySlug' => request()->attributes->get('company')->slug, 'branchId' => $branches[0]['id']]) }}">
									<span class="menu-title">Products</span>
								</a>
							</div>
							@endif

							@if (in_array('Inventory/Product Disposal', $permissions))
							<div class="menu-item">
								<a class="menu-link {{ request()->routeIs('company.product-disposals.index') ? 'active' : '' }}" href="{{ route('company.product-disposals.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
									<span class="menu-title">Product Disposals</span>
								</a>
							</div>
							@endif

							@if (in_array('Inventory/Product Physical Count', $permissions))
							<div class="menu-item">
								<a class="menu-link {{ request()->routeIs('company.product-physical-counts.index') ? 'active' : '' }}" href="{{ route('company.product-physical-counts.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
									<span class="menu-title">Product Physical Count</span>
								</a>
							</div>
							@endif
						</div>
					@endif
				</div>
				@endif

				@if (in_array('Procurement', $permissions))
				<div data-kt-menu-trigger="click" class="menu-item menu-accordion
					{{ request()->routeIs(
						'company.purchase-requests.*',
						'company.purchase-orders.*',
						'company.purchase-deliveries.*',
						'company.stock-transfer-requests.*',
					) ? 'here show' : '' }}"
				>
					<span class="menu-link">
						<span class="menu-icon"><i class="fa-solid fa-boxes-stacked fs-2"></i></span>
						<span class="menu-title">Procurement</span>
						<span class="menu-arrow"></span>
					</span>

					<div class="menu-sub menu-sub-accordion">
						@if (in_array('Procurement/Purchase Requests', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.purchase-requests.*') ? 'active' : '' }}" href="{{ route('company.purchase-requests.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Purchase Requests</span>
							</a>
						</div>
						@endif

						@if (in_array('Procurement/Purchase Orders', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.purchase-orders.*') ? 'active' : '' }}" href="{{ route('company.purchase-orders.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Purchase Orders</span>
							</a>
						</div>
						@endif

						@if (in_array('Procurement/Purchase Deliveries', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.purchase-deliveries.*') ? 'active' : '' }}" href="{{ route('company.purchase-deliveries.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Purchase Deliveries</span>
							</a>
						</div>
						@endif

						@if (in_array('Procurement/Stock Transfer Requests', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.stock-transfer-requests.*') ? 'active' : '' }}" href="{{ route('company.stock-transfer-requests.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Stock Transfer Requests</span>
							</a>
						</div>
						@endif
					</div>
				</div>
				@endif

				@if (in_array('Company Access Level', $permissions))
				<div data-kt-menu-trigger="click" class="menu-item menu-accordion
					{{ request()->routeIs(
						'company.users.*',
						'company.roles.*',
					) ? 'here show' : '' }}"
				>
					<span class="menu-link">
						<span class="menu-icon"><i class="fa-solid fa-id-card fs-2"></i></span>
						<span class="menu-title">Access Level</span>
						<span class="menu-arrow"></span>
					</span>

					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.users.*') ? 'active' : '' }}" href="{{ route('company.users.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Users</span>
							</a>
						</div>

						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.roles.*') ? 'active' : '' }}" href="{{ route('company.roles.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Roles</span>
							</a>
						</div>
					</div>
				</div>
				@endif

				@if (in_array('Settings', $permissions))
				<div data-kt-menu-trigger="click" class="menu-item menu-accordion
					{{ request()->routeIs(
						'company.departments.*',
						'company.suppliers.*',
						'company.categories.*',
						'company.subcategories.*',
						'company.item-types.*',
						'company.unit-of-measurements.*',
						'company.discount-types.*',
						'company.charge-accounts.*',
						'company.payment-types.*',
						'company.payment-terms.*',
						'company.supplier-terms.*',
						'company.products.*',
						'company.product-disposal-reasons.*',
					) ? 'here show' : '' }}"
				>
					<span class="menu-link">
						<span class="menu-icon"><i class="fa-solid fa-sliders fs-2"></i></span>
						<span class="menu-title">Settings</span>
						<span class="menu-arrow"></span>
					</span>

					<!--departments-->
					<div class="menu-sub menu-sub-accordion">
						@if (in_array('Settings/Products', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.products.*') ? 'active' : '' }}" href="{{ route('company.products.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Products</span>
							</a>
						</div>
						@endif

						@if (in_array('Settings/Payment Types', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.payment-types.*') ? 'active' : '' }}" href="{{ route('company.payment-types.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Payment Types</span>
							</a>
						</div>
						@endif

						@if (in_array('Settings/Departments', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.departments.*') ? 'active' : '' }}" href="{{ route('company.departments.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Departments</span>
							</a>
						</div>
						@endif

						@if (in_array('Settings/Categories', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.categories.*') ? 'active' : '' }}" href="{{ route('company.categories.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Categories</span>
							</a>
						</div>
						@endif

						@if (in_array('Settings/Sub-Categories', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.subcategories.*') ? 'active' : '' }}" href="{{ route('company.subcategories.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Sub - Categories</span>
							</a>
						</div>
						@endif

						@if (in_array('Settings/Item Types', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.item-types.*') ? 'active' : '' }}" href="{{ route('company.item-types.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Item Types</span>
							</a>
						</div>
						@endif

						@if (in_array('Settings/Unit of Measurements', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.unit-of-measurements.*') ? 'active' : '' }}" href="{{ route('company.unit-of-measurements.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Unit of Measurements</span>
							</a>
						</div>
						@endif

						@if (in_array('Settings/Discount Types', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.discount-types.*') ? 'active' : '' }}" href="{{ route('company.discount-types.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Discount Types</span>
							</a>
						</div>
						@endif

						@if (in_array('Settings/Suppliers', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.suppliers.*') ? 'active' : '' }}" href="{{ route('company.suppliers.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Suppliers</span>
							</a>
						</div>
						@endif

						@if (in_array('Settings/Payment Terms', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.payment-terms.*') ? 'active' : '' }}" href="{{ route('company.payment-terms.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Payment Terms</span>
							</a>
						</div>
						@endif

						@if (in_array('Settings/Supplier Terms', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.supplier-terms.*') ? 'active' : '' }}" href="{{ route('company.supplier-terms.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Supplier Terms</span>
							</a>
						</div>
						@endif

						@if (in_array('Settings/Product Disposal Reasons', $permissions))
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.product-disposal-reasons.*') ? 'active' : '' }}" href="{{ route('company.product-disposal-reasons.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Product Disposal Reasons</span>
							</a>
						</div>
						@endif

						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.item-locations.*') ? 'active' : '' }}" href="{{ route('company.item-locations.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Item Locations</span>
							</a>
						</div>

						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.change-price-reasons.*') ? 'active' : '' }}" href="{{ route('company.change-price-reasons.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Change Price Reasons</span>
							</a>
						</div>
					</div>
				</div>
				@endif

				<div data-kt-menu-trigger="click" class="menu-item menu-accordion
					{{ request()->routeIs(
						'company.reports.*',
					) ? 'here show' : '' }}"
				>
					<span class="menu-link">
						<span class="menu-icon"><i class="fa-solid fa-chart-simple fs-2"></i></span>
						<span class="menu-title">Reports</span>
						<span class="menu-arrow"></span>
					</span>

					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item menu-accordion {{ request()->routeIs(
							'company.reports.sales-invoices-report',
							'company.reports.sales-transaction-report',
							'company.reports.void-transactions-report',
							'company.reports.vat-sales-report',
							'company.reports.x-reading-report',
							'company.reports.z-reading-report',
							'company.reports.discounts-report',
						) ? 'here show' : '' }}" data-kt-menu-trigger="click">
							<span class="menu-link">
								<span class="menu-title">Sales Reports</span>
								<span class="menu-arrow"></span>
							</span>

							<div class="menu-sub menu-sub-accordion pt-3">
								<div class="menu-item">
									<a class="menu-link {{ request()->routeIs('company.reports.sales-invoices-report') ? 'active' : '' }}" href="{{ route('company.reports.sales-invoices-report', ['companySlug' => request()->attributes->get('company')->slug]) }}">
										<span class="menu-title">Sales Invoices Report</span>
									</a>
								</div>
		
								<div class="menu-item">
									<a class="menu-link {{ request()->routeIs('company.reports.sales-transaction-report') ? 'active' : '' }}" href="{{ route('company.reports.sales-transaction-report', ['companySlug' => request()->attributes->get('company')->slug]) }}">
										<span class="menu-title">Sales Transaction Report</span>
									</a>
								</div>
		
								<div class="menu-item">
									<a class="menu-link {{ request()->routeIs('company.reports.void-transactions-report') ? 'active' : '' }}" href="{{ route('company.reports.void-transactions-report', ['companySlug' => request()->attributes->get('company')->slug]) }}">
										<span class="menu-title">Void Transactions Report</span>
									</a>
								</div>
		
								<div class="menu-item">
									<a class="menu-link {{ request()->routeIs('company.reports.vat-sales-report') ? 'active' : '' }}" href="{{ route('company.reports.vat-sales-report', ['companySlug' => request()->attributes->get('company')->slug]) }}">
										<span class="menu-title">Vat Sales Report</span>
									</a>
								</div>
		
								<div class="menu-item">
									<a class="menu-link {{ request()->routeIs('company.reports.x-reading-report') ? 'active' : '' }}" href="{{ route('company.reports.x-reading-report', ['companySlug' => request()->attributes->get('company')->slug]) }}">
										<span class="menu-title">X Reading Report</span>
									</a>
								</div>
		
								<div class="menu-item">
									<a class="menu-link {{ request()->routeIs('company.reports.z-reading-report') ? 'active' : '' }}" href="{{ route('company.reports.z-reading-report', ['companySlug' => request()->attributes->get('company')->slug]) }}">
										<span class="menu-title">Z Reading Report</span>
									</a>
								</div>
		
								<div class="menu-item">
									<a class="menu-link {{ request()->routeIs('company.reports.discounts-report') ? 'active' : '' }}" href="{{ route('company.reports.discounts-report', ['companySlug' => request()->attributes->get('company')->slug]) }}">
										<span class="menu-title">Discounts Report</span>
									</a>
								</div>

								<div class="menu-item">
									<a class="menu-link {{ request()->routeIs('company.reports.item-sales-report') ? 'active' : '' }}" href="{{ route('company.reports.item-sales-report', ['companySlug' => request()->attributes->get('company')->slug]) }}">
										<span class="menu-title">Item Sales Report</span>
									</a>
								</div>
							</div>
						</div>

						<div class="menu-item menu-accordion" data-kt-menu-trigger="click">
							<span class="menu-link">
								<span class="menu-title">Inventory Reports</span>
								<span class="menu-arrow"></span>
							</span>

							<div class="menu-sub menu-sub-accordion pt-3">
								<div class="menu-item">
									<a class="menu-link {{ request()->routeIs('company.reports.stock-card') ? 'active' : '' }}" href="{{ route('company.reports.stock-card', ['companySlug' => request()->attributes->get('company')->slug]) }}">
										<span class="menu-title">Stock Card</span>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			@endif
		</div>
	</div>
</div>
