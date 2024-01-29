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
					<a class="menu-link {{ request()->routeIs('admin.branches.*') || request()->routeIs('admin.machines.*') ? 'active' : '' }}" href="{{ route('admin.branches.index') }}">
						<span class="menu-icon">
							<i class="fa-solid fa-shop fs-2"></i>
						</span>
						<span class="menu-title">Branches</span>
					</a>
				</div>
			@endif
		</div>
	</div>
</div>
