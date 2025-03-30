<?php

namespace NicheClassify\Directory;

// Class Nc Directory.Php

defined('ABSPATH') || exit;

class Directory_Renderer {

    public function __construct() {
        add_shortcode('nc_listing_gallery', [$this, 'render_gallery_shortcode']);
        add_shortcode('nc_directory', [$this, 'render_directory_shortcode']);
        add_filter('the_content', [$this, 'render_single_listing_content']);
        add_action('nc_directory_listing_item', [$this, 'render_loop_item']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            'nicheclassify-directory',
            plugins_url('../assets/js/nicheclassify-directory.js', __FILE__),
            ['jquery'],
            '1.0',
            true
        );

        wp_enqueue_style(
            'nicheclassify-directory-style',
            plugins_url('../assets/css/nicheclassify-directory.css', __FILE__),
            [],
            '1.0'
        );
    }

    public function render_gallery_shortcode($atts) {
        $atts = shortcode_atts([
            'id' => get_the_ID(),
        ], $atts, 'nc_listing_gallery');

        $post_id = absint($atts['id']);
        if (!$post_id) return '';

        $image_ids = get_post_meta($post_id, 'gallery_image_ids', true);
        if (empty($image_ids) || !is_array($image_ids)) {
            return '<p>' . esc_html__('No gallery images available.', 'nicheclassify') . '</p>';
        }

        ob_start();
        echo '<div class="nc-listing-gallery">';
        foreach ($image_ids as $image_id) {
            $img_url = wp_get_attachment_image_url($image_id, 'medium');
            if ($img_url) {
                printf('<div class="nc-gallery-item"><img src="%s" alt="" /></div>', esc_url($img_url));
            }
        }
        echo '</div>';
        return ob_get_clean();
    }

    public function render_directory_shortcode($atts) {
        $atts = shortcode_atts([
            'posts_per_page' => 10,
            'paged' => get_query_var('paged') ?: 1,
        ], $atts, 'nc_directory');

        if (defined('DOING_AJAX') || isset($_GET['ajax'])) {
            add_filter('template_include', function($template) {
                return plugin_dir_path(__FILE__) . 'ajax-template.php';
            });
        }

        $taxonomies = apply_filters('nc_directory_taxonomies', ['nc_listing_category', 'nc_listing_location', 'nc_listing_type']);
        $tax_query = [];
        foreach ($taxonomies as $taxonomy) {
            if (!empty($_GET[$taxonomy])) {
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => sanitize_text_field($_GET[$taxonomy]),
                ];
            }
        }

        $args = [
            'post_type' => 'nc_listing',
            'post_status' => 'publish',
            'posts_per_page' => intval($atts['posts_per_page']),
            'paged' => intval($atts['paged']),
        ];

        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }

        if (!empty($_GET['s'])) {
            $args['s'] = sanitize_text_field($_GET['s']);
        }

        // Radius-based filtering
        $lat = isset($_GET['location_lat']) ? floatval($_GET['location_lat']) : null;
        $lng = isset($_GET['location_lng']) ? floatval($_GET['location_lng']) : null;
        $radius_km = isset($_GET['radius_km']) ? floatval($_GET['radius_km']) : null;

        if ($lat && $lng && $radius_km) {
            add_filter('posts_where', function ($where, $query) use ($lat, $lng, $radius_km) {
                global $wpdb;

                $earth_radius_km = 6371;

                $where .= $wpdb->prepare(
                    " AND (
                        $earth_radius_km * ACOS(
                            COS(RADIANS(%f)) * COS(RADIANS(CAST(pm_lat.meta_value AS DECIMAL(10,6)))) *
                            COS(RADIANS(CAST(pm_lng.meta_value AS DECIMAL(10,6))) - RADIANS(%f)) +
                            SIN(RADIANS(%f)) * SIN(RADIANS(CAST(pm_lat.meta_value AS DECIMAL(10,6))))
                        )
                    ) <= %f",
                    $lat, $lng, $lat, $radius_km
                );

                return $where;
            }, 10, 2);

            $args['meta_query'][] = [
                'key' => 'location_lat',
                'compare' => 'EXISTS',
            ];
            $args['meta_query'][] = [
                'key' => 'location_lng',
                'compare' => 'EXISTS',
            ];

            $args['meta_query']['relation'] = 'AND';
            $args['meta_key'] = 'location_lat';

            add_filter('posts_join', function ($join) {
                global $wpdb;
                $join .= " LEFT JOIN {$wpdb->postmeta} AS pm_lat ON ({$wpdb->posts}.ID = pm_lat.post_id AND pm_lat.meta_key = 'location_lat')";
                $join .= " LEFT JOIN {$wpdb->postmeta} AS pm_lng ON ({$wpdb->posts}.ID = pm_lng.post_id AND pm_lng.meta_key = 'location_lng')";
                return $join;
            });
        }

        $args['location_lat'] = $lat;
        $args['location_lng'] = $lng;
        $args['radius_km'] = $radius_km;

        $sort = $_GET['sort'] ?? '';
        $sort_options = apply_filters('nc_directory_sort_options', [
            'date_desc'  => __('Newest First', 'nicheclassify'),
            'date_asc'   => __('Oldest First', 'nicheclassify'),
            'title_asc'  => __('Title A-Z', 'nicheclassify'),
            'title_desc' => __('Title Z-A', 'nicheclassify'),
            'price_asc'  => __('Price (Low to High)', 'nicheclassify'),
            'price_desc' => __('Price (High to Low)', 'nicheclassify'),
        ]);

        $meta_sorting = apply_filters('nc_directory_meta_sorting', [
            'age' => ['meta_key' => 'pet_age', 'orderby' => 'meta_value_num'],
            'price' => ['meta_key' => 'listing_price', 'orderby' => 'meta_value_num'],
        ]);

        switch ($sort) {
            case 'date_asc':
                $args['orderby'] = 'date';
                $args['order'] = 'ASC';
                break;
            case 'title_asc':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'title_desc':
                $args['orderby'] = 'title';
                $args['order'] = 'DESC';
                break;
            default:
                if (preg_match('/^([a-z0-9_]+)_(asc|desc)$/', $sort, $matches)) {
                    $field = $matches[1];
                    $order = strtoupper($matches[2]);
                    if (isset($meta_sorting[$field])) {
                        $args['meta_key'] = $meta_sorting[$field]['meta_key'];
                        $args['orderby'] = $meta_sorting[$field]['orderby'];
                        $args['order'] = $order;
                        break;
                    }
                }
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }

        $args = apply_filters('nc_directory_query_args', $args);
        $query = new WP_Query($args);

        ob_start();

        $template = locate_template('nicheclassify/directory/filters.php');
        if (!$template) {
            $template = plugin_dir_path(__DIR__) . '../templates/directory/filters.php';
        }
        include $template;

        echo '<div id="nc-ajax-listings">';
        if ($query->have_posts()) {
            echo '<div class="nc-directory-listings">';
            while ($query->have_posts()) {
                $query->the_post();
                do_action('nc_directory_listing_item', get_the_ID());
            }
            echo '</div>';

            $big = 999999999;
            $pagination = paginate_links([
                'base' => str_replace($big, '%#%', esc_url(add_query_arg('paged', $big))),
                'format' => '',
                'current' => max(1, intval(get_query_var('paged'))),
                'total' => $query->max_num_pages,
                'type' => 'array',
                'prev_text' => __('« Prev', 'nicheclassify'),
                'next_text' => __('Next »', 'nicheclassify'),
            ]);

            if ($pagination) {
                $pagination_template = locate_template('nicheclassify/directory/pagination.php');
                if (!$pagination_template) {
                    $pagination_template = plugin_dir_path(__DIR__) . '../templates/directory/pagination.php';
                }
                include $pagination_template;
            }

            wp_reset_postdata();
        } else {
            echo '<p>' . esc_html__('No listings found.', 'nicheclassify') . '</p>';
        }
        echo '</div>';

        return ob_get_clean();
    }

    public function render_single_listing_content($content) {
        if (!is_singular('nc_listing') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        ob_start();
        $template = locate_template('nicheclassify/single-nc_listing.php');
        if (!$template) {
            $template = plugin_dir_path(__DIR__) . '../templates/single-nc_listing.php';
        }
        include $template;
        return ob_get_clean();
    }

    public function render_loop_item($post_id) {
        $template = locate_template('nicheclassify/directory/loop-item.php');
        if (!$template) {
            $template = plugin_dir_path(__DIR__) . '../templates/directory/loop-item.php';
        }

        // Optional: compute distance from user's position if present
        $user_lat = isset($_GET['location_lat']) ? floatval($_GET['location_lat']) : null;
        $user_lng = isset($_GET['location_lng']) ? floatval($_GET['location_lng']) : null;
        $post_lat = get_post_meta($post_id, 'location_lat', true);
        $post_lng = get_post_meta($post_id, 'location_lng', true);

        $distance_km = null;
        if ($user_lat && $user_lng && $post_lat && $post_lng) {
            $earth_radius = 6371;
            $lat1 = deg2rad($user_lat);
            $lng1 = deg2rad($user_lng);
            $lat2 = deg2rad(floatval($post_lat));
            $lng2 = deg2rad(floatval($post_lng));

            $delta_lat = $lat2 - $lat1;
            $delta_lng = $lng2 - $lng1;

            $a = sin($delta_lat/2) * sin($delta_lat/2) +
                 cos($lat1) * cos($lat2) * sin($delta_lng/2) * sin($delta_lng/2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance_km = $earth_radius * $c;
        }

        // Make distance available globally to loop-item.php
        if (!empty($distance_km)) {
            $GLOBALS['nc_listing_distance_km'] = round($distance_km, 1);
        } else {
            $GLOBALS['nc_listing_distance_km'] = null;
        }

        include $template;
    }
}

// Initialize
$GLOBALS['nc_directory_renderer'] = new NC_Directory_Renderer();
