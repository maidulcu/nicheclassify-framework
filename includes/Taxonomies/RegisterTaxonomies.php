<?php

namespace NicheClassify\Taxonomies;

// Class Nc Taxonomies.Php

defined('ABSPATH') || exit;

/**
 * Register default taxonomies for NicheClassify listings.
 */
function nc_register_taxonomies() {
    // Listing Type taxonomy
    $type_labels = [
        'name'              => __('Listing Types', 'nicheclassify'),
        'singular_name'     => __('Listing Type', 'nicheclassify'),
        'search_items'      => __('Search Types', 'nicheclassify'),
        'all_items'         => __('All Types', 'nicheclassify'),
        'edit_item'         => __('Edit Type', 'nicheclassify'),
        'update_item'       => __('Update Type', 'nicheclassify'),
        'add_new_item'      => __('Add New Type', 'nicheclassify'),
        'new_item_name'     => __('New Type Name', 'nicheclassify'),
        'menu_name'         => __('Types', 'nicheclassify'),
    ];

    register_taxonomy('nc_listing_type', 'nc_listing', [
        'labels' => $type_labels,
        'public' => true,
        'hierarchical' => true,
        'rewrite' => ['slug' => 'listing-type'],
        'show_in_rest' => true,
    ]);

    // Location taxonomy
    $location_labels = [
        'name'              => __('Locations', 'nicheclassify'),
        'singular_name'     => __('Location', 'nicheclassify'),
        'search_items'      => __('Search Locations', 'nicheclassify'),
        'all_items'         => __('All Locations', 'nicheclassify'),
        'edit_item'         => __('Edit Location', 'nicheclassify'),
        'update_item'       => __('Update Location', 'nicheclassify'),
        'add_new_item'      => __('Add New Location', 'nicheclassify'),
        'new_item_name'     => __('New Location Name', 'nicheclassify'),
        'menu_name'         => __('Locations', 'nicheclassify'),
    ];

    register_taxonomy('nc_listing_location', 'nc_listing', [
        'labels' => $location_labels,
        'public' => true,
        'hierarchical' => false,
        'rewrite' => ['slug' => 'location'],
        'show_in_rest' => true,
    ]);

    // Listing Category taxonomy
    $category_labels = [
        'name'              => __('Listing Categories', 'nicheclassify'),
        'singular_name'     => __('Listing Category', 'nicheclassify'),
        'search_items'      => __('Search Categories', 'nicheclassify'),
        'all_items'         => __('All Categories', 'nicheclassify'),
        'edit_item'         => __('Edit Category', 'nicheclassify'),
        'update_item'       => __('Update Category', 'nicheclassify'),
        'add_new_item'      => __('Add New Category', 'nicheclassify'),
        'new_item_name'     => __('New Category Name', 'nicheclassify'),
        'menu_name'         => __('Categories', 'nicheclassify'),
    ];

    register_taxonomy('nc_listing_category', 'nc_listing', [
        'labels' => $category_labels,
        'public' => true,
        'hierarchical' => true,
        'rewrite' => ['slug' => 'listing-category'],
        'show_in_rest' => true,
    ]);
}

add_action('init', 'nc_register_taxonomies');
