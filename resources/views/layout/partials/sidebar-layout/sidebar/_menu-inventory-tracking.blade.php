{{-- Admin Level Menu --}}
<div class="menu-item">
	<a class="menu-link {{ request()->routeIs('inventory-tracking.index') ? 'active' : '' }}" href="{{ route('inventory-tracking.index') }}">
		<span class="menu-title">Inventory Tracking</span>
	</a>

    <a class="menu-link {{ request()->routeIs('inventory-tracking.history') ? 'active' : '' }}" href="{{ route('inventory-tracking.history') }}">
		<span class="menu-title">Movement History</span>
	</a>

    <a class="menu-link {{ request()->routeIs('inventory-tracking.master-list') ? 'active' : '' }}" href="{{ route('inventory-tracking.master-list') }}">
		<span class="menu-title">Stock Master List</span>
	</a>

    <a class="menu-link {{ request()->routeIs('inventory-tracking.report') ? 'active' : '' }}" href="{{ route('inventory-tracking.report') }}">
		<span class="menu-title">Inventory Report</span>
	</a>
</div>