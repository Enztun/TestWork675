<?php
// Hook child theme to its parent~
add_action('wp_enqueue_scripts', 'enqueue_parent_styles');
function enqueue_parent_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}

// Include necessary files...
require_once get_theme_file_path('inc/custom-post-types.php');
require_once get_theme_file_path('inc/meta-boxes.php');
require_once get_theme_file_path('inc/taxonomies.php');
require_once get_theme_file_path('inc/widgets.php');

// Enqueue scripts and styles for DataTables
function enqueue_countries_cities_assets() {
    if (is_page_template('template-countries-cities.php') || has_shortcode(get_post()->post_content, 'countries_cities_table')) {
        // DataTables
        wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js', ['jquery'], null, true);
        wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css');

        // Cities AJAX script
        wp_enqueue_script('cities-ajax', get_stylesheet_directory_uri() . '/js/cities-ajax.js', ['jquery', 'datatables-js'], null, true);
        wp_localize_script('cities-ajax', 'storefront_cities_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_countries_cities_assets');

// Register Countries taxonomy
add_action('init', 'register_country_taxonomy');
function register_country_taxonomy() {
    register_taxonomy(
        'countries',
        'cities',
        [
            'label' => __('Countries'),
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'country'],
        ]
    );
}

// Register rewrite rule and query vars
add_action('init', function () {
    add_rewrite_rule('^cities/([^/]*)/?', 'index.php?city=$matches[1]', 'top');
});
add_filter('query_vars', function ($vars) {
    $vars[] = 'city'; 
    return $vars;
});

// Shortcode to display countries and cities table
add_shortcode('countries_cities_table', 'display_countries_cities_table');
function display_countries_cities_table() {
    ob_start();
    do_action('before_countries_cities_table');
    echo '<div id="countries-cities-table-container">';
    echo '<table id="countries-cities-table" class="display">';
    echo '<thead><tr><th>Country</th><th>City</th><th>Latitude</th><th>Longitude</th><th>Temperature</th></tr></thead><tbody>';

    global $wpdb;
    $results = $wpdb->get_results(
        "
        SELECT 
            p.ID, 
            p.post_title AS city_name, 
            pm_lat.meta_value AS latitude, 
            pm_lng.meta_value AS longitude, 
            pm_temp.meta_value AS temperature, 
            t.name AS country
        FROM {$wpdb->posts} AS p
        LEFT JOIN {$wpdb->postmeta} AS pm_lat 
            ON p.ID = pm_lat.post_id AND pm_lat.meta_key = 'latitude'
        LEFT JOIN {$wpdb->postmeta} AS pm_lng 
            ON p.ID = pm_lng.post_id AND pm_lng.meta_key = 'longitude'
        LEFT JOIN {$wpdb->postmeta} AS pm_temp 
            ON p.ID = pm_temp.post_id AND pm_temp.meta_key = 'temperature'
        LEFT JOIN {$wpdb->term_relationships} AS tr 
            ON p.ID = tr.object_id
        LEFT JOIN {$wpdb->term_taxonomy} AS tt 
            ON tr.term_taxonomy_id = tt.term_taxonomy_id
        LEFT JOIN {$wpdb->terms} AS t 
            ON tt.term_id = t.term_id
        WHERE p.post_type = 'cities'
        AND p.post_status = 'publish'
        "
    );
    
    if (!empty($results)) {
        foreach ($results as $result) {
            echo '<tr>';
            echo '<td>' . esc_html($result->country) . '</td>';
            echo '<td>' . esc_html($result->city_name) . '</td>';
            echo '<td>' . esc_html($result->latitude) . '</td>';
            echo '<td>' . esc_html($result->longitude) . '</td>';
            echo '<td>' . esc_html($result->temperature) . ' °C</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5">No data found</td></tr>';
    }

    echo '</tbody></table></div>';
    do_action('after_countries_cities_table');
    return ob_get_clean();
}

// Custom content hooks before and after the table
add_action('before_countries_cities_table', 'add_custom_content_before_table');
function add_custom_content_before_table() {
    echo '<div class="custom-content-before"><p>Welcome! Here’s a list of cities and countries.</p></div>';
}

add_action('after_countries_cities_table', 'add_custom_content_after_table');
function add_custom_content_after_table() {
    echo '<div class="custom-content-after"><p>Thank you for viewing the list. Have a great day!</p></div>';
}

// Thanks for viewing my code !