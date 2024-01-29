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