<div class="app-navbar flex-shrink-0">
    <div class="app-navbar-item align-items-stretch ms-1 ms-md-3">
        <div class="header-search d-flex align-items-stretch">
            <div class="d-flex align-items-center">
                <a href="#" class="btn btn-icon btn-icon-muted btn-icon-primar ms-1" data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                    <i class="fa-solid fa-sun theme-light-show fs-1"></i>

                    <i class="fa-solid fa-moon theme-dark-show fs-1"></i>
                </a>

                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px" data-kt-menu="true" data-kt-element="theme-mode-menu" style="">
                    <!--begin::Menu item-->
                    <div class="menu-item px-3 my-0">
                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                            <span class="menu-icon" data-kt-element="icon">
                                <i class="fa-solid fa-sun fs-2"></i>
                            </span>
                            <span class="menu-title">
                                Light
                            </span>
                        </a>
                    </div>

                    <div class="menu-item px-3 my-0">
                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                            <span class="menu-icon" data-kt-element="icon">
                                <i class="fa-solid fa-moon fs-2"></i>
                            </span>
                            <span class="menu-title">
                                Dark
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

	<div class="app-navbar-item ms-1 ms-md-4" id="kt_header_user_menu_toggle">
		<div class="cursor-pointer symbol symbol-35px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
            @if(Auth::user()->profile_photo_url)
                <img src="{{ \Auth::user()->profile_photo_url }}" class="rounded-3" alt="user" />
            @else
                <div class="symbol-label fs-3 {{ app(\App\Actions\GetThemeType::class)->handle('bg-light-? text-?', Auth::user()->name) }}">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
            @endif
        </div>
        @include('partials/menus/_user-account-menu')
    </div>
</div>
