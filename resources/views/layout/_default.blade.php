@extends('layout.master')

@section('content')
    <!--begin::App-->
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <!--begin::Page-->
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            @include(config('settings.KT_THEME_LAYOUT_DIR').'/partials/sidebar-layout/_header')
            <!--begin::Wrapper-->
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                @include(config('settings.KT_THEME_LAYOUT_DIR').'/partials/sidebar-layout/_sidebar')
                <!--begin::Main-->
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <!--begin::Content wrapper-->

                    <div class="d-flex flex-column flex-column-fluid">
                        @include(config('settings.KT_THEME_LAYOUT_DIR').'/partials/sidebar-layout/_toolbar')
                        <!--begin::Content-->
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <!--begin::Content container-->
                            <div id="kt_app_content_container" class="app-container container-fluid">
                                @if(session('success'))
                                    <div class="alert alert-dismissible bg-light-success d-flex flex-column flex-sm-row p-5 mb-10">
                                        <i class="fa-solid fa-check fs-2hx text-success me-4 mb-5 mb-sm-0"></i>

                                        <div class="d-flex flex-column pe-0 pe-sm-10">
                                            <h4 class="fw-semibold">Success!</h4>

                                            <span>{{ session('success') }}</span>
                                        </div>

                                        <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                                            <i class="fa-solid fa-xmark fs-1"></i>
                                        </button>
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                {{ $slot }}
                            </div>
                            <!--end::Content container-->
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Content wrapper-->
                    @include(config('settings.KT_THEME_LAYOUT_DIR').'/partials/sidebar-layout/_footer')
                </div>
                <!--end:::Main-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::App-->

    @include('partials/_modals')

    @include('partials/_scrolltop')

@endsection
