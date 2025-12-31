@php
	$permissions = request()->attributes->get('permissionNames');
	$companySlug = request()->attributes->get('company')->slug;
	$branchSlug = request()->attributes->get('branch')->slug;
	$routeParams = ['companySlug' => $companySlug, 'branchSlug' => $branchSlug];

	$menuItems = [
		[
			'permission' => 'Branch Dashboard',
			'title' => 'Dashboard',
			'icon' => 'fa-solid fa-chart-line fs-2',
			'route' => 'branch.dashboard',
			'routeParams' => $routeParams,
			'activeRoutes' => ['branch.dashboard'],
		],
		[
			'permission' => 'Branch Users',
			'title' => 'Users',
			'icon' => 'fa-solid fa-users fs-2',
			'route' => 'branch.users.index',
			'routeParams' => $routeParams,
			'activeRoutes' => ['branch.users.*'],
		],
		[
			'permission' => 'Branch Delivery Locations',
			'title' => 'Delivery Locations',
			'icon' => 'fa-solid fa-map-location-dot fs-2',
			'route' => 'branch.delivery-locations.index',
			'routeParams' => $routeParams,
			'activeRoutes' => ['branch.delivery-locations.*'],
		],
		[
			'title' => 'Procurement',
			'permission' => 'Branch Procurement',
			'icon' => 'fa-solid fa-boxes-stacked fs-2',
			'activeRoutes' => ['branch.purchase-requests.*', 'branch.purchase-orders.*', 'branch.purchase-deliveries.*', 'branch.purchase-delivery.*'],
			'children' => [
				[
					'permission' => 'Branch Procurement/Purchase Requests',
					'title' => 'Purchase Requests',
					'route' => 'branch.purchase-requests.index',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.purchase-requests.*'],
				],
				[
					'permission' => 'Branch Procurement/Purchase Orders',
					'title' => 'Purchase Orders',
					'route' => 'branch.purchase-orders.index',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.purchase-orders.*'],
				],
				[
					'permission' => 'Branch Procurement/Purchase Deliveries',
					'title' => 'Purchase Deliveries',
					'route' => 'branch.purchase-deliveries.index',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.purchase-deliveries.*', 'branch.purchase-delivery.*'],
				],
			],
		],
		[
			'permission' => 'Branch Inventory',
			'title' => 'Inventory',
			'icon' => 'fa-solid fa-truck-moving fs-2',
			'activeRoutes' => ['branch.stock-transfer-requests.*', 'branch.stock-transfer-orders.*', 'branch.stock-transfer-deliveries.*', 'branch.product-physical-counts.*', 'branch.product-disposals.*'],
			'children' => [
				[
					'permission' => 'Branch Inventory/Stock Transfer Requests',
					'title' => 'Stock Transfer Requests',
					'route' => 'branch.stock-transfer-requests.index',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.stock-transfer-requests.*'],
				],
				[
					'permission' => 'Branch Inventory/Stock Transfer Orders',
					'title' => 'Stock Transfer Orders',
					'route' => 'branch.stock-transfer-orders.index',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.stock-transfer-orders.*'],
				],
				[
					'permission' => 'Branch Inventory/Stock Transfer Deliveries',
					'title' => 'Stock Transfer Deliveries',
					'route' => 'branch.stock-transfer-deliveries.index',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.stock-transfer-deliveries.*'],
				],
				[
					'permission' => 'Branch Inventory/Product Physical Count',
					'title' => 'Product Physical Count',
					'route' => 'branch.product-physical-counts.index',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.product-physical-counts.*'],
				],
				[
					'permission' => 'Branch Inventory/Product Disposals',
					'title' => 'Product Disposals',
					'route' => 'branch.product-disposals.index',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.product-disposals.*'],
				],
			],
		],
		[
			'permission' => 'Branch Products',
			'title' => 'Products',
			'icon' => 'fa-solid fa-table-list fs-2',
			'route' => 'branch.products.index',
			'routeParams' => $routeParams,
			'activeRoutes' => ['branch.products.*'],
		],
		[
			'permission' => 'Branch Reports',
			'title' => 'Reports',
			'icon' => 'fa-solid fa-chart-simple fs-2',
			'activeRoutes' => ['branch.reports.*'],
			'children' => [
				[
					'permission' => 'Branch Reports/Sales Invoices Report',
					'title' => 'Sales Invoices Report',
					'route' => 'branch.reports.sales-invoices-report',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.reports.sales-invoices-report'],
				],
				[
					'permission' => 'Branch Reports/Sales Transaction Report',
					'title' => 'Sales Transaction Report',
					'route' => 'branch.reports.sales-transaction-report',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.reports.sales-transaction-report'],
				],
				[
					'permission' => 'Branch Reports/Void Transaction Report',
					'title' => 'Void Transactions Report',
					'route' => 'branch.reports.void-transactions-report',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.reports.void-transactions-report'],
				],
				[
					'permission' => 'Branch Reports/Vat Sales Report',
					'title' => 'Vat Sales Report',
					'route' => 'branch.reports.vat-sales-report',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.reports.vat-sales-report'],
				],
				[
					'permission' => 'Branch Reports/X Reading Report',
					'title' => 'X Reading Report',
					'route' => 'branch.reports.x-reading-report',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.reports.x-reading-report'],
				],
				[
					'permission' => 'Branch Reports/Z Reading Report',
					'title' => 'Z Reading Report',
					'route' => 'branch.reports.z-reading-report',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.reports.z-reading-report'],
				],
				[
					'permission' => 'Branch Reports/Discounts Report',
					'title' => 'Discounts Report',
					'route' => 'branch.reports.discounts-report',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.reports.discounts-report'],
				],
				[
					'permission' => 'Branch Reports/Item Sales Report',
					'title' => 'Item Sales Report',
					'route' => 'branch.reports.item-sales-report',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.reports.item-sales-report'],
				],
				[
					'permission' => 'Branch Reports/Stock Card',
					'title' => 'Stock Card',
					'route' => 'branch.reports.stock-card',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.reports.stock-card'],
				],
				[
					'permission' => 'Branch Reports/Audit Trail',
					'title' => 'Audit Trail',
					'route' => 'branch.reports.audit-trail',
					'routeParams' => $routeParams,
					'activeRoutes' => ['branch.reports.audit-trail'],
				],
			],
		],
		[
			'permission' => 'Branch Customer Informations',
			'title' => 'Customer Informations',
			'route' => 'branch.charge-accounts.index',
			'routeParams' => $routeParams,
			'activeRoutes' => ['branch.charge-accounts.*'],
		],
		[
			'title' => 'Back Up',
			'route' => 'branch.reports.backup',
			'routeParams' => $routeParams,
			'activeRoutes' => ['branch.reports.backup'],
		],
	];
@endphp

@foreach ($menuItems as $item)
	@php
		$hasPermission = !isset($item['permission']) || in_array($item['permission'], $permissions);
		$hasChildren = isset($item['children']) && count($item['children']) > 0;
		$isActive = $hasChildren ? 
			collect($item['activeRoutes'])->some(fn($route) => request()->routeIs($route)) : 
			collect($item['activeRoutes'])->some(fn($route) => request()->routeIs($route));
	@endphp

	@if ($hasPermission)
		@if ($hasChildren)
			<!-- Parent menu with children -->
			<div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $isActive ? 'here show' : '' }}">
				<span class="menu-link">
					@if (isset($item['icon']))
						<span class="menu-icon"><i class="{{ $item['icon'] }}"></i></span>
					@endif
					<span class="menu-title">{{ $item['title'] }}</span>
					<span class="menu-arrow"></span>
				</span>

				<div class="menu-sub menu-sub-accordion">
					@foreach ($item['children'] as $child)
						@php
							$childHasPermission = !isset($child['permission']) || in_array($child['permission'], $permissions);
							$childHasChildren = isset($child['children']) && count($child['children']) > 0;
							$childIsActive = $childHasChildren ? 
								collect($child['activeRoutes'])->some(fn($route) => request()->routeIs($route)) : 
								collect($child['activeRoutes'])->some(fn($route) => request()->routeIs($route));
						@endphp

						@if ($childHasPermission)
							@if ($childHasChildren)
							<!-- Nested submenu -->
							<div class="menu-item menu-accordion {{ $childIsActive ? 'here show' : '' }}" data-kt-menu-trigger="click">
								<span class="menu-link">
									<span class="menu-title">{{ $child['title'] }}</span>
									<span class="menu-arrow"></span>
								</span>

								<div class="menu-sub menu-sub-accordion pt-3">
									@foreach ($child['children'] as $grandchild)
										@php
											$grandchildHasPermission = !isset($grandchild['permission']) || in_array($grandchild['permission'], $permissions);
										@endphp

										@if ($grandchildHasPermission)
											<a class="menu-link {{ request()->routeIs(...$grandchild['activeRoutes']) ? 'active' : '' }}" href="{{ route($grandchild['route'], $grandchild['routeParams']) }}">
												<span class="menu-title">{{ $grandchild['title'] }}</span>
											</a>
										@endif
									@endforeach
								</div>
							</div>
						@else
							<!-- Regular child menu item -->
							<div class="menu-item">
								<a class="menu-link {{ request()->routeIs(...$child['activeRoutes']) ? 'active' : '' }}" href="{{ route($child['route'], $child['routeParams']) }}">
									<span class="menu-title">{{ $child['title'] }}</span>
								</a>
							</div>
						@endif
						@endif
					@endforeach
				</div>
			</div>
		@else
			<!-- Single menu item without children -->
			<div class="menu-item">
				<a class="menu-link {{ request()->routeIs(...$item['activeRoutes']) ? 'active' : '' }}" href="{{ route($item['route'], $item['routeParams']) }}">
					@if (isset($item['icon']))
						<span class="menu-icon"><i class="{{ $item['icon'] }}"></i></span>
					@endif
					<span class="menu-title">{{ $item['title'] }}</span>
				</a>
			</div>
		@endif
	@endif
@endforeach
