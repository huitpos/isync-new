@php
	$permissions = request()->attributes->get('permissionNames');
	$branches = auth()->user()->activeBranches;
	$companySlug = request()->attributes->get('company')->slug;

	$menuItems = [
		[
			'permission' => 'Main Dashboard',
			'title' => 'Dashboard',
			'icon' => 'fa-solid fa-chart-line fs-2',
			'route' => 'company.dashboard',
			'routeParams' => ['companySlug' => $companySlug],
			'activeRoutes' => ['company.dashboard'],
		],
		[
			'permission' => 'Inventory',
			'title' => 'Inventory',
			'icon' => 'fa-solid fa-warehouse fs-2',
			'activeRoutes' => ['company.branch-inventory.*', 'company.product-physical-counts.*', 'company.product-disposals.*'],
			'children' => [
				[
					'permission' => 'Inventory/Products',
					'title' => 'Products',
					'route' => 'company.branch-inventory.index',
					'routeParams' => ['companySlug' => $companySlug, 'branchId' => $branches[0]['id'] ?? null],
					'activeRoutes' => ['company.branch-inventory.index'],
					'requiresBranch' => true,
				],
				[
					'permission' => 'Inventory/Product Disposal',
					'title' => 'Product Disposals',
					'route' => 'company.product-disposals.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.product-disposals.index'],
				],
				[
					'permission' => 'Inventory/Product Physical Count',
					'title' => 'Product Physical Count',
					'route' => 'company.product-physical-counts.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.product-physical-counts.index'],
				],
			],
		],
		[
			'permission' => 'Procurement',
			'title' => 'Procurement',
			'icon' => 'fa-solid fa-boxes-stacked fs-2',
			'activeRoutes' => ['company.purchase-requests.*', 'company.purchase-orders.*', 'company.purchase-deliveries.*', 'company.stock-transfer-requests.*'],
			'children' => [
				[
					'permission' => 'Procurement/Purchase Requests',
					'title' => 'Purchase Requests',
					'route' => 'company.purchase-requests.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.purchase-requests.*'],
				],
				[
					'permission' => 'Procurement/Purchase Orders',
					'title' => 'Purchase Orders',
					'route' => 'company.purchase-orders.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.purchase-orders.*'],
				],
				[
					'permission' => 'Procurement/Purchase Deliveries',
					'title' => 'Purchase Deliveries',
					'route' => 'company.purchase-deliveries.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.purchase-deliveries.*'],
				],
				[
					'permission' => 'Procurement/Stock Transfer Requests',
					'title' => 'Stock Transfer Requests',
					'route' => 'company.stock-transfer-requests.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.stock-transfer-requests.*'],
				],
			],
		],
		[
			'permission' => 'Company Access Level',
			'title' => 'Access Level',
			'icon' => 'fa-solid fa-id-card fs-2',
			'activeRoutes' => ['company.users.*', 'company.roles.*'],
			'children' => [
				[
					'title' => 'Users',
					'route' => 'company.users.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.users.*'],
				],
				[
					'title' => 'Roles',
					'route' => 'company.roles.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.roles.*'],
				],
			],
		],
		[
			'permission' => 'Settings',
			'title' => 'Settings',
			'icon' => 'fa-solid fa-sliders fs-2',
			'activeRoutes' => ['company.departments.*', 'company.suppliers.*', 'company.categories.*', 'company.subcategories.*', 'company.item-types.*', 'company.unit-of-measurements.*', 'company.discount-types.*', 'company.charge-accounts.*', 'company.payment-types.*', 'company.payment-terms.*', 'company.supplier-terms.*', 'company.products.*', 'company.product-disposal-reasons.*', 'company.item-locations.*', 'company.change-price-reasons.*'],
			'children' => [
				[
					'permission' => 'Settings/Products',
					'title' => 'Products',
					'route' => 'company.products.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.products.*'],
				],
				[
					'permission' => 'Settings/Payment Types',
					'title' => 'Payment Types',
					'route' => 'company.payment-types.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.payment-types.*'],
				],
				[
					'permission' => 'Settings/Departments',
					'title' => 'Departments',
					'route' => 'company.departments.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.departments.*'],
				],
				[
					'permission' => 'Settings/Categories',
					'title' => 'Categories',
					'route' => 'company.categories.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.categories.*'],
				],
				[
					'permission' => 'Settings/Sub-Categories',
					'title' => 'Sub - Categories',
					'route' => 'company.subcategories.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.subcategories.*'],
				],
				[
					'permission' => 'Settings/Item Types',
					'title' => 'Item Types',
					'route' => 'company.item-types.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.item-types.*'],
				],
				[
					'permission' => 'Settings/Unit of Measurements',
					'title' => 'Unit of Measurements',
					'route' => 'company.unit-of-measurements.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.unit-of-measurements.*'],
				],
				[
					'permission' => 'Settings/Discount Types',
					'title' => 'Discount Types',
					'route' => 'company.discount-types.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.discount-types.*'],
				],
				[
					'permission' => 'Settings/Suppliers',
					'title' => 'Suppliers',
					'route' => 'company.suppliers.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.suppliers.*'],
				],
				[
					'permission' => 'Settings/Payment Terms',
					'title' => 'Payment Terms',
					'route' => 'company.payment-terms.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.payment-terms.*'],
				],
				[
					'permission' => 'Settings/Supplier Terms',
					'title' => 'Supplier Terms',
					'route' => 'company.supplier-terms.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.supplier-terms.*'],
				],
				[
					'permission' => 'Settings/Product Disposal Reasons',
					'title' => 'Product Disposal Reasons',
					'route' => 'company.product-disposal-reasons.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.product-disposal-reasons.*'],
				],
				[
					'permission' => 'Settings/Settings/Item Locations',
					'title' => 'Item Locations',
					'route' => 'company.item-locations.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.item-locations.*'],
				],
				[
					'permission' => 'Settings/Settings/Item Locations',
					'title' => 'Change Price Reasons',
					'route' => 'company.change-price-reasons.index',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.change-price-reasons.*'],
				],
			],
		],
		[
			'title' => 'Reports',
			'icon' => 'fa-solid fa-chart-simple fs-2',
            'permission' => 'Company Reports',
			'activeRoutes' => ['company.reports.*'],
			'children' => [
				[
					'permission' => 'Company Reports/Inventory Reports',
					'title' => 'Inventory Reports',
					'isSubmenu' => true,
					'activeRoutes' => ['company.reports.stock-card'],
					'children' => [
						[
							'permission' => 'Company Reports/Inventory Reports/Stock Card',
							'title' => 'Stock Card',
							'route' => 'company.reports.stock-card',
							'routeParams' => ['companySlug' => $companySlug],
							'activeRoutes' => ['company.reports.stock-card'],
						],
					],
				],
				[
					'permission' => 'Company Reports/Sales Reports',
					'title' => 'Sales Reports',
					'isSubmenu' => true,
					'activeRoutes' => ['company.reports.sales-invoices-report', 'company.reports.sales-transaction-report', 'company.reports.void-transactions-report', 'company.reports.vat-sales-report', 'company.reports.x-reading-report', 'company.reports.z-reading-report', 'company.reports.discounts-report', 'company.reports.item-sales-report', 'company.reports.bir-sales-summary-report', 'company.reports.bir-senior-citizen-sales-report', 'company.reports.bir-pwd-sales-report', 'company.reports.bir-naac-sales-report', 'company.reports.bir-solo-parent-sales-report'],
					'children' => [
						[
							'permission' => 'Company Reports/Sales Reports/Sales Invoices Report',
							'title' => 'Sales Invoices Report',
							'route' => 'company.reports.sales-invoices-report',
							'routeParams' => ['companySlug' => $companySlug],
							'activeRoutes' => ['company.reports.sales-invoices-report'],
						],
						[
							'permission' => 'Company Reports/Sales Reports/Sales Transaction Report',
							'title' => 'Sales Transaction Report',
							'route' => 'company.reports.sales-transaction-report',
							'routeParams' => ['companySlug' => $companySlug],
							'activeRoutes' => ['company.reports.sales-transaction-report'],
						],
						[
							'permission' => 'Company Reports/Sales Reports/Void Transactions Report',
							'title' => 'Void Transactions Report',
							'route' => 'company.reports.void-transactions-report',
							'routeParams' => ['companySlug' => $companySlug],
							'activeRoutes' => ['company.reports.void-transactions-report'],
						],
						[
							'permission' => 'Company Reports/Sales Reports/Vat Sales Report',
							'title' => 'Vat Sales Report',
							'route' => 'company.reports.vat-sales-report',
							'routeParams' => ['companySlug' => $companySlug],
							'activeRoutes' => ['company.reports.vat-sales-report'],
						],
						[
							'permission' => 'Company Reports/Sales Reports/X Reading Report',
							'title' => 'X Reading Report',
							'route' => 'company.reports.x-reading-report',
							'routeParams' => ['companySlug' => $companySlug],
							'activeRoutes' => ['company.reports.x-reading-report'],
						],
						[
							'title' => 'Z Reading Report',
							'route' => 'company.reports.z-reading-report',
							'routeParams' => ['companySlug' => $companySlug],
							'activeRoutes' => ['company.reports.z-reading-report'],
						],
						[
							'title' => 'Discounts Report',
							'route' => 'company.reports.discounts-report',
							'routeParams' => ['companySlug' => $companySlug],
							'activeRoutes' => ['company.reports.discounts-report'],
						],
						[
							'title' => 'Item Sales Report',
							'route' => 'company.reports.item-sales-report',
							'routeParams' => ['companySlug' => $companySlug],
							'activeRoutes' => ['company.reports.item-sales-report'],
						],
						[
							'title' => 'BIR Sales Summary Report',
							'route' => 'company.reports.bir-sales-summary-report',
							'routeParams' => ['companySlug' => $companySlug],
							'activeRoutes' => ['company.reports.bir-sales-summary-report'],
						],
						[
							'title' => 'Senior Citizen Sales Book/Report',
							'route' => 'company.reports.bir-senior-citizen-sales-report',
							'routeParams' => ['companySlug' => $companySlug],
							'activeRoutes' => ['company.reports.bir-senior-citizen-sales-report'],
						],
						[
							'title' => 'Persons with Disability Sales Book/Report',
							'route' => 'company.reports.bir-pwd-sales-report',
							'routeParams' => ['companySlug' => $companySlug],
							'activeRoutes' => ['company.reports.bir-pwd-sales-report'],
						],
						[
							'title' => 'National Athletes and Coaches Sales Book/Report',
							'route' => 'company.reports.bir-naac-sales-report',
							'routeParams' => ['companySlug' => $companySlug],
							'activeRoutes' => ['company.reports.bir-naac-sales-report'],
						],
						[
							'title' => 'Solo Parent Sales Book/Report',
							'route' => 'company.reports.bir-solo-parent-sales-report',
							'routeParams' => ['companySlug' => $companySlug],
							'activeRoutes' => ['company.reports.bir-solo-parent-sales-report'],
						],
					],
				],
				[
					'title' => 'Audit Trail',
					'route' => 'company.reports.audit-trail',
					'routeParams' => ['companySlug' => $companySlug],
					'activeRoutes' => ['company.reports.audit-trail'],
                    'permission' => 'Company Reports/Audit Trail Report',
				],
			],
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
							$shouldShowChild = $childHasPermission && (!isset($child['requiresBranch']) || isset($branches[0]['id']));
						@endphp

						@if ($shouldShowChild)
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
