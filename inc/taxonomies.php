 <?php
 // Register Countries Taxonomy
 function register_countries_taxonomy() {
     $labels = [
         'name'              => _x('Countries', 'taxonomy general name', 'text_domain'),
         'singular_name'     => _x('Country', 'taxonomy singular name', 'text_domain'),
         'search_items'      => __('Search Countries', 'text_domain'),
         'all_items'         => __('All Countries', 'text_domain'),
         'parent_item'       => null, // No parent-child for countries !
         'parent_item_colon' => null,
         'edit_item'         => __('Edit Country', 'text_domain'),
         'update_item'       => __('Update Country', 'text_domain'),
         'add_new_item'      => __('Add New Country', 'text_domain'),
         'new_item_name'     => __('New Country Name', 'text_domain'),
         'menu_name'         => __('Countries', 'text_domain'),
     ];
 
     $args = [
         'hierarchical'      => true,
         'labels'            => $labels,
         'show_ui'           => true,
         'show_admin_column' => true,
         'query_var'         => true,
         'rewrite'           => ['slug' => 'countries'],
     ];
 
     register_taxonomy('countries', ['cities'], $args);
 }
 add_action('init', 'register_countries_taxonomy');
