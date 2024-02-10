// Initialize KTMenu
KTMenu.init();

// Add click event listener to delete buttons
document.querySelectorAll('[data-kt-action="delete_row"]').forEach(function (element) {
    element.addEventListener('click', function () {
        Swal.fire({
            text: 'Are you sure you want to remove?',
            icon: 'warning',
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: this.getAttribute('data-url'),
                    method: 'DELETE',
                    data: {
                        _token: this.getAttribute('data-csrf'),
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            Swal.fire({
                                text: response.message,
                                icon: 'success',
                                buttonsStyling: false,
                                confirmButtonText: 'Ok',
                                customClass: {
                                    confirmButton: 'btn btn-primary',
                                }
                            }).then(function() {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                      console.error('Error:', error);
                    }
                });
            }
        });
    });
});

var start = moment().subtract(29, "days");
var end = moment();
var input = $("#kt_ecommerce_report_returns_daterangepicker");

function cb(start, end) {
    input.html(start.format("MMMM D, YYYY") + " - " + end.format("MMMM D, YYYY"));
}

input.daterangepicker({
    startDate: start,
    endDate: end,
    ranges: {
        "Today": [moment(), moment()],
        "Yesterday": [moment().subtract(1, "days"), moment().subtract(1, "days")],
        "Last 7 Days": [moment().subtract(6, "days"), moment()],
        "Last 30 Days": [moment().subtract(29, "days"), moment()],
        "This Month": [moment().startOf("month"), moment().endOf("month")],
        "Last Month": [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")]
    }
}, cb);

cb(start, end);