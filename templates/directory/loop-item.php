<?php
defined('ABSPATH') || exit;

$post_id = get_the_ID();
$is_featured = get_post_meta($post_id, 'is_featured', true) === 'yes';
?>

<div class="nc-listing<?php echo $is_featured ? ' nc-listing-featured' : ''; ?>">
    <?php if ($is_featured): ?>
        <div class="nc-featured-badge"><?php esc_html_e('Featured', 'nicheclassify'); ?></div>
    <?php endif; ?>

    <a href="<?php the_permalink(); ?>" class="nc-listing-link">
        <?php if (has_post_thumbnail()): ?>
            <div class="nc-listing-thumb"><?php the_post_thumbnail('medium', ['loading' => 'lazy', 'decoding' => 'async']); ?></div>
        <?php endif; ?>

        <h3 class="nc-listing-title"><?php the_title(); ?></h3>
        <?php if (!empty($GLOBALS['nc_listing_distance_km'])): ?>
            <div class="nc-listing-distance">
                <?php echo esc_html(sprintf(__('%.1f km away', 'nicheclassify'), $GLOBALS['nc_listing_distance_km'])); ?>
            </div>
        <?php endif; ?>
        <div class="nc-listing-excerpt"><?php the_excerpt(); ?></div>
    </a>
</div>
