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