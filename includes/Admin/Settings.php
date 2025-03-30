<?php

namespace NicheClassify\Admin;

// Class Nc Settings.Php

defined('ABSPATH') || exit;

class Plugin_Settings {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_settings_page() {
        add_options_page(
            __('NicheClassify Settings', 'nicheclassify'),
            __('NicheClassify', 'nicheclassify'),
            'manage_options',
            'nicheclassify-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('nicheclassify_options', 'nicheclassify_options');

        add_settings_section(
            'nc_general',
            __('General Settings', 'nicheclassify'),
            null,
            'nicheclassify-settings'
        );

        add_settings_field(
            'moderate_submissions',
            __('Moderate Submissions', 'nicheclassify'),
            [$this, 'render_checkbox_field'],
            'nicheclassify-settings',
            'nc_general',
            [
                'label_for' => 'moderate_submissions',
                'option_key' => 'moderate_submissions',
                'description' => __('Require admin approval for new listings.', 'nicheclassify'),
            ]
        );
        
        add_settings_field(
            'default_listing_status',
            __('Default Listing Status', 'nicheclassify'),
            [$this, 'render_select_field'],
            'nicheclassify-settings',
            'nc_general',
            [
                'label_for'   => 'default_listing_status',
                'option_key'  => 'default_listing_status',
                'description' => __('Default post status for new submissions.', 'nicheclassify'),
                'options'     => [
                    'publish' => __('Published', 'nicheclassify'),
                    'pending' => __('Pending Review', 'nicheclassify'),
                    'draft'   => __('Draft', 'nicheclassify'),
                ],
            ]
        );

        add_settings_field(
            'enable_contact_form',
            __('Enable Contact Form', 'nicheclassify'),
            [$this, 'render_checkbox_field'],
            'nicheclassify-settings',
            'nc_general',
            [
                'label_for'   => 'enable_contact_form',
                'option_key'  => 'enable_contact_form',
                'description' => __('Enable the contact form on single listing pages.', 'nicheclassify'),
            ]
        );
    }

    public function render_checkbox_field($args) {
        $options = get_option('nicheclassify_options', []);
        $checked = !empty($options[$args['option_key']]) ? 'checked' : '';
        echo '<label><input type="checkbox" id="' . esc_attr($args['label_for']) . '" name="nicheclassify_options[' . esc_attr($args['option_key']) . ']" value="1" ' . $checked . '> ' . esc_html($args['description']) . '</label>';
    }

    public function render_select_field($args) {
        $options = get_option('nicheclassify_options', []);
        $current = $options[$args['option_key']] ?? '';
        echo '<select id="' . esc_attr($args['label_for']) . '" name="nicheclassify_options[' . esc_attr($args['option_key']) . ']">';
        foreach ($args['options'] as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected($current, $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('NicheClassify Settings', 'nicheclassify'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('nicheclassify_options');
                do_settings_sections('nicheclassify-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

$GLOBALS['nc_plugin_settings'] = new NC_Plugin_Settings();
