<?php
defined('ABSPATH') || exit;

$taxonomies = apply_filters('nc_directory_taxonomies', [
    'nc_listing_category',
    'nc_listing_location',
    'nc_listing_type',
]);

echo '<form method="get" class="nc-directory-filters">';

// Keyword search
echo '<input type="text" name="s" placeholder="' . esc_attr__('Search listings...', 'nicheclassify') . '" value="' . esc_attr($_GET['s'] ?? '') . '"> ';

// Address input and map container
echo '<input type="text" name="location_address" id="location_address" placeholder="' . esc_attr__('Enter a location...', 'nicheclassify') . '" value="' . esc_attr($_GET['location_address'] ?? '') . '">';
echo '<input type="hidden" name="location_lat" id="location_lat" value="' . esc_attr($args['location_lat'] ?? '') . '">';
echo '<input type="hidden" name="location_lng" id="location_lng" value="' . esc_attr($args['location_lng'] ?? '') . '">';
echo '<div id="nc-location-map" style="height: 250px; margin: 1rem 0;"></div>';
echo '<button type="button" class="button nc-use-my-location">' . esc_html__('Use My Location', 'nicheclassify') . '</button>';

// Radius
echo '<select name="radius_km">';
echo '<option value="">' . esc_html__('Radius (km)', 'nicheclassify') . '</option>';
foreach ([5, 10, 25, 50, 100, 250] as $radius) {
    $selected = selected($_GET['radius_km'] ?? '', $radius, false);
    echo '<option value="' . esc_attr($radius) . '" ' . $selected . '>' . esc_html($radius . ' km') . '</option>';
}
echo '</select> ';

// Taxonomy dropdowns
foreach ($taxonomies as $taxonomy) {
    $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
    if (!empty($terms) && !is_wp_error($terms)) {
        echo '<select name="' . esc_attr($taxonomy) . '">';
        echo '<option value="">' . esc_html(ucfirst(str_replace('nc_listing_', '', $taxonomy))) . '</option>';
        foreach ($terms as $term) {
            $selected = selected($_GET[$taxonomy] ?? '', $term->slug, false);
            echo '<option value="' . esc_attr($term->slug) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
        }
        echo '</select> ';
    }
}

// Sort dropdown
$sort_options = apply_filters('nc_directory_sort_options', [
    'date_desc'  => __('Newest First', 'nicheclassify'),
    'date_asc'   => __('Oldest First', 'nicheclassify'),
    'title_asc'  => __('Title A-Z', 'nicheclassify'),
    'title_desc' => __('Title Z-A', 'nicheclassify'),
    'age_asc'    => __('Age (Youngest First)', 'nicheclassify'),
    'age_desc'   => __('Age (Oldest First)', 'nicheclassify'),
    'price_asc'  => __('Price (Low to High)', 'nicheclassify'),
    'price_desc' => __('Price (High to Low)', 'nicheclassify'),
]);

echo '<select name="sort">';
echo '<option value="">' . esc_html__('Sort By', 'nicheclassify') . '</option>';
foreach ($sort_options as $key => $label) {
    $selected = selected($_GET['sort'] ?? '', $key, false);
    echo '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($label) . '</option>';
}
echo '</select> ';

// Submit
echo '<button type="submit">' . esc_html__('Filter', 'nicheclassify') . '</button>';
echo '</form>';
