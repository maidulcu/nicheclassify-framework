<?php
defined('ABSPATH') || exit;

$post_id = get_the_ID();
$gallery_ids = get_post_meta($post_id, 'gallery_image_ids', true);

if (!empty($gallery_ids) && is_array($gallery_ids)) {
    echo '<div class="nc-gallery">';
    foreach ($gallery_ids as $image_id) {
        $img_url = wp_get_attachment_image_url($image_id, 'medium');
        if ($img_url) {
            echo '<div class="nc-gallery-item">';
            echo '<img src="' . esc_url($img_url) . '" alt="" loading="lazy" decoding="async" />';
            echo '</div>';
        }
    }
    echo '</div>';
}
?>
