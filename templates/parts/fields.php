<?php
defined('ABSPATH') || exit;

$post_id = get_the_ID();
$fields = nc_get_field_schema();

if (!empty($fields)) {
    echo '<ul class="nc-fields">';
    foreach ($fields as $key => $field) {
        $value = get_post_meta($post_id, $key, true);
        if ($value !== '') {
            echo '<li><strong>' . esc_html($field['label']) . ':</strong> ' . esc_html($value) . '</li>';
        }
    }
    echo '</ul>';
}
