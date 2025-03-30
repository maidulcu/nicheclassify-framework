<?php
defined('ABSPATH') || exit;

$post_id = get_the_ID();
$lat = get_post_meta($post_id, 'location_lat', true);
$lng = get_post_meta($post_id, 'location_lng', true);
if ($lat && $lng) :
?>
    <div id="nc-single-map" style="height: 300px; margin: 1.5rem 0;"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof L === 'undefined') return;
            const map = L.map('nc-single-map').setView([<?php echo esc_js($lat); ?>, <?php echo esc_js($lng); ?>], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
            }).addTo(map);
            L.marker([<?php echo esc_js($lat); ?>, <?php echo esc_js($lng); ?>]).addTo(map);
        });
    </script>
<?php endif; ?>
