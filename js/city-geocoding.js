jQuery(document).ready(function ($) {
    $('#city_name').on('blur', function () {
        const cityName = $(this).val();
        if (cityName) {
            $.ajax({
                url: geocodingData.ajax_url,
                type: 'POST',
                data: {
                    action: 'fetch_city_geocoding',
                    city: cityName,
                },
                success: function (response) {
                    if (response.success) {
                        $('#temp').val(response.data.temperature);
                        $('#latitude').val(response.data.latitude);
                        $('#longitude').val(response.data.longitude);
                        $('#country').val(response.data.country);
                    } else {
                        alert(response.data.message || 'Error fetching city data.');
                    }
                },
                error: function () {
                    alert('An error occurred while fetching the city data.');
                },
            });
        }
    });
});
