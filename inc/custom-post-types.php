<?php
// Register Cities Custom Post Type
function register_cities_post_type() {
    $labels = array(
        'name'               => _x('Cities', 'post type general name', 'text_domain'),
        'singular_name'      => _x('City', 'post type singular name', 'text_domain'),
        'menu_name'          => __('Cities', 'text_domain'),
        'name_admin_bar'     => __('City', 'text_domain'),
        'add_new'            => __('Add New', 'text_domain'),
        'add_new_item'       => __('Add New City', 'text_domain'),
        'edit_item'          => __('Edit City', 'text_domain'),
        'new_item'           => __('New City', 'text_domain'),
        'view_item'          => __('View City', 'text_domain'),
        'all_items'          => __('All Cities', 'text_domain'),
        'search_items'       => __('Search Cities', 'text_domain'),
        'not_found'          => __('No cities found.', 'text_domain'),
        'not_found_in_trash' => __('No cities found in Trash.', 'text_domain'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'supports'           => array('title', 'editor', 'thumbnail'),
        'rewrite'            => ['slug' => 'cities'],
        'taxonomies'         => ['countries'], 
        'show_in_rest'       => true,
        'show_ui'            => true,

    );

    register_post_type('cities', $args);
}
add_action('init', 'register_cities_post_type');