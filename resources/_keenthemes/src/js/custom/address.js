$(document).ready(function() {
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