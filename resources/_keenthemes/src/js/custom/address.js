$(document).ready(function() {
    $('#markup_type').on('change', function() {
        computeSrp();
    });

    $('.compute-srp').on('keyup', function() {
        computeSrp();
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
});