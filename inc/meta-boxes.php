<?php
// The reason i made this Meta Boxes like this is so the user should only enter the City name and VOILA ! latitude and longitude are generated automatically.
function enqueue_meta_box_scripts($hook) {
    global $post_type;
    if (($hook === 'post.php' || $hook === 'post-new.php') && $post_type === 'cities') {
        wp_enqueue_script('city-geocoding-script', get_stylesheet_directory_uri() . '/js/city-geocoding.js', ['jquery'], null, true);
        wp_localize_script('city-geocoding-script', 'geocodingData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'api_key'  => '8cd9145e1766b95fa48319342de8e1f8', //This is my free usage API key from openweather, it's okay to use it.
        ]);
    }
}
add_action('admin_enqueue_scripts', 'enqueue_meta_box_scripts');

// AJAX handler for city geocoding
function fetch_city_geocoding() {
   
    $city = sanitize_text_field($_POST['city']);
    $api_key = '8cd9145e1766b95fa48319342de8e1f8';
    $api_url = "http://api.openweathermap.org/geo/1.0/direct?q={$city}&limit=1&appid={$api_key}"; //This kind of magic in the UX saves my days many times.

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => 'Error fetching data']);
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (!empty($data) && isset($data[0])) {
        $result = [
            'latitude'  => $data[0]['lat'],
            'longitude' => $data[0]['lon'],
            'country'   => $data[0]['country'],
        ];
        wp_send_json_success($result);
    } else {
        wp_send_json_error(['message' => 'City not found']);
    }
}

add_action('wp_ajax_fetch_city_geocoding', 'fetch_city_geocoding');
add_action('wp_ajax_nopriv_fetch_city_geocoding', 'fetch_city_geocoding');

function add_city_coordinates_meta_box() {
    add_meta_box(
        'city_coordinates',                  
        __('City Coordinates', 'text_domain'), 
        'render_city_coordinates_meta_box',  
        'cities',                            
        'normal',                           
        'high'                               
    );
}
add_action('add_meta_boxes', 'add_city_coordinates_meta_box');


function render_city_coordinates_meta_box($post) {
    $latitude = get_post_meta($post->ID, 'latitude', true);
    $longitude = get_post_meta($post->ID, 'longitude', true);
    $country = get_post_meta($post->ID, 'country', true);

    ?>
    <p>
        <label for="city_name"><?php _e('City Name', 'text_domain'); ?></label>
        <input type="text" id="city_name" name="city_name" value="<?php echo esc_attr(get_the_title($post)); ?>" class="widefat">
    </p>
    <p>
        <label for="latitude"><?php _e('Latitude', 'text_domain'); ?></label>
        <input type="text" id="latitude" name="latitude" value="<?php echo esc_attr($latitude); ?>" class="widefat" readonly>
    </p>
    <p>
        <label for="longitude"><?php _e('Longitude', 'text_domain'); ?></label>
        <input type="text" id="longitude" name="longitude" value="<?php echo esc_attr($longitude); ?>" class="widefat" readonly>
    </p>
    <p>
        <label for="country"><?php _e('Country', 'text_domain'); ?></label>
        <input type="text" id="country" name="country" value="<?php echo esc_attr($country); ?>" class="widefat" readonly>
    </p>
    <?php
}

function save_city_meta_boxes($post_id) {
    if (isset($_POST['latitude'])) {
        update_post_meta($post_id, 'latitude', sanitize_text_field($_POST['latitude']));
    }
    if (isset($_POST['longitude'])) {
        update_post_meta($post_id, 'longitude', sanitize_text_field($_POST['longitude']));
    }
    if (isset($_POST['country'])) {
        update_post_meta($post_id, 'country', sanitize_text_field($_POST['country']));
    }
}
add_action('save_post', 'save_city_meta_boxes');

function update_city_weather_data($post_id) {
    if (get_post_type($post_id) !== 'cities') {
        return;
    }

    $city_name = get_the_title($post_id);
    $weather_data = fetch_weather_data($city_name);

    if ($weather_data) {
        update_post_meta($post_id, 'temperature', $weather_data['main']['temp']);
        update_post_meta($post_id, 'description', $weather_data['weather'][0]['description']);
        update_post_meta($post_id, 'humidity', $weather_data['main']['humidity']);
        update_post_meta($post_id, 'last_updated', current_time('mysql'));
    }
}
add_action('save_post', 'update_city_weather_data');
