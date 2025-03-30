<?php

namespace NicheClassify\Fields;

// Class Nc Fields.Php

defined('ABSPATH') || exit;

class Field_Manager {
    protected static $instance = null;
    protected $fields = [];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->fields = $this->get_field_schema();
        add_action('add_meta_boxes', [$this, 'register_meta_box']);
        add_action('save_post_nc_listing', [$this, 'save_fields']);
        add_action('rest_api_init', [$this, 'register_rest_fields']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
        wp_enqueue_media();
        wp_enqueue_script(
            'nc-field-media',
            plugins_url('../assets/js/nc-field-media.js', __FILE__),
            ['jquery'],
            '1.0',
            true
        );
        wp_enqueue_style(
            'nicheclassify-styles',
            plugins_url('../assets/css/nicheclassify.css', __FILE__)
        );
    }

    /**
     * Define the field schema for nc_listing.
     */
    public function get_field_schema() {
        $fields = [
            'custom_price' => [
                'label' => __('Price', 'nicheclassify'),
                'type' => 'number',
                'sanitize_callback' => 'absint',
                'render_callback' => [self::class, 'render_number_field'],
                'show_in_rest' => true,
            ],
            'custom_condition' => [
                'label' => __('Condition', 'nicheclassify'),
                'type' => 'select',
                'options' => [
                    'new' => __('New', 'nicheclassify'),
                    'used' => __('Used', 'nicheclassify'),
                ],
                'sanitize_callback' => 'sanitize_text_field',
                'render_callback' => [self::class, 'render_select_field'],
                'show_in_rest' => true,
            ],
            'is_featured' => [
                'label' => __('Featured Listing', 'nicheclassify'),
                'type' => 'checkbox',
                'sanitize_callback' => function($val) { return $val === 'yes' ? 'yes' : 'no'; },
                'render_callback' => [self::class, 'render_checkbox_field'],
                'show_in_rest' => true,
            ],
            'custom_image' => [
                'label' => __('Primary Image', 'nicheclassify'),
                'type' => 'media',
                'sanitize_callback' => 'absint',
                'render_callback' => [self::class, 'render_media_field'],
                'show_in_rest' => true,
            ],
            'custom_gallery' => [
                'label' => __('Gallery', 'nicheclassify'),
                'type' => 'gallery',
                'sanitize_callback' => 'sanitize_text_field',
                'render_callback' => [self::class, 'render_gallery_field'],
                'show_in_rest' => true,
            ],
            'custom_group' => [
                'label' => __('Group Field', 'nicheclassify'),
                'type' => 'group',
                'fields' => [
                    'subfield1' => [
                        'label' => __('Subfield 1', 'nicheclassify'),
                        'type' => 'text',
                    ],
                    'subfield2' => [
                        'label' => __('Subfield 2', 'nicheclassify'),
                        'type' => 'number',
                    ],
                ],
                'sanitize_callback' => 'sanitize_text_field',
                'render_callback' => [self::class, 'render_group_field'],
                'show_in_rest' => true,
            ],
        ];

        // Normalize and enrich each field
        foreach ($fields as $key => &$field) {
            $field = wp_parse_args($field, [
                'label'             => ucfirst(str_replace('_', ' ', $key)),
                'type'              => 'text',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => null,
                'render_callback'   => [self::class, 'render_text_field'],
                'show_in_rest'      => true,
                'default'           => '',
                'description'       => '',
                'options'           => [],
            ]);
        }

        $fields['location_address'] = [
            'label' => __('Location', 'nicheclassify'),
            'type' => 'text',
            'sanitize_callback' => 'sanitize_text_field',
            'render_callback' => [self::class, 'render_location_field'],
            'show_in_rest' => true,
        ];

        $fields['location_lat'] = [
            'label' => __('Latitude', 'nicheclassify'),
            'type' => 'text',
            'sanitize_callback' => 'sanitize_text_field',
            'render_callback' => [self::class, 'render_text_field'],
            'show_in_rest' => true,
        ];

        $fields['location_lng'] = [
            'label' => __('Longitude', 'nicheclassify'),
            'type' => 'text',
            'sanitize_callback' => 'sanitize_text_field',
            'render_callback' => [self::class, 'render_text_field'],
            'show_in_rest' => true,
        ];

        return apply_filters('nc_listing_field_schema', $fields);
    }

    public static function render_location_field($key, $field, $value = '') {
        $lat = isset($_POST['location_lat']) ? esc_attr($_POST['location_lat']) : '';
        $lng = isset($_POST['location_lng']) ? esc_attr($_POST['location_lng']) : '';
        $address = isset($_POST['location_address']) ? esc_attr($_POST['location_address']) : $value;

        echo '<div class="nc-field-row nc-field-type-location">';
        echo '<label>' . esc_html($field['label']) . '<br>';
        echo '<input type="text" name="location_address" value="' . esc_attr($address) . '" placeholder="' . esc_attr__('Enter address or click map', 'nicheclassify') . '" />';
        echo '</label>';
        echo '<input type="hidden" name="location_lat" value="' . $lat . '" />';
        echo '<input type="hidden" name="location_lng" value="' . $lng . '" />';
        echo '<div id="nc-location-map" style="height: 300px; margin-top: 1rem;"></div>';
        echo '</div>';
    }

    /**
     * Register custom fields for REST API.
     */
    public function register_rest_fields() {
        foreach ($this->fields as $key => $field) {
            if (!empty($field['show_in_rest'])) {
                register_rest_field('nc_listing', $key, [
                    'get_callback' => function($object) use ($key) {
                        return get_post_meta($object['id'], $key, true);
                    },
                    'update_callback' => function($value, $object, $field_name) {
                        update_post_meta($object->ID, $field_name, $value);
                    },
                    'schema' => [
                        'type' => $field['type'],
                        'description' => $field['label'],
                    ],
                ]);
            }
        }
    }

    /**
     * Render field (e.g., in frontend forms).
     */
    public function render_field($key, $value = '') {
        if (!isset($this->fields[$key])) return;

        $field = $this->fields[$key];
        $callback = $field['render_callback'] ?? null;

        if (is_callable($callback)) {
            call_user_func($callback, $key, $field, $value);
        } else {
            // fallback for media/gallery
            if ($field['type'] === 'media') {
                self::render_media_field($key, $field, $value);
            } elseif ($field['type'] === 'gallery') {
                self::render_gallery_field($key, $field, $value);
            } elseif ($field['type'] === 'group') {
                self::render_group_field($key, $field, $value);
            }
        }
    }

    // Example field renderers
    public static function render_text_field($key, $field, $value = '') {
        printf(
            '<div class="nc-field-row nc-field-type-text"><label>%s <input type="text" name="%s" value="%s" /></label></div>',
            esc_html($field['label']),
            esc_attr($key),
            esc_attr($value)
        );
    }

    public static function render_number_field($key, $field, $value = '') {
        printf(
            '<div class="nc-field-row nc-field-type-number"><label>%s <input type="number" name="%s" value="%s" /></label></div>',
            esc_html($field['label']),
            esc_attr($key),
            esc_attr($value)
        );
    }

    public static function render_checkbox_field($key, $field, $value = '') {
        printf(
            '<div class="nc-field-row nc-field-type-checkbox"><label><input type="checkbox" name="%s" value="yes" %s /> %s</label></div>',
            esc_attr($key),
            checked($value, 'yes', false),
            esc_html($field['label'])
        );
    }

    // Media field renderer
    public static function render_media_field($key, $field, $value = '') {
        echo '<div class="nc-field-row nc-field-type-media">';
        echo '<label>' . esc_html($field['label']) . '<br>';
        echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="nc-media-field" />';
        echo '<button type="button" class="button nc-select-media" data-target="' . esc_attr($key) . '">' . esc_html__('Select Image', 'nicheclassify') . '</button>';
        if ($value) {
            echo '<div class="nc-media-preview"><img src="' . esc_url(wp_get_attachment_image_url($value, 'thumbnail')) . '" alt="" /></div>';
        }
        echo '</label><br>';
        echo '</div>';
    }

    // Gallery field renderer
    public static function render_gallery_field($key, $field, $value = '') {
        $values = is_array($value) ? $value : explode(',', $value);
        echo '<div class="nc-field-row nc-field-type-gallery">';
        echo '<label>' . esc_html($field['label']) . '<br>';
        echo '<input type="hidden" name="' . esc_attr($key) . '[]" value="' . esc_attr(implode(',', $values)) . '" class="nc-gallery-field" />';
        echo '<button type="button" class="button nc-select-gallery" data-target="' . esc_attr($key) . '">' . esc_html__('Select Images', 'nicheclassify') . '</button>';
        echo '<div class="nc-gallery-preview">';
        foreach ($values as $id) {
            if ($img = wp_get_attachment_image_url($id, 'thumbnail')) {
                echo '<img src="' . esc_url($img) . '" />';
            }
        }
        echo '</div>';
        echo '</label><br>';
        echo '</div>';
    }

    // Group field renderer
    public static function render_group_field($key, $field, $value = []) {
        $subfields = $field['fields'] ?? [];
        $value = is_array($value) ? $value : [];

        echo '<fieldset class="nc-field-row nc-field-type-group nc-group-field">';
        echo '<legend>' . esc_html($field['label']) . '</legend>';

        $group_index = 0;
        foreach ($value as $entry) {
            echo '<div class="nc-group-entry">';
            foreach ($subfields as $subkey => $subfield) {
                $input_name = "{$key}[{$group_index}][{$subkey}]";
                $field_value = $entry[$subkey] ?? '';
                $subfield['label'] = $subfield['label'] ?? ucfirst($subkey);
                self::render_group_subfield($input_name, $subfield, $field_value);
            }
            echo '<button type="button" class="button nc-remove-group-entry">×</button>';
            echo '</div>';
            $group_index++;
        }

        // Render a blank entry template
        echo '<div class="nc-group-entry nc-group-template" style="display:none;">';
        foreach ($subfields as $subkey => $subfield) {
            $input_name = "{$key}[__index__][{$subkey}]";
            $subfield['label'] = $subfield['label'] ?? ucfirst($subkey);
            self::render_group_subfield($input_name, $subfield, '');
        }
        echo '<button type="button" class="button nc-remove-group-entry">×</button>';
        echo '</div>';

        echo '<button type="button" class="button nc-add-group-entry" data-group="' . esc_attr($key) . '">' . esc_html__('Add Entry', 'nicheclassify') . '</button>';
        echo '</fieldset>';
    }

    // Helper renderer for group subfields
    public static function render_group_subfield($name, $field, $value = '') {
        $type = $field['type'] ?? 'text';

        echo '<div class="nc-field-row nc-subfield-row">';
        switch ($type) {
            case 'text':
                printf('<label>%s <input type="text" name="%s" value="%s" /></label><br>', esc_html($field['label']), esc_attr($name), esc_attr($value));
                break;
            case 'textarea':
                printf('<label>%s<br><textarea name="%s" rows="4">%s</textarea></label><br>', esc_html($field['label']), esc_attr($name), esc_textarea($value));
                break;
            case 'number':
                printf('<label>%s <input type="number" name="%s" value="%s" /></label><br>', esc_html($field['label']), esc_attr($name), esc_attr($value));
                break;
            default:
                printf('<label>%s <input type="text" name="%s" value="%s" /></label><br>', esc_html($field['label']), esc_attr($name), esc_attr($value));
                break;
        }
        echo '</div>';
    }

    // Admin metabox for listing fields
    public function register_meta_box() {
        add_meta_box('nc_listing_fields', __('Listing Details', 'nicheclassify'), [$this, 'render_meta_box'], 'nc_listing');
    }

    public function render_meta_box($post) {
        foreach ($this->fields as $key => $field) {
            $value = get_post_meta($post->ID, $key, true);
            $this->render_field($key, $value);
        }
    }

    public function save_fields($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        foreach ($this->fields as $key => $field) {
            if (isset($_POST[$key])) {
                $value = is_callable($field['sanitize_callback']) ? call_user_func($field['sanitize_callback'], $_POST[$key]) : $_POST[$key];
                update_post_meta($post_id, $key, $value);
            }
        }
    }
}


