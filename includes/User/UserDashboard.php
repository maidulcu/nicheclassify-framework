<?php

namespace NicheClassify\User;

// Class Nc User Dashboard.Php

defined('ABSPATH') || exit;

class UserDashboard {
    protected static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_shortcode('nc_dashboard', [$this, 'render_dashboard']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    public function enqueue_styles() {
        if (has_shortcode(get_post()->post_content ?? '', 'nc_dashboard')) {
            wp_enqueue_style(
                'nc-dashboard-style',
                plugins_url('../assets/css/nicheclassify-dashboard.css', __FILE__),
                [],
                '1.0'
            );
        }
    }

    public function render_dashboard() {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('You must be logged in to view your listings.', 'nicheclassify') . '</p>';
        }

        $user_id = get_current_user_id();

        // Handle deletion of listings
        if (isset($_GET['nc_delete_listing']) && is_numeric($_GET['nc_delete_listing'])) {
            $delete_id = absint($_GET['nc_delete_listing']);
            if (get_post_field('post_author', $delete_id) == $user_id && get_post_type($delete_id) === 'nc_listing') {
                wp_trash_post($delete_id);
                echo '<div class="notice notice-success"><p>' . esc_html__('Listing deleted.', 'nicheclassify') . '</p></div>';
            }
        }

        $args = [
            'post_type'      => 'nc_listing',
            'post_status'    => ['publish', 'pending', 'draft'],
            'author'         => $user_id,
            'posts_per_page' => -1,
        ];

        $query = new WP_Query($args);

        ob_start();
        echo '<div class="nc-user-dashboard">';
        echo '<h3>' . esc_html__('My Listings', 'nicheclassify') . '</h3>';

        if ($query->have_posts()) {
            echo '<table>';
            echo '<thead><tr><th>' . esc_html__('Title', 'nicheclassify') . '</th><th>' . esc_html__('Status', 'nicheclassify') . '</th><th>' . esc_html__('Date', 'nicheclassify') . '</th><th>' . esc_html__('Actions', 'nicheclassify') . '</th></tr></thead>';
            echo '<tbody>';

            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                echo '<tr>';
                echo '<td data-label="' . esc_attr__('Title', 'nicheclassify') . '"><a href="' . esc_url(get_permalink($post_id)) . '">' . esc_html(get_the_title($post_id)) . '</a></td>';
                $status = get_post_status($post_id);
                $status_label = '';
                switch ($status) {
                    case 'publish':
                        $status_label = __('Approved', 'nicheclassify');
                        break;
                    case 'pending':
                        $status_label = __('Pending Review', 'nicheclassify');
                        break;
                    case 'draft':
                        $status_label = __('Draft', 'nicheclassify');
                        break;
                    default:
                        $status_label = ucfirst($status);
                        break;
                }
                echo '<td data-label="' . esc_attr__('Status', 'nicheclassify') . '">' . esc_html($status_label) . '</td>';
                echo '<td data-label="' . esc_attr__('Date', 'nicheclassify') . '">' . esc_html(get_the_date('', $post_id)) . '</td>';
                $delete_url = add_query_arg('nc_delete_listing', $post_id);
                echo '<td data-label="' . esc_attr__('Actions', 'nicheclassify') . '"><a href="' . esc_url(get_edit_post_link($post_id)) . '">' . esc_html__('Edit', 'nicheclassify') . '</a> | ';
                echo '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Are you sure you want to delete this listing?\');">' . esc_html__('Delete', 'nicheclassify') . '</a></td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
            wp_reset_postdata();
        } else {
            echo '<p>' . esc_html__('You have not submitted any listings yet.', 'nicheclassify') . '</p>';
        }

        echo '</div>';
        return ob_get_clean();
    }
}
