@php
	$permissions = request()->attributes->get('permissionNames');
@endphp

{{-- Branch Level Menu --}}
@if (in_array('Branch Dashboard', $permissions))
<div class="menu-item">
	<a class="menu-link {{ request()->routeIs('branch.dashboard') ? 'active' : '' }}" href="{{ route('branch.dashboard', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
		<span class="menu-icon">
			<i class="fa-solid fa-chart-line fs-2"></i>
		</span>
		<span class="menu-title">Dashboard</span>
	</a>
</div>
@endif

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

		<div class="menu-item">
			<a class="menu-link {{ request()->routeIs('branch.reports.audit-trail') ? 'active' : '' }}" href="{{ route('branch.reports.audit-trail', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
				<span class="menu-title">Audit Trail</span>
			</a>
		</div>
	</div>
</div>

<div class="menu-item">
	<a class="menu-link {{ request()->routeIs('branch.charge-accounts.*') ? 'active' : '' }}" href="{{ route('branch.charge-accounts.index', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
		<span class="menu-title">Customer Informations</span>
	</a>
</div>

<div class="menu-item">
	<a class="menu-link {{ request()->routeIs('branch.reports.backup') ? 'active' : '' }}" href="{{ route('branch.reports.backup', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => request()->attributes->get('branch')->slug]) }}">
		<span class="menu-title">Back Up</span>
	</a>
</div>
