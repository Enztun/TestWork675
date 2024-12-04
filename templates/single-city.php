<?php
/**
 * Template for displaying a single city page
 */

get_header();

global $wpdb;

$city_slug = sanitize_title(get_query_var('city_slug'));

if ($city_slug) {
    $city_data = $wpdb->get_row(
        $wpdb->prepare(
            "
            SELECT p.ID, p.post_title, pm_lat.meta_value AS latitude, pm_lng.meta_value AS longitude, pm_temp.meta_value AS temperature, t.name AS country
            FROM {$wpdb->posts} AS p
            LEFT JOIN {$wpdb->postmeta} AS pm_lat ON p.ID = pm_lat.post_id AND pm_lat.meta_key = 'latitude'
            LEFT JOIN {$wpdb->postmeta} AS pm_lng ON p.ID = pm_lng.post_id AND pm_lng.meta_key = 'longitude'
            LEFT JOIN {$wpdb->postmeta} AS pm_temp ON p.ID = pm_temp.post_id AND pm_temp.meta_key = 'temperature'
            LEFT JOIN {$wpdb->term_relationships} AS tr ON p.ID = tr.object_id
            LEFT JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            LEFT JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id
            WHERE p.post_type = 'cities'
            AND p.post_status = 'publish'
            AND p.post_name = %s
            ",
            $city_slug
        )
    );

    if ($city_data) {
        echo '<article class="single-city">';
        echo '<h1>' . esc_html($city_data->post_title) . '</h1>';
        echo '<p><strong>Country:</strong> ' . esc_html($city_data->country) . '</p>';
        echo '<p><strong>Latitude:</strong> ' . esc_html($city_data->latitude) . '</p>';
        echo '<p><strong>Longitude:</strong> ' . esc_html($city_data->longitude) . '</p>';
        echo '<p><strong>Temperature:</strong>' . esc_html($city_data->temperature) . ' °C</p>';
        echo '</article>';
    } else {
        echo '<section class="error-page">';
        echo '<h1>City Not Found</h1>';
        echo '<p>We couldn’t find the city you’re looking for. Please check the URL or try searching again.</p>';
        echo '</section>';
    }
} else {
    echo '<section class="error-page">';
    echo '<h1>No City Specified</h1>';
    echo '<p>Please specify a city to view its details.</p>';
    echo '</section>';
}

get_footer();
