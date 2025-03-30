<?php

namespace NicheClassify\Forms;

// Class Nc Forms.Php

defined('ABSPATH') || exit;

class FormHandler {
    protected static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_shortcode('nc_submit_form', [$this, 'render_submission_form']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('wp_ajax_nc_submit_form', [$this, 'ajax_handle_submission']);
        add_action('wp_ajax_nopriv_nc_submit_form', [$this, 'ajax_handle_submission']);
    }

    public function enqueue_styles() {
        wp_enqueue_style('nicheclassify-form', plugins_url('../assets/css/nicheclassify-form.css', __FILE__));
        wp_enqueue_script(
            'nicheclassify-form-ajax',
            plugins_url('../assets/js/nicheclassify-form.js', __FILE__),
            ['jquery'],
            '1.0',
            true
        );

        wp_localize_script('nicheclassify-form-ajax', 'ncFormData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('nc_form_nonce'),
        ]);
    }

    public function render_submission_form() {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('You must be logged in to submit a listing.', 'nicheclassify') . '</p>';
        }

        ob_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nc_submit_listing'])) {
            $this->handle_submission();
        }

        $title_error = $content_error = '';
        $title_value = $_POST['post_title'] ?? '';
        $content_value = $_POST['post_content'] ?? '';

        echo '<form method="post" enctype="multipart/form-data" class="nc-submit-form nc-ajax-enabled">';
        echo '<input type="hidden" name="nonce" value="' . esc_attr(wp_create_nonce('nc_form_nonce')) . '">';
        echo '<p data-label="' . esc_attr__('Title', 'nicheclassify') . '"><label>' . esc_html__('Title', 'nicheclassify') . ' ';
        echo '<input type="text" name="post_title" value="' . esc_attr($title_value) . '" required>';
        if (isset($_POST['nc_submit_listing']) && empty($title_value)) {
            echo '<span class="nc-form-error">' . esc_html__('Title is required.', 'nicheclassify') . '</span>';
        }
        echo '</label></p>';
        echo '<p data-label="' . esc_attr__('Description', 'nicheclassify') . '"><label>' . esc_html__('Description', 'nicheclassify') . '<br>';
        echo '<textarea name="post_content" rows="5" required>' . esc_textarea($content_value) . '</textarea>';
        if (isset($_POST['nc_submit_listing']) && empty($content_value)) {
            echo '<span class="nc-form-error">' . esc_html__('Description is required.', 'nicheclassify') . '</span>';
        }
        echo '</label></p>';
        echo '<p data-label="' . esc_attr__('Upload Images', 'nicheclassify') . '"><label>' . esc_html__('Upload Images', 'nicheclassify') . ' <input type="file" name="listing_images[]" multiple></label></p>';

        $fields = nc_get_field_schema();
        $options = get_option('nicheclassify_options', []);
        $enable_contact = !empty($options['enable_contact_form']);

        $errors = [];
        foreach ($fields as $key => $field) {
            $value = $_POST[$key] ?? '';
            $sanitized_value = is_callable($field['sanitize_callback']) ? call_user_func($field['sanitize_callback'], $value) : $value;

            if (isset($_POST['nc_submit_listing']) && $field['type'] !== 'checkbox' && $field['type'] !== 'gallery' && empty($sanitized_value)) {
                $errors[$key] = esc_html__('This field is required.', 'nicheclassify');
            }

            nc_render_field($key, $value);

            if (!empty($errors[$key])) {
                echo '<p class="nc-form-error">' . $errors[$key] . '</p>';
            }
        }

        if ($enable_contact) {
            echo '<input type="hidden" name="contact_form_enabled" value="1">';
        }

        echo '<p><input type="submit" name="nc_submit_listing" value="' . esc_attr__('Submit Listing', 'nicheclassify') . '" class="button button-primary"></p>';
        echo '</form>';

        return ob_get_clean();
    }

    public function ajax_handle_submission() {
        check_ajax_referer('nc_form_nonce', 'nonce');

        ob_start();
        $this->handle_submission();
        wp_send_json_success([
            'html' => ob_get_clean()
        ]);
    }

    public function handle_submission() {
        if (!is_user_logged_in()) return;

        $options = get_option('nicheclassify_options', []);
        $post_status = $options['default_listing_status'] ?? 'pending';

        $title = sanitize_text_field($_POST['post_title'] ?? '');
        $content = wp_kses_post($_POST['post_content'] ?? '');

        $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => $content,
            'post_type'    => 'nc_listing',
            'post_status'  => $post_status,
            'post_author'  => get_current_user_id(),
        ]);

        if (is_wp_error($post_id)) {
            echo '<p class="nc-form-error">' . esc_html__('Failed to create listing.', 'nicheclassify') . '</p>';
            return;
        }

        if (!empty($_FILES['listing_images']['name'][0])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $gallery_ids = [];

            foreach ($_FILES['listing_images']['name'] as $i => $name) {
                if ($_FILES['listing_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_array = [
                        'name'     => $_FILES['listing_images']['name'][$i],
                        'type'     => $_FILES['listing_images']['type'][$i],
                        'tmp_name' => $_FILES['listing_images']['tmp_name'][$i],
                        'error'    => $_FILES['listing_images']['error'][$i],
                        'size'     => $_FILES['listing_images']['size'][$i],
                    ];

                    $attachment_id = media_handle_sideload($file_array, $post_id);
                    if (!is_wp_error($attachment_id)) {
                        $gallery_ids[] = $attachment_id;
                        if (!has_post_thumbnail($post_id)) {
                            set_post_thumbnail($post_id, $attachment_id);
                        }
                    }
                }
            }

            if (!empty($gallery_ids)) {
                update_post_meta($post_id, 'gallery_image_ids', $gallery_ids);
            }
        }

        $fields = nc_get_field_schema();
        foreach ($fields as $key => $field) {
            $raw_value = $_POST[$key] ?? '';
            $sanitized = is_callable($field['sanitize_callback']) ? call_user_func($field['sanitize_callback'], $raw_value) : $raw_value;
            update_post_meta($post_id, $key, $sanitized);
        }

        echo '<p class="nc-form-success">' . esc_html__('Listing submitted successfully and is pending review.', 'nicheclassify') . '</p>';
    }
}