<?php
defined('ABSPATH') || exit;

$post_id = get_the_ID();
$taxonomies = apply_filters('nc_directory_taxonomies', [
    'nc_listing_category',
    'nc_listing_location',
    'nc_listing_type',
]);

echo '<div class="nc-taxonomies">';
foreach ($taxonomies as $taxonomy) {
    $terms = get_the_term_list($post_id, $taxonomy, '<span class="nc-tax ' . esc_attr($taxonomy) . '">', ', ', '</span>');
    if ($terms) {
        echo '<div class="nc-tax-wrap">' . $terms . '</div>';
    }
}
echo '</div>';
?>
