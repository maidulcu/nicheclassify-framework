<?php

namespace NicheClassify\PostTypes;

defined('ABSPATH') || exit;

/**
 * Register core custom post types for NicheClassify Framework.
 */
class RegisterTypes {
    public static function init() {
        add_action('init', [self::class, 'register_post_types']);
    }

    public static function register_post_types() {
        $default_args = [
            'label' => __('Listing', 'nicheclassify'),
            'labels' => [
                'name' => __('Listings', 'nicheclassify'),
                'singular_name' => __('Listing', 'nicheclassify'),
                'add_new' => __('Add New', 'nicheclassify'),
                'add_new_item' => __('Add New Listing', 'nicheclassify'),
                'edit_item' => __('Edit Listing', 'nicheclassify'),
                'new_item' => __('New Listing', 'nicheclassify'),
                'view_item' => __('View Listing', 'nicheclassify'),
                'search_items' => __('Search Listings', 'nicheclassify'),
                'not_found' => __('No listings found', 'nicheclassify'),
                'not_found_in_trash' => __('No listings found in Trash', 'nicheclassify'),
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'listing'],
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'author'],
            'menu_icon' => 'dashicons-list-view',
            'show_in_rest' => true,
        ];

        $args = apply_filters('nc_register_listing_post_type_args', $default_args);

        register_post_type('nc_listing', $args);
    }
}
