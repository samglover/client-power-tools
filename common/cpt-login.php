<?php

namespace Client_Power_Tools\Core\Common;

function check_password() {
  if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'cpt-login-nonce')) wp_send_json_error(['message' => 'Invalid nonce.']);
  if (!isset($_POST['email']) || strlen($_POST['email']) < 1) wp_send_json_error(['message' => 'Email address is missing.']);
  if (!isset($_POST['password']) || strlen($_POST['password']) < 1) wp_send_json_error(['message' => 'Password is missing.']);

  $user = get_user_by('email', sanitize_email($_POST['email']));
  $password = wp_check_password($_POST['password'], $user->data->user_pass, $user->ID);
  if (!$user || !$password) wp_send_json_error(['message' => 'Login failed.']);

  wp_set_current_user($user->ID);
	wp_set_auth_cookie($user->ID, true);
  wp_send_json_success(['message' => 'Logging you in …']);
}

add_action('wp_ajax_nopriv_check_password', __NAMESPACE__ . '\check_password'); // Not-logged-in users.


function send_login_code() {
  if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'cpt-login-nonce')) wp_send_json_error(['message' => 'Invalid nonce.']);
  if (!isset($_POST['email']) || strlen($_POST['email']) < 1) wp_send_json_error(['message' => 'Email address is missing.']);
  if (!is_email(sanitize_email($_POST['email']))) wp_send_json_error(['message' => 'Please enter a valid email address.']);

  $email = sanitize_email($_POST['email']);
  $user = get_user_by('email', $email);
  if (!$user) wp_send_json_error(['message' => 'Unable to find a user with that email address.']);

  $to = $email;
  $subject = '[' . get_bloginfo('title') . '] Login Code';

  $code = wp_generate_password(8, false);
  set_transient('cpt_login_code_' . $user->ID, wp_hash_password($code) , 600);
  set_transient('cpt_login_code_tries_' . $user->ID, 0, 600);

  $dashboard_url = add_query_arg([
    'cpt_login' => 'code',
    'user' => urlencode($email),
  ], cpt_get_client_dashboard_url());

  ob_start();
    ?>
      <p>Here is the login code you requested:</p>
      <p style="text-align: center;"><strong style="font-size: 125%"><?php echo $code; ?></strong></p>
    <?php
  $card_content = ob_get_clean();

  $message = cpt_get_email_card('', $card_content, __('Go to Login', 'client-power-tools'), $dashboard_url);
  $headers[] = 'Content-Type: text/html; charset=UTF-8';

  $result = wp_mail($to, $subject, $message, $headers);
  wp_send_json_success(['message' => 'Login code sent. It will remain valid for 10 minutes.']);
}

add_action('wp_ajax_nopriv_send_login_code', __NAMESPACE__ . '\send_login_code'); // Not-logged-in users.


function check_login_code() {
  if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'cpt-login-nonce')) wp_send_json_error(['message' => 'Invalid nonce.']);
  if (!isset($_POST['email']) || strlen($_POST['email']) < 1) wp_send_json_error(['message' => 'Email address is missing.']);

  $user = get_user_by('email', sanitize_email($_POST['email']));
  if (!$user) wp_send_json_error(['message' => 'User missing.']);

  $code = isset($_POST['code']) ? wp_check_password(trim($_POST['code']), get_transient('cpt_login_code_' . $user->ID)) : false;
  if (!$code) {
    $tries = get_transient('cpt_login_code_tries_' . $user->ID);
    if ($tries < 3) {
      $tries++;
      set_transient('cpt_login_code_tries_' . $user->ID, $tries);
      wp_send_json_error([
        'message' => $tries . ' of 3 tries. Login failed.',
        'tries' => $tries,
      ]);
    } else {
      delete_transient('cpt_login_code_' . $user->ID);
      delete_transient('cpt_login_code_tries_' . $user->ID);
      wp_send_json_error(['message' => 'Too many tries. Login failed.']);
    }
  }

  delete_transient('cpt_login_code_' . $user->ID);
  wp_set_current_user($user->ID);
	wp_set_auth_cookie($user->ID, true);
  wp_send_json_success(['message' => 'Logging you in …']);
}

add_action('wp_ajax_nopriv_check_login_code', __NAMESPACE__ . '\check_login_code'); // Not-logged-in users.
