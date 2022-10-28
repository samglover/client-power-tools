<?php

namespace Client_Power_Tools\Core\Common;

function send_login_code() {
  if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'cpt-login-code-nonce')) exit('Invalid nonce.');
  if (!isset($_POST['email']) || !get_user_by('email', $_POST['email'])) {
    wp_send_json(['success' => false]);
    return;
  }

  $code = wp_generate_password(8, false);
  set_transient('cpt_login_code_' . $_POST['email'], wp_hash_password($code) , 900);

  $to = $_POST['email'];
  $subject = '[' . get_bloginfo('title') . '] Login Code';

  ob_start();
    ?>
      <p>Here is the login code you requested: <strong><?php echo $user->user_email; ?></strong></p>
      <p><strong style="font-size: 125%"><?php echo $code; ?></strong></p>
    <?php
  $card_content = ob_get_clean();
  $dashboard_url = cpt_get_client_dashboard_url();

  ob_start();
    echo cpt_get_email_card('Login Code', $card_content, 'Go to Login', $dashboard_url);
  $message = ob_get_clean();

  $headers[] = 'Content-Type: text/html; charset=UTF-8';

  $result = wp_mail($to, $subject, $message, $headers);
  wp_send_json([
    'email' => $_POST['email'],
    'success' => $result,
  ]);
}

add_action('wp_ajax_send_login_code', __NAMESPACE__ . '\send_login_code'); // Logged-in users.
add_action('wp_ajax_nopriv_send_login_code', __NAMESPACE__ . '\send_login_code'); // Not-logged-in users.


function check_login_code($email, $code) {
  if (!wp_check_password($code, get_transient('cpt_login_code_' . $email))) return;

  delete_transient('cpt_login_code_' . $email);
}

add_action('wp_ajax_check_login_code', __NAMESPACE__ . '\check_login_code'); // Logged-in users.
add_action('wp_ajax_nopriv_check_login_code', __NAMESPACE__ . '\check_login_code'); // Not-logged-in users.
