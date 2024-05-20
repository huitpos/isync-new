$(document).ready(function () {
    function computeTotal(elem)
    {
        var quantity = elem.find('.pr_quantity').val();
        var unitPrice = elem.find('.unit_price').val();

        var total = quantity * unitPrice;

        //rount to 2 decimal places
        total = Math.round(total * 100) / 100;
        elem.find('.pr_item_total').val(total);

        //get sum of all .pr_item_total
        var sum = 0;

        $('.pr_item_total').each(function() {
            sum += Number($(this).val());
        });

        sum = Math.round(sum * 100) / 100;

        $('.grandtotal').html(sum);
        $('#pr_total').val(sum);
    }

    $(document).on('keyup', '.pr_quantity', function (e) {
        var granparentElem = $(this).parent().parent();

        computeTotal(granparentElem);
    });

    $('.pr_department_id').on('change', function (e) {
        var selectedValue = $(this).val();

        $('[data-repeater-delete]').trigger('click');
        $('.select2-container').remove();
        $('.select2-ajax').removeAttr("data-kt-initialized");
        initConditionsSelect2();

        var newOptionsHTML = '';

        $.ajax({
            url: '/ajax/get-department-suppliers', // Replace with your actual endpoint
            method: 'GET',
            data: {
              department_id: selectedValue
            },
            success: function(response) {
                newOptionsHTML += '<option value="">Select a supplier</option>';

                response.forEach(function(option) {
                    newOptionsHTML += '<option value="' + option.id + '">' + option.name + '</option>';
                });

                changeOptionsAndReinitialize('supplier_id', newOptionsHTML);
            },
            error: function(xhr, status, error) {
              console.error('Error:', error);
            }
        });

        changeOptionsAndReinitialize('supplier_id', newOptionsHTML);
    });

    $(document).on('change', '.pr_uom_id', function (e) {
        var selectedValue = $(this).val();
        var selectedText = $(this).find("option:selected").text();

        var granparentElem = $(this).parent().parent();
        granparentElem.find('.pr_selected_uom_text').val(selectedText);
    });

    $(document).on('change', '.pr_product_id', function (e) {
        var selectedValue = $(this).val();
        var selectedText = $(this).find("option:selected").text();

        var element = $(this);

        var newOptionsHTML = '';

        var granparentElem = $(this).parent().parent();
        var uomElem = granparentElem.find('.pr_uom_id');
        granparentElem.find('.pr_selected_product_text').val(selectedText);

        $.ajax({
            url: '/ajax/get-product-uoms', // Replace with your actual endpoint
            method: 'GET',
            data: {
              product_id: selectedValue
            },
            success: function(response) {
                response.forEach(function(option) {
                    newOptionsHTML += '<option value="' + option.id + '">' + option.text + '</option>';
                });

                uomElem.empty();
                uomElem.html(newOptionsHTML);
                uomElem.select2();

                granparentElem.find('.pr_selected_uom_text').val(uomElem.find("option:selected").text());
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });

        $.ajax({
            url: '/ajax/get-product-details', // Replace with your actual endpoint
            method: 'GET',
            data: {
              product_id: selectedValue
            },
            success: function(response) {
                granparentElem.find('.barcode').val(response.barcode);
                granparentElem.find('.unit_price').val(response.cost);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });

    $('#markup_type').on('change', function() {
        computeSrp();
    });

    $('.compute-srp').on('keyup', function() {
        computeSrp();
    });

    $('#delivery_location_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var address = selectedOption.data('address');

        $('#delivery_address').val(address)
    });

    function computeSrp()
    {
        var cost  = parseFloat($('#cost').val());
        var markUpType = $('#markup_type').val();
        var markUpValue = parseFloat($('#markup').val());

        if (isNaN(cost) || isNaN(markUpValue)) {
            $('#srp').val(0);
            return false;
        }

        var srp = cost + markUpValue;
        if (markUpType == 'percentage') {
            srp = cost + (cost * (markUpValue / 100));
        }

        $('#srp').val(srp);
    }

    $('.department-category-selector').on('change', function() {
        var selectedValue = $(this).val();
        var newOptionsHTML = '';

        tempOptions('category_id');

        if ($('.category-subcategory-selector').length) {
            tempOptions('subcategory_id');
        }

        $.ajax({
            url: '/ajax/get-department-categories', // Replace with your actual endpoint
            method: 'GET',
            data: {
              department_id: selectedValue
            },
            success: function(response) {
                newOptionsHTML += '<option value="">Select a category</option>';

                response.forEach(function(option) {
                    newOptionsHTML += '<option value="' + option.id + '">' + option.name + '</option>';
                });

                changeOptionsAndReinitialize('category_id', newOptionsHTML);
            },
            error: function(xhr, status, error) {
              console.error('Error:', error);
            }
        });
    });

    $('.category-subcategory-selector').on('change', function() {
        var selectedValue = $(this).val();
        var newOptionsHTML = '';

        tempOptions('subcategory_id');

        $.ajax({
            url: '/ajax/get-category-subcategories', // Replace with your actual endpoint
            method: 'GET',
            data: {
              category_id: selectedValue
            },
            success: function(response) {
                newOptionsHTML += '<option value="">Select a subcategory</option>';

                response.forEach(function(option) {
                    newOptionsHTML += '<option value="' + option.id + '">' + option.name + '</option>';
                });

                changeOptionsAndReinitialize('subcategory_id', newOptionsHTML);
            },
            error: function(xhr, status, error) {
              console.error('Error:', error);
            }
        });
    });

    $('.company-cluster-selector').on('change', function() {
        var selectedValue = $(this).val();
        var newOptionsHTML = '';

        tempOptions('cluster_id');

        $.ajax({
            url: '/ajax/get-clusters', // Replace with your actual endpoint
            method: 'GET',
            data: {
              company_id: selectedValue
            },
            success: function(response) {
                newOptionsHTML += '<option value="">Select a cluster</option>';

                response.forEach(function(option) {
                    newOptionsHTML += '<option value="' + option.id + '">' + option.name + '</option>';
                });

                changeOptionsAndReinitialize('cluster_id', newOptionsHTML);
            },
            error: function(xhr, status, error) {
              console.error('Error:', error);
            }
        });
    });

    $('#region_id').on('change', function() {
        var selectedValue = $(this).val();
        var newOptionsHTML = '';

        tempOptions('province_id');
        tempOptions('city_id');
        tempOptions('barangay_id');

        $.ajax({
            url: '/ajax/get-provinces', // Replace with your actual endpoint
            method: 'GET',
            data: {
              region_id: selectedValue
            },
            success: function(response) {
                newOptionsHTML += '<option value="">Select a province</option>';

                response.forEach(function(option) {
                    newOptionsHTML += '<option value="' + option.id + '">' + option.name + '</option>';
                });

                changeOptionsAndReinitialize('province_id', newOptionsHTML);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });

    $('#province_id').on('change', function() {
        var selectedValue = $(this).val();
        var newOptionsHTML = '';

        tempOptions('city_id');
        tempOptions('barangay_id');

        $.ajax({
            url: '/ajax/get-cities', // Replace with your actual endpoint
            method: 'GET',
            data: {
                province_id: selectedValue
            },
            success: function(response) {
                newOptionsHTML += '<option value="">Select a city</option>';

                response.forEach(function(option) {
                    newOptionsHTML += '<option value="' + option.id + '">' + option.name + '</option>';
                });

                changeOptionsAndReinitialize('city_id', newOptionsHTML);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });

    $('#city_id').on('change', function() {
        var selectedValue = $(this).val();
        var newOptionsHTML = '';

        tempOptions('barangay_id');

        $.ajax({
            url: '/ajax/get-barangays', // Replace with your actual endpoint
            method: 'GET',
            data: {
                city_id: selectedValue
            },
            success: function(response) {
                newOptionsHTML += '<option value="">Select a barangay</option>';

                response.forEach(function(option) {
                    newOptionsHTML += '<option value="' + option.id + '">' + option.name + '</option>';
                });

                changeOptionsAndReinitialize('barangay_id', newOptionsHTML);
            },
            error: function(xhr, status, error) {
              console.error('Error:', error);
            }
        });
    });

    function tempOptions(targetElement) {
        var newOptionsHTML = '<option value="">Select a ' + targetElement + '</option>';3

        $('#' + targetElement).empty();

        $('#' + targetElement).html(newOptionsHTML);

        $('#' + targetElement).select2();
    }

    // Function to change options via HTML and reinitialize Select2
    function changeOptionsAndReinitialize(targetElement, newOptions) {
        $('#' + targetElement).empty();

        $('#' + targetElement).html(newOptions);

        $('#' + targetElement).select2();
    }

    $('.repeater').repeater({
        initEmpty: $('.repeater').data('init-empty') || false,
        defaultValues: {
            'text-input': 'foo'
        },
        show: function () {
            $(this).slideDown();
            $('.repeater').find('.select2-container').remove();
            $('.repeater').find('.select2-ajax').removeAttr("data-kt-initialized");

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
    var elements = [].slice.call(document.querySelectorAll('[data-control="select2"], [data-kt-select2="true"]'));

    elements.map(function (element) {
        if (element.getAttribute("data-kt-initialized") === "1") {
            return;
        }

        var options = {
            dir: document.body.getAttribute('direction')
        };

        if (element.getAttribute('data-hide-search') == 'true') {
            options.minimumResultsForSearch = Infinity;
        }

        if (element.hasAttribute('data-ajax-url')) {
            var query = {};

            if (element.getAttribute('data-param-link')) {
                var paramLink = element.getAttribute('data-param-link');
                var paramName = element.getAttribute('data-param-name');
                var paramValueElement = document.querySelector(paramLink);

                var query = {};

                query[paramName] = paramValueElement.value;
            }

            options.ajax = {
                url: element.getAttribute('data-ajax-url'),
                dataType: 'json',
                delay: 250,
                cache: true,
                data: function (params) {
                    return query;
                }
            };
        }

        $(element).select2(options);

        element.setAttribute("data-kt-initialized", "1");
    });
}

$(document).on('click', '.disable-on-click', function (e) {
    $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Please wait...');
    $(this).prop('disabled', true);

    //check if element has data-button-link
    if ($(this).data('button-link')) {
        var target = $(this).data('button-link');
        $(target).val($(this).val());
    }

    $(this).closest('form').submit();
});

$(document).on('change', '.permission-parent', function (e) {
    var permissionId = $(this).attr('data-permission-id');
    var isChecked = $(this).is(':checked');

    $('.permission-child[data-parent-id="' + permissionId + '"]').prop('checked', isChecked);
    $('.permission-grandchild[data-grandparent-id="' + permissionId + '"]').prop('checked', isChecked);
});


$(document).on('change', '.permission-child', function (e) {
    var parentId = $(this).attr('data-parent-id');

    var allChildren = $('.permission-child[data-parent-id="' + parentId + '"]');
    var atLeastOneChecked = allChildren.is(':checked');
    $('.permission-parent[data-permission-id="' + parentId + '"]').prop('checked', atLeastOneChecked);

    var permissionId = $(this).attr('data-permission-id');
    var isChecked = $(this).is(':checked');
    $('.permission-grandchild[data-parent-id="' + permissionId + '"]').prop('checked', isChecked);
});

$(document).on('change', '.permission-grandchild', function (e) {
    var parentId = $(this).attr('data-parent-id');
    var grandparentId = $(this).attr('data-grandparent-id');

    var allGrandChildren = $('.permission-grandchild[data-parent-id="' + parentId + '"]');
    var atLeastOneChecked = allGrandChildren.is(':checked');
    if (atLeastOneChecked) {
        $('.permission-child[data-permission-id="' + parentId + '"]').prop('checked', atLeastOneChecked);
    }

    var allChildren = $('.permission-child[data-parent-id="' + grandparentId + '"]');
    var atLeastOneChecked = allChildren.is(':checked');

    $('.permission-parent[data-permission-id="' + grandparentId + '"]').prop('checked', atLeastOneChecked);
});