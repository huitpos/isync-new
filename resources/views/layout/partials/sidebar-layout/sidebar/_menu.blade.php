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

			@if (request()->attributes->get('company'))
				<div data-kt-menu-trigger="click" class="menu-item menu-accordion
					{{ request()->routeIs(
						'company.products.*',
					) ? 'here show' : '' }}"
				>
					<span class="menu-link">
						<span class="menu-icon"><i class="fa-solid fa-warehouse fs-2"></i></span>
						<span class="menu-title">Inventory</span>
						<span class="menu-arrow"></span>
					</span>

					<!--products-->
					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.products.*') ? 'active' : '' }}" href="{{ route('company.products.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Products</span>
							</a>
						</div>
					</div>
				</div>

				<div data-kt-menu-trigger="click" class="menu-item menu-accordion
					{{ request()->routeIs(
						'company.departments.*',
						'company.suppliers.*',
						'company.categories.*',
						'company.subcategories.*',
						'company.item-types.*',
						'company.unit-of-measurements.*',
						'company.discount-types.*'
					) ? 'here show' : '' }}"
				>
					<span class="menu-link">
						<span class="menu-icon"><i class="fa-solid fa-sliders fs-2"></i></span>
						<span class="menu-title">Settings</span>
						<span class="menu-arrow"></span>
					</span>

					<!--departments-->
					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.departments.*') ? 'active' : '' }}" href="{{ route('company.departments.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Departments</span>
							</a>
						</div>
					</div>

					<!--categories-->
					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.categories.*') ? 'active' : '' }}" href="{{ route('company.categories.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Categories</span>
							</a>
						</div>
					</div>

					<!--subcategories-->
					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.subcategories.*') ? 'active' : '' }}" href="{{ route('company.subcategories.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Sub - Categories</span>
							</a>
						</div>
					</div>

					<!--item types-->
					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.item-types.*') ? 'active' : '' }}" href="{{ route('company.item-types.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Item Types</span>
							</a>
						</div>
					</div>

					<!--UOM-->
					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.unit-of-measurements.*') ? 'active' : '' }}" href="{{ route('company.unit-of-measurements.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Unit of Measurements</span>
							</a>
						</div>
					</div>

					<!--discount types-->
					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.discount-types.*') ? 'active' : '' }}" href="{{ route('company.discount-types.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Discount Types</span>
							</a>
						</div>
					</div>

					<!--suppliers-->
					<div class="menu-sub menu-sub-accordion">
						<div class="menu-item">
							<a class="menu-link {{ request()->routeIs('company.suppliers.*') ? 'active' : '' }}" href="{{ route('company.suppliers.index', ['companySlug' => request()->attributes->get('company')->slug]) }}">
								<span class="menu-title">Suppliers</span>
							</a>
						</div>
					</div>
				</div>
			@endif
		</div>
	</div>
</div>
