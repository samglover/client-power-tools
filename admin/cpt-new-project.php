<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_process_new_project() {
  if (!isset($_POST['cpt_new_project_nonce']) || !wp_verify_nonce($_POST['cpt_new_project_nonce'], 'cpt_new_project_added')) exit('Invalid nonce.');

  $new_project = wp_insert_post([
    'post_title' => sanitize_text_field($_POST['project_name']),
    'post_type' => 'cpt_project',
    'meta_input' => [
      'cpt_project_id' => sanitize_text_field($_POST['project_id']),
      'cpt_project_status' => sanitize_text_field($_POST['project_status']),
      'cpt_client_id' => sanitize_text_field($_POST['client_id']),
    ]
  ]);

  if (is_wp_error($new_project)) {
    $result = 'Project could not be created. Error message: ' . $new_client->get_error_message();
  } else {
    $result = 'Project created. <a href="' . get_permalink($new_project) . '">' . __('View project', 'client-power-tools') . '</a>.';
  }

  set_transient('cpt_notice_for_user_' . get_current_user_id(), $result, 15);
  wp_redirect($_POST['_wp_http_referer']);
  exit;
}

add_action('admin_post_cpt_new_project_added', __NAMESPACE__ . '\cpt_process_new_project');