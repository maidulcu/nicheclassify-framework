<?php
defined('ABSPATH') || exit;

$post_id = get_the_ID();
$is_featured = get_post_meta($post_id, 'is_featured', true) === 'yes';

if ($is_featured) {
    echo '<div class="nc-featured-badge">' . esc_html__('Featured', 'nicheclassify') . '</div>';
}
?>
