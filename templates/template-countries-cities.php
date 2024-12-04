<?php
/**
 * Template Name: Countries and Cities Table
 * Description: A custom template to display a table of countries and cities.
 * P.S : The Temperature Widget didn't display in this template, but will be displayed at default template (i put in sidebar template)
 */

get_header(); ?>

<div class="countries-cities-page">
    <?php
    echo do_shortcode('[countries_cities_table]');
    ?>
</div>

<?php get_footer(); ?>
