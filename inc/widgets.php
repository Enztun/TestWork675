<?php
/**
 * Dynamic City Weather Widget
 */

// Fetch weather data from OpenWeatherMap API
function fetch_weather_data($city_name) {
    $api_key = '8cd9145e1766b95fa48319342de8e1f8';
    $api_url = "https://api.openweathermap.org/data/2.5/weather?q={$city_name}&appid={$api_key}&units=metric";

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return false; 
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($data['main'])) {
        return $data; 
    }

    return false; 
}

// Get cached weather data or fetch fresh data
function get_cached_weather_data($city_name) {
    $cache_key = 'weather_data_' . sanitize_title($city_name);
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        return $cached_data; 
    }

    $fresh_data = fetch_weather_data($city_name);
    if ($fresh_data) {
        set_transient($cache_key, $fresh_data, 300); // Cache for 5 minutes
    }

    return $fresh_data;
}

// City Weather Widget Class
class Dynamic_City_Weather_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'dynamic_city_weather_widget',
            __('Dynamic City Weather Widget', 'text_domain'),
            ['description' => __('Displays weather data for the current city dynamically.', 'text_domain')]
        );
    }

    // Output the widget content
    public function widget($args, $instance) {
        global $post;

        echo $args['before_widget'];

        // Check if we're on a `cities` post type
        if (is_singular('cities') && isset($post)) {
            $city_name = get_the_title($post->ID); 
        } else {
            $city_name = !empty($instance['default_city']) ? $instance['default_city'] : 'Jakarta';
        }

        $weather_data = get_cached_weather_data($city_name);

        if ($weather_data) {
            $temperature = $weather_data['main']['temp'] . '°C';
            $description = ucfirst($weather_data['weather'][0]['description']);
            $humidity = $weather_data['main']['humidity'] . '%';

            echo $args['before_title'] . esc_html__('Weather in ' . $city_name, 'text_domain') . $args['after_title'];
            echo "<p><strong>Temperature:</strong> {$temperature}</p>";
            echo "<p><strong>Description:</strong> {$description}</p>";
            echo "<p><strong>Humidity:</strong> {$humidity}</p>";
        } else {
            echo '<p>' . esc_html__('Weather data is currently unavailable.', 'text_domain') . '</p>';
        }

        echo $args['after_widget'];
    }

    // Display the widget form in the admin
    public function form($instance) {
        $default_city = !empty($instance['default_city']) ? $instance['default_city'] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('default_city')); ?>"><?php _e('Default City Name:', 'text_domain'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('default_city')); ?>" name="<?php echo esc_attr($this->get_field_name('default_city')); ?>" type="text" value="<?php echo esc_attr($default_city); ?>">
        </p>
        <?php
    }

    // Save widget settings
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['default_city'] = (!empty($new_instance['default_city'])) ? sanitize_text_field($new_instance['default_city']) : '';
        return $instance;
    }
}

// Register the widget
function register_dynamic_city_weather_widget() {
    register_widget('Dynamic_City_Weather_Widget');
}
add_action('widgets_init', 'register_dynamic_city_weather_widget');
