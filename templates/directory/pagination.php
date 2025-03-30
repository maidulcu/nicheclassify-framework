<?php
defined('ABSPATH') || exit;

if (!empty($pagination) && is_array($pagination)) {
    echo '<ul class="nc-pagination">';
    foreach ($pagination as $page_link) {
        echo '<li>' . $page_link . '</li>';
    }
    echo '</ul>';
}
?>
