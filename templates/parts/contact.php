<?php
defined('ABSPATH') || exit;

$post_id = get_the_ID();
$author_id = get_post_field('post_author', $post_id);
$author_email = get_the_author_meta('user_email', $author_id);

if ($author_email) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nc_contact_message'])) {
        $name    = sanitize_text_field($_POST['nc_name']);
        $email   = sanitize_email($_POST['nc_email']);
        $message = sanitize_textarea_field($_POST['nc_message']);

        $headers = ['Reply-To: ' . $email];
        $subject = sprintf(__('New inquiry on listing: %s', 'nicheclassify'), get_the_title($post_id));
        $body    = sprintf("Name: %s\nEmail: %s\n\nMessage:\n%s", $name, $email, $message);

        wp_mail($author_email, $subject, $body, $headers);

        echo '<p class="nc-contact-success">' . esc_html__('Your message has been sent!', 'nicheclassify') . '</p>';
    }

    ?>
    <div class="nc-contact">
        <h3><?php esc_html_e('Contact Listing Owner', 'nicheclassify'); ?></h3>
        <form method="post">
            <p>
                <label><?php esc_html_e('Your Name', 'nicheclassify'); ?><br>
                    <input type="text" name="nc_name" required>
                </label>
            </p>
            <p>
                <label><?php esc_html_e('Your Email', 'nicheclassify'); ?><br>
                    <input type="email" name="nc_email" required>
                </label>
            </p>
            <p>
                <label><?php esc_html_e('Message', 'nicheclassify'); ?><br>
                    <textarea name="nc_message" rows="5" required></textarea>
                </label>
            </p>
            <p>
                <input type="submit" value="<?php esc_attr_e('Send Message', 'nicheclassify'); ?>">
            </p>
        </form>
    </div>
    <?php
}
