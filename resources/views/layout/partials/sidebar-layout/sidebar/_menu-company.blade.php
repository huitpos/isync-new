@php
	$permissions = request()->attributes->get('permissionNames');
	$branches = auth()->user()->activeBranches;
@endphp

{{-- Company Level Menu --}}
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
		'company.item-locations.*',
		'company.change-price-reasons.*',
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

        @if (in_array('Settings/Settings/Item Locations', $permissions))
		<div class="menu-item">
			<a class="menu-link {{ request()->routeIs('company.item-locations.*') ? 'active' : '' }}" href="{{ route('company.item-locations.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
				<span class="menu-title">Item Locations</span>
			</a>
		</div>
        @endif

        @if (in_array('Settings/Settings/Item Locations', $permissions))
		<div class="menu-item">
			<a class="menu-link {{ request()->routeIs('company.change-price-reasons.*') ? 'active' : '' }}" href="{{ route('company.change-price-reasons.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
				<span class="menu-title">Change Price Reasons</span>
			</a>
		</div>
        @endif
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

		<div class="menu-item">
			<a class="menu-link {{ request()->routeIs('company.reports.audit-trail') ? 'active' : '' }}" href="{{ route('company.reports.audit-trail', ['companySlug' => request()->attributes->get('company')->slug]) }}">
				<span class="menu-title">Audit Trail</span>
			</a>
		</div>

		<div class="menu-item">
			<a class="menu-link {{ request()->routeIs('company.reports.bir-sales-summary-report') ? 'active' : '' }}" href="{{ route('company.reports.bir-sales-summary-report', ['companySlug' => request()->attributes->get('company')->slug]) }}">
				<span class="menu-title">BIR Sales Summary Report</span>
			</a>

			<a class="menu-link {{ request()->routeIs('company.reports.bir-senior-citizen-sales-report') ? 'active' : '' }}" href="{{ route('company.reports.bir-senior-citizen-sales-report', ['companySlug' => request()->attributes->get('company')->slug]) }}">
				<span class="menu-title">Senior Citizen Sales Book/Report</span>
			</a>

			<a class="menu-link {{ request()->routeIs('company.reports.bir-pwd-sales-report') ? 'active' : '' }}" href="{{ route('company.reports.bir-pwd-sales-report', ['companySlug' => request()->attributes->get('company')->slug]) }}">
				<span class="menu-title">Persons with Disability Sales Book/Report</span>
			</a>
			
			<a class="menu-link {{ request()->routeIs('company.reports.bir-naac-sales-report') ? 'active' : '' }}" href="{{ route('company.reports.bir-naac-sales-report', ['companySlug' => request()->attributes->get('company')->slug]) }}">
				<span class="menu-title">National Athletes and Coaches Sales Book/Report</span>
			</a>
			
			<a class="menu-link {{ request()->routeIs('company.reports.bir-solo-parent-sales-report') ? 'active' : '' }}" href="{{ route('company.reports.bir-solo-parent-sales-report', ['companySlug' => request()->attributes->get('company')->slug]) }}">
				<span class="menu-title">Solo Parent Sales Book/Report</span>
			</a>
		</div>
	</div>
</div>
