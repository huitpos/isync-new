$(document).ready(function () {
    $('.repeater').repeater({
        initEmpty: $('.repeater').data('init-empty') || false,
        defaultValues: {
            'text-input': 'foo'
        },
        show: function () {
            $(this).slideDown();
            // Init select2 on new repeated items
            initConditionsSelect2();
        },
        hide: function (deleteElement) {
            // if(confirm('Are you sure you want to delete this element?')) {
                $(this).slideUp(deleteElement);
            // }
        },
        isFirstItemUndeletable: false,
        repeaters: [{
            // (Required)
            // Specify the jQuery selector for this nested repeater
            selector: '.inner-repeater'
        }]
    });
});

$(document).on('click', '.button-ajax', function (e) {
    e.preventDefault();
    var action = $(this).data('action');
    var method = $(this).data('method');
    var csrf = $(this).data('csrf');
    var reload = $(this).data('reload');

    axios.request({
        url: action,
        method: method,
        data: {
            _token: csrf
        },
    })
        .then(function (response) {
            console.log(response);
        })
        .catch(function (error) {
            console.log(error);
        })
        .then(function () {
            if (reload) {
                window.location.reload();
            }
        });
});



$(document).on('change', '.status-toggle', function (e) {
    e.preventDefault();

    var elem = $(this);
    var action = elem.data('action');
    var csrf = $(this).data('csrf');

    axios.request({
        url: action,
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        data: {
            _token: csrf,
            status: elem.is(':checked') ? 'active' : 'inactive'
        },
    })
    .then(function (response) {

        if (response.data.status == 'error') {
            elem.prop('checked', !elem.is(':checked'));
            toastr.error(
                "",
                response.data.message,
                {timeOut: 2000, extendedTimeOut: 0, closeButton: true, closeDuration: 0}
            );
            return;
        } else {
            toastr.success(
                "",
                response.data.message,
                {timeOut: 2000, extendedTimeOut: 0, closeButton: true, closeDuration: 0}
            );
        }
    })
    .catch(function (error) {
        console.log(error);
    })
    .then(function () {
        if (reload) {
            window.location.reload();
        }
    });
});

// Init condition select2
const initConditionsSelect2 = () => {
    // Tnit new repeating condition types
    const allConditionTypes = document.querySelectorAll('[data-kt-ecommerce-catalog-add-category="condition_type"]');
    allConditionTypes.forEach(type => {
        if ($(type).hasClass("select2-hidden-accessible")) {
            return;
        } else {
            $(type).select2({
                minimumResultsForSearch: -1
            });
        }
    });

    // Tnit new repeating condition equals
    const allConditionEquals = document.querySelectorAll('[data-kt-ecommerce-catalog-add-category="condition_equals"]');
    allConditionEquals.forEach(equal => {
        if ($(equal).hasClass("select2-hidden-accessible")) {
            return;
        } else {
            $(equal).select2({
                minimumResultsForSearch: -1
            });
        }
    });
}

$(document).on('click', '.disable-on-click', function (e) {
    $(this).prop('disabled', true);
    $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Please wait...');

    $(this).closest('form').submit();
});