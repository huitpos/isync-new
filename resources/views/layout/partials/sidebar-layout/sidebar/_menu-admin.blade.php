{{-- Admin Level Menu --}}
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
