<?php
defined('ABSPATH') || exit;

echo '<div class="nc-thumbnail">';

if (has_post_thumbnail()) {
    the_post_thumbnail('large', ['loading' => 'lazy', 'decoding' => 'async']);
} else {
    echo '<img src="' . esc_url(plugins_url('../assets/images/fallback-thumbnail.png', __FILE__)) . '" alt="' . esc_attr__('No image available', 'nicheclassify') . '" loading="lazy" decoding="async">';
}

echo '</div>';
