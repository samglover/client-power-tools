<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_process_new_client() {
  if (!isset($_POST['cpt_new_client_nonce']) || !wp_verify_nonce($_POST['cpt_new_client_nonce'], 'cpt_new_client_added')) exit('Invalid nonce.');

  $first_name = sanitize_text_field($_POST['first_name']);
  $last_name = sanitize_text_field($_POST['last_name']);
  $email = sanitize_email($_POST['email']);
  $existing_client_id = email_exists($email);

  if (!$existing_client_id) {
    $new_client = wp_insert_user([
      'first_name'            => $first_name,
      'last_name'             => $last_name,
      'display_name'          => $first_name . ' ' . $last_name,
      'user_nicename'         => wp_generate_password(12, false),
      'user_email'            => $email,
      'user_login'            => sanitize_user($email),
      'user_pass'             => wp_hash_password(wp_generate_password(32, true)),
      'role'                  => 'cpt-client',
      'show_admin_bar_front'  => 'false',
    ]);
  } else {
    $new_client = wp_update_user([
      'ID'                    => $existing_client_id,
      'first_name'            => $first_name,
      'last_name'             => $last_name,
    ]);
    $user = new \WP_User($new_client);
    $user->add_role('cpt-client');
  }

  if (is_wp_error($new_client)) {
    $result = 'Client could not be created. Error message: ' . $new_client->get_error_message();
  } else {
    update_user_meta($new_client, 'cpt_client_id', sanitize_text_field($_POST['client_id']));
    update_user_meta($new_client, 'cpt_client_manager', sanitize_text_field($_POST['client_manager']));
    update_user_meta($new_client, 'cpt_client_status', sanitize_text_field($_POST['client_status']));

    if (!$existing_client_id) cpt_new_client_email($new_client);
    $client_profile_url = Common\cpt_get_client_profile_url($new_client);
    $result = 'Client created. <a href="' . $client_profile_url . '">View ' . Common\cpt_get_name($new_client) . '\'s profile</a>.';
  }

  set_transient('cpt_new_client_result', $result, 45);
  wp_redirect($_POST['_wp_http_referer']);
  exit;
}

add_action('admin_post_cpt_new_client_added', __NAMESPACE__ . '\cpt_process_new_client');


function cpt_new_client_email($clients_user_id) {
  if (!$clients_user_id) exit('Missing user ID.');

  $user           = get_userdata($clients_user_id);
  $client_data    = Common\cpt_get_client_data($clients_user_id);

  $from_name      = Common\cpt_get_name($client_data['manager_id']);
  $from_email     = $client_data['manager_email'];

  $headers[]      = 'Content-Type: text/html; charset=UTF-8';
  $headers[]      = $from_name ? 'From: ' . $from_name . ' <' . $from_email . '>' : 'From: ' . $from_email;

  $to             = $user->user_email;
  $subject        = get_option('cpt_new_client_email_subject_line');

  $activation_key = get_password_reset_key($user);
  $activation_url = Common\cpt_get_client_dashboard_url() . '?cpt_login=setpw&key=' . $activation_key . '&login=' . urlencode($user->user_login);

  ob_start();
    ?>
      <p>Your username is your email address: <strong><?php echo $user->user_email; ?></strong></p>
      <p>You will need to activate your account and set a password in order to access your client dashboard.</p>
    <?php
  $card_content = ob_get_clean();

  ob_start();
    echo get_option('cpt_new_client_email_message_body');
    echo Common\cpt_get_email_card($subject, $card_content, 'Activate Your Account', $activation_url);
  $message = ob_get_clean();

  wp_mail($to, $subject, $message, $headers);
}
