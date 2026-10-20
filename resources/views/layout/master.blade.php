<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" {!! printHtmlAttributes('html') !!}>
<!--begin::Head-->
<head>
    <base href=""/>
    <title>@yield('title') | {{ config('app.name', 'iSync') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8"/>
    <meta name="description" content=""/>
    <meta name="keywords" content=""/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta property="og:locale" content="en_US"/>
    <meta property="og:type" content="article"/>
    <meta property="og:title" content=""/>
    <link rel="canonical" href=""/>

    {!! includeFavicon() !!}

    <!--begin::Fonts-->
    {!! includeFonts() !!}
    <!--end::Fonts-->

    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    @foreach(getGlobalAssets('css') as $path)
        {!! sprintf('<link rel="stylesheet" href="%s">', asset($path)) !!}
    @endforeach
    <!--end::Global Stylesheets Bundle-->

    <!--begin::Vendor Stylesheets(used by this page)-->
    @foreach(getVendors('css') as $path)
        {!! sprintf('<link rel="stylesheet" href="%s">', asset($path)) !!}
    @endforeach
    <!--end::Vendor Stylesheets-->

    <!--begin::Custom Stylesheets(optional)-->
    @foreach(getCustomCss() as $path)
        {!! sprintf('<link rel="stylesheet" href="%s">', asset($path)) !!}
    @endforeach
    <!--end::Custom Stylesheets-->

    @livewireStyles
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">

    @stack('styles')
</head>
<!--end::Head-->

<!--begin::Body-->
<body {!! printHtmlClasses('body') !!} {!! printHtmlAttributes('body') !!}>

@include('partials/theme-mode/_init')

@yield('content')

<!--begin::Javascript-->
<!--begin::Global Javascript Bundle(mandatory for all pages)-->
@foreach(getGlobalAssets() as $path)
    {!! sprintf('<script src="%s"></script>', asset($path)) !!}
@endforeach
<script>
    document.addEventListener('click', function (event) {
        var button = event.target.closest('.disable-on-click');
        if (!button) {
            return;
        }
        if (button.dataset.confirmed === '1') {
            button.dataset.confirmed = '';
            return;
        }
        if (button.getAttribute('data-blocked') === '1') {
            event.preventDefault();
            event.stopImmediatePropagation();
            window.alert(button.getAttribute('data-blocked-message') || 'Fix the highlighted quantities before saving.');
            return;
        }
        var message = button.getAttribute('data-confirm');
        if (!message) {
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        if (window.confirm(message)) {
            button.dataset.confirmed = '1';
            button.click();
        }
    }, true);

    document.addEventListener('change', function (event) {
        var toggle = event.target;
        if (!toggle.classList || !toggle.classList.contains('status-toggle')) {
            return;
        }
        if (toggle.dataset.inactiveConfirmed === '1') {
            toggle.dataset.inactiveConfirmed = '';
            return;
        }
        if (toggle.checked) {
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        if (window.confirm('Set this to inactive? It will no longer be available for new transactions.')) {
            toggle.dataset.inactiveConfirmed = '1';
            toggle.dispatchEvent(new Event('change', { bubbles: true }));
        } else {
            toggle.checked = true;
        }
    }, true);

    $(function () {
        $('#product-name').off('input');
        $('#abbreviation').off('input');
        $('.compute-srp').off('keyup');
        $('#markup_type').off('change');

        function computeSrp(overwrite) {
            var cost = parseFloat($('#cost').val());
            var markUpType = $('#markup_type').val();
            var markUpValue = parseFloat($('#markup').val());
            var formula = $('#srp-formula');
            if (isNaN(cost) || isNaN(markUpValue)) {
                if ($('#srp').prop('readonly')) {
                    $('#srp').val('');
                }
                formula.text('Enter cost and markup to see the selling price.');
                return;
            }
            var srp = cost + markUpValue;
            var text = cost + ' + ' + markUpValue + ' = ';
            if (markUpType === 'percentage') {
                srp = cost + (cost * (markUpValue / 100));
                text = cost + ' + ' + markUpValue + '% = ';
            }
            srp = Math.round(srp * 10000) / 10000;
            if (overwrite || $('#srp').prop('readonly')) {
                $('#srp').val(srp);
            }
            formula.text(text + srp);
        }

        $(document).on('input change', '.compute-srp', function () {
            computeSrp(true);
        });
        $('#markup_type').on('change', function () {
            computeSrp(true);
        });
        if ($('#srp').length) {
            computeSrp(!!$('#srp').prop('readonly'));
        }

        $(document).on('change', '.pr_product_id', function () {
            var branchId = $(this).closest('form').data('branch-id');
            if (!branchId) {
                return;
            }
            var row = $(this).closest('.form-group');
            $.get('/ajax/get-product-details', {
                product_id: $(this).val(),
                branch_id: branchId
            }, function (response) {
                if (response && response.on_hand !== undefined && response.on_hand !== null) {
                    row.find('.on-hand').val(response.on_hand);
                }
            });
        });
    });
</script>
<!--end::Global Javascript Bundle-->

<!--begin::Vendors Javascript(used by this page)-->
@foreach(getVendors('js') as $path)
    {!! sprintf('<script src="%s"></script>', asset($path)) !!}
@endforeach
<!--end::Vendors Javascript-->

<!--begin::Custom Javascript(optional)-->
@foreach(getCustomJs() as $path)
    {!! sprintf('<script src="%s"></script>', asset($path)) !!}
@endforeach
<!--end::Custom Javascript-->

<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script>
{!! file_get_contents(resource_path('views/datatables/_column-filters.js')) !!}
</script>
@stack('scripts')
<!--end::Javascript-->

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('success', (message) => {
            toastr.success(message);
        });
        Livewire.on('error', (message) => {
            toastr.error(message);
        });

        Livewire.on('swal', (message, icon, confirmButtonText) => {
            if (typeof icon === 'undefined') {
                icon = 'success';
            }
            if (typeof confirmButtonText === 'undefined') {
                confirmButtonText = 'Ok, got it!';
            }
            Swal.fire({
                text: message,
                icon: icon,
                buttonsStyling: false,
                confirmButtonText: confirmButtonText,
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            });
        });
    });
</script>

@livewireScripts
</body>
<!--end::Body-->

</html>
