<?php
defined('ABSPATH') || exit;

$post_id = get_the_ID();
?>

<div class="nc-single-listing">
    <?php nc_include_template_part('featured-badge'); ?>
    <?php nc_include_template_part('title'); ?>
    <?php nc_include_template_part('thumbnail'); ?>
    <?php nc_include_template_part('content'); ?>
    <?php nc_include_template_part('gallery'); ?>
    <?php nc_include_template_part('fields'); ?>
    <?php nc_include_template_part('taxonomies'); ?>
    <?php nc_include_template_part('map'); ?>
    <?php
    $options = get_option('nicheclassify_options', []);
    if (!empty($options['enable_contact_form'])) {
        nc_include_template_part('contact');
    }
    ?>
</div>
