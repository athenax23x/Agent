<?php
/**
 * Contact form: shortcode [wrightparel_contact_form].
 * Posts to admin-post.php, emails the site admin, then shows a confirmation.
 */
if (!defined('ABSPATH')) exit;

function wr_contact_form_shortcode() {
    $sent  = isset($_GET['wr_sent']) ? sanitize_key($_GET['wr_sent']) : '';
    $error = isset($_GET['wr_error']) ? sanitize_key($_GET['wr_error']) : '';

    if ($sent === '1') {
        echo '<div class="wr-notice wr-notice--ok"><h3>Thank you, your message has been sent.</h3>'
           . '<p>We reply to every enquiry within two business days.</p></div>';
        return '';
    }

    $messages = [
        'fields'  => 'Please fill in your name, email and message.',
        'email'   => 'That email address does not look right. Please check it.',
        'send'    => 'Sorry, the message could not be sent. Please email us directly instead.',
        'invalid' => 'Please try sending your message again.',
    ];
    $notice = isset($messages[$error]) ? $messages[$error] : '';

    ob_start();
    if ($notice) echo '<p class="wr-notice wr-notice--err">' . esc_html($notice) . '</p>';
    ?>
    <form class="contact" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
      <input type="hidden" name="action" value="wr_contact">
      <?php wp_nonce_field('wr_contact_send', 'wr_contact_nonce'); ?>
      <div class="wr-hp" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden"><input type="text" name="wr_website" tabindex="-1" autocomplete="off"></div>
      <label for="wr-name">Name</label>
      <input id="wr-name" type="text" name="wr_name" required maxlength="120" autocomplete="name">
      <label for="wr-email">Email</label>
      <input id="wr-email" type="email" name="wr_email" required maxlength="160" autocomplete="email">
      <label for="wr-subject">Subject</label>
      <input id="wr-subject" type="text" name="wr_subject" maxlength="140">
      <label for="wr-message">Message</label>
      <textarea id="wr-message" name="wr_message" rows="6" required maxlength="4000"></textarea>
      <button class="wr-btn" type="submit">Send message</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('wrightparel_contact_form', 'wr_contact_form_shortcode');

function wr_contact_handle() {
    $back = wp_get_referer() ? wp_get_referer() : home_url('/');
    $back = strtok($back, '#');
    $back = remove_query_arg(['wr_sent', 'wr_error'], $back);
    $fail = function ($code) use ($back) {
        wp_safe_redirect(add_query_arg('wr_error', $code, $back) . '#wr-contact');
        exit;
    };

    if (!isset($_POST['wr_contact_nonce']) || !wp_verify_nonce($_POST['wr_contact_nonce'], 'wr_contact_send')) {
        $fail('invalid');
    }
    // Honeypot: bots fill it, so accept the form and drop the message.
    if (!empty($_POST['wr_website'])) {
        wp_safe_redirect(add_query_arg('wr_sent', '1', $back) . '#wr-contact');
        exit;
    }

    $name    = isset($_POST['wr_name']) ? sanitize_text_field(wp_unslash($_POST['wr_name'])) : '';
    $email   = isset($_POST['wr_email']) ? sanitize_email(wp_unslash($_POST['wr_email'])) : '';
    $subject = isset($_POST['wr_subject']) ? sanitize_text_field(wp_unslash($_POST['wr_subject'])) : '';
    $message = isset($_POST['wr_message']) ? sanitize_textarea_field(wp_unslash($_POST['wr_message'])) : '';

    if ($name === '' || $message === '') $fail('fields');
    if (!is_email($email)) $fail('email');

    $to      = apply_filters('wr_contact_recipient', get_option('admin_email'));
    $headers = ['Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>'];
    $body    = "New enquiry from the website\n\nName: $name\nEmail: $email\n\n" . $message . "\n";
    $sent    = wp_mail($to, '[' . wp_specialchars_decode(get_bloginfo('name')) . '] ' . ($subject !== '' ? $subject : 'Website enquiry'), $body, $headers);

    if (!$sent) $fail('send');
    wp_safe_redirect(add_query_arg('wr_sent', '1', $back) . '#wr-contact');
    exit;
}
add_action('admin_post_wr_contact', 'wr_contact_handle');
add_action('admin_post_nopriv_wr_contact', 'wr_contact_handle');
