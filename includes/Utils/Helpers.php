<?php

namespace NicheClassify\Utils;

// Nc Helpers.Php

/**
 * Sanitize and return a trimmed string value.
 */
function nc_sanitize_text($value) {
    return sanitize_text_field(trim($value));
}

/**
 * Get a post meta value with fallback.
 */
function nc_get_post_meta($post_id, $key, $default = '') {
    $value = get_post_meta($post_id, $key, true);
    return $value !== '' ? $value : $default;
}

/**
 * Check if a post belongs to a given taxonomy term.
 */
function nc_post_has_term($post_id, $term_slug, $taxonomy) {
    $terms = wp_get_post_terms($post_id, $taxonomy, ['fields' => 'slugs']);
    return in_array($term_slug, $terms, true);
}

/**
 * Output a select dropdown from an array.
 */
function nc_render_select($name, $options, $selected = '', $placeholder = '') {
    echo '<select name="' . esc_attr($name) . '">';
    if ($placeholder) {
        echo '<option value="">' . esc_html($placeholder) . '</option>';
    }
    foreach ($options as $value => $label) {
        $is_selected = selected($selected, $value, false);
        echo '<option value="' . esc_attr($value) . '" ' . $is_selected . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}

/**
 * Format a phone number to (XXX) XXX-XXXX format.
 */
function nc_format_phone($phone) {
    $cleaned = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($cleaned) === 10) {
        return sprintf('(%s) %s-%s',
            substr($cleaned, 0, 3),
            substr($cleaned, 3, 3),
            substr($cleaned, 6)
        );
    }
    return $phone;
}

/**
 * Format a date to a readable string.
 */
function nc_format_date($date, $format = 'F j, Y') {
    $timestamp = strtotime($date);
    return $timestamp ? date_i18n($format, $timestamp) : $date;
}

/**
 * Format a price value.
 */
function nc_format_price($amount, $currency = '$') {
    if (!is_numeric($amount)) return $amount;
    return sprintf('%s%s', $currency, number_format($amount, 2));
}

/**
 * Format weight with unit.
 */
function nc_format_weight($weight, $unit = 'kg') {
    if (!is_numeric($weight)) return $weight;
    return sprintf('%s %s', number_format($weight, 1), esc_html($unit));
}

/**
 * Convert weight between kg and lbs.
 */
function nc_convert_weight($value, $from = 'kg', $to = 'lbs') {
    if (!is_numeric($value)) return $value;
    if ($from === 'kg' && $to === 'lbs') {
        return $value * 2.20462;
    } elseif ($from === 'lbs' && $to === 'kg') {
        return $value / 2.20462;
    }
    return $value;
}

/**
 * Get current user locale (e.g., for unit preferences).
 */
function nc_get_user_locale() {
    return get_user_locale() ?: get_locale();
}

/**
 * Convert temperature between Celsius and Fahrenheit.
 */
function nc_convert_temperature($value, $from = 'C', $to = 'F') {
    if (!is_numeric($value)) return $value;
    if ($from === 'C' && $to === 'F') {
        return ($value * 9/5) + 32;
    } elseif ($from === 'F' && $to === 'C') {
        return ($value - 32) * 5/9;
    }
    return $value;
}

/**
 * Format temperature with unit.
 */
function nc_format_temperature($value, $unit = 'C') {
    if (!is_numeric($value)) return $value;
    return sprintf('%s°%s', number_format($value, 1), strtoupper($unit));
}

/**
 * Convert distance between kilometers and miles.
 */
function nc_convert_distance($value, $from = 'km', $to = 'mi') {
    if (!is_numeric($value)) return $value;
    if ($from === 'km' && $to === 'mi') {
        return $value * 0.621371;
    } elseif ($from === 'mi' && $to === 'km') {
        return $value / 0.621371;
    }
    return $value;
}

/**
 * Format distance with unit.
 */
function nc_format_distance($value, $unit = 'km') {
    if (!is_numeric($value)) return $value;
    return sprintf('%s %s', number_format($value, 1), esc_html($unit));
}

/**
 * Convert volume between liters and gallons.
 */
function nc_convert_volume($value, $from = 'l', $to = 'gal') {
    if (!is_numeric($value)) return $value;
    if ($from === 'l' && $to === 'gal') {
        return $value * 0.264172;
    } elseif ($from === 'gal' && $to === 'l') {
        return $value / 0.264172;
    }
    return $value;
}

/**
 * Format volume with unit.
 */
function nc_format_volume($value, $unit = 'l') {
    if (!is_numeric($value)) return $value;
    return sprintf('%s %s', number_format($value, 2), esc_html($unit));
}

/**
 * Format age in months or years.
 */
function nc_format_age($months) {
    if (!is_numeric($months)) return $months;
    if ($months < 12) {
        return sprintf('%d month%s', $months, $months === 1 ? '' : 's');
    }
    $years = floor($months / 12);
    $remaining = $months % 12;
    if ($remaining > 0) {
        return sprintf('%d year%s %d month%s', $years, $years === 1 ? '' : 's', $remaining, $remaining === 1 ? '' : 's');
    }
    return sprintf('%d year%s', $years, $years === 1 ? '' : 's');
}

function nc_include_template_part($slug) {
    $template = locate_template("nicheclassify/parts/{$slug}.php");
    if (!$template) {
        $template = plugin_dir_path(__DIR__) . "templates/parts/{$slug}.php";
    }
    include $template;
}

/**
 * Wrapper function to access the field schema from NC_Field_Manager instance.
 */
function nc_get_field_schema() {
    if (isset(FieldManager::get_instance()) && method_exists(FieldManager::get_instance(), 'get_field_schema')) {
        return FieldManager::get_instance()->get_field_schema();
    }
    return [];
}

/**
 * Render a field based on the registered field schema.
 */
function nc_render_field($key, $value = '') {
    $fields = nc_get_field_schema();
    if (!isset($fields[$key])) {
        return;
    }

    $field = $fields[$key];

    if (!empty($field['render_callback']) && is_callable($field['render_callback'])) {
        call_user_func($field['render_callback'], $key, $field, $value);
    }
}