@php
	$permissions = request()->attributes->get('permissionNames');
	$companyPermissionCount = request()->attributes->get('companyPermissionCount');
	$branchPermissionCount = request()->attributes->get('branchPermissionCount');
	$companyFirstRoute = request()->attributes->get('companyFirstRoute');
@endphp

<div class="app-sidebar-menu overflow-hidden flex-column-fluid">
	<div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper hover-scroll-overlay-y my-5" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer" data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">
		<div class="menu menu-column menu-rounded menu-sub-indention px-3 fw-semibold fs-6" id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">
			{{-- Admin Level Menu --}}
			@if (request()->segment(1) == 'admin')
				@include('layout.partials.sidebar-layout.sidebar._menu-admin')
			@endif

			{{-- Switcher for Company and Branch --}}
			@if (request()->attributes->get('branch') || request()->attributes->get('company'))
			<div class="mb-5">
				<button type="button" class="btn btn-primary rotate w-100 btn-trim-end" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start" data-kt-menu-offset="10px, 10px">
					{{ request()->attributes->get('branch') ? request()->attributes->get('branch')->name : request()->attributes->get('company')->company_name }}
					<i class="ki-duotone ki-down fs-3 rotate-180 ms-3 me-0"></i>
				</button>

				@php
					$branches = auth()->user()->activeBranches;
				@endphp

				<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-primary fw-semibold w-auto min-w-300px mw-300px" data-kt-menu="true">
					@if ($companyPermissionCount > 0)
					<div class="menu-item mt-2">
						<a href="{{ route($companyFirstRoute, [
							'companySlug' => request()->attributes->get('company')->slug,
							'branchSlug' => request()->attributes->get('branch')?->slug,
							'branchId' => $branches[0]['id']
						]) }}" class="menu-link p-2">
							{{ request()->attributes->get('company')->company_name }}
						</a>
					</div>

					<div class="separator mb-3 opacity-75"></div>
					@endif

					@if ($branches->count() > 1)
						<label class="form-label fw-semibold p-2">Branches:</label>
					@endif

					@foreach($branches as $branch)
						<div class="menu-item p-0">
							<a href="{{ route($branchPermissionCount > 0 ? 'branch.dashboard' : 'branch.users.index', ['companySlug' => request()->attributes->get('company')->slug, 'branchSlug' => $branch->slug]) }}" class="menu-link p-2 mb-1">
								{{ $branch->name }}
							</a>
						</div>
					@endforeach
				</div>
			</div>
			@endif

			{{-- Branch Level Menu --}}
			@if (request()->attributes->get('branch'))
				@include('layout.partials.sidebar-layout.sidebar._menu-branch')
			{{-- Company Level Menu --}}
			@elseif (request()->attributes->get('company'))
				@include('layout.partials.sidebar-layout.sidebar._menu-company')
			@endif
		</div>
	</div>
</div>
