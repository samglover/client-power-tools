<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_edit_project($project_id) {
  if (!$project_id || !is_user_logged_in()) return;
  $project_data = Common\cpt_get_project_data($project_id);

  if (is_admin() && current_user_can('cpt_manage_projects')) {
    ?>
      <button class="button cpt-click-to-expand"><?php _e('Edit Project', 'client-power-tools'); ?></button>
      <div class="cpt-this-expands">
        <?php include(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-edit-project-form.php'); ?>
        <p style="margin-bottom: 2em; margin-top: 0;"><span id="cpt-delete-project-link"><?php _e('Delete this Project', 'client-power-tools'); ?></span></p>
      </div>
    <?php
  }
}

function cpt_process_project_update() {
  if (isset($_POST['cpt_project_updated_nonce']) && wp_verify_nonce($_POST['cpt_project_updated_nonce'], 'cpt_project_updated')) {
    $project_id = sanitize_key(intval($_POST['project_id']));

    $userdata = [
      'ID'            => $clients_user_id,
      'first_name'    => sanitize_text_field($_POST['first_name']),
      'last_name'     => sanitize_text_field($_POST['last_name']),
      'display_name'  => sanitize_text_field($_POST['first_name']) . ' ' . sanitize_text_field($_POST['last_name']),
      'user_email'    => sanitize_email($_POST['email']),
    ];

    $clients_user_id = wp_update_user($userdata);

    if (is_wp_error($clients_user_id)) {
      $result = 'Client could not be updated. Error message: ' . $clients_user_id->get_error_message();
    } else {
      $client_id      = sanitize_text_field($_POST['client_id']);
      $client_manager = sanitize_text_field($_POST['client_manager']);
      $client_status  = sanitize_text_field($_POST['client_status']);

      update_user_meta($clients_user_id, 'cpt_client_id', $client_id);
      update_user_meta($clients_user_id, 'cpt_client_manager', $client_manager);
      update_user_meta($clients_user_id, 'cpt_client_status', $client_status);

      $custom_fields = Common\cpt_custom_client_fields();
      if ($custom_fields) {
        foreach ($custom_fields as $field) {
          update_user_meta($clients_user_id, $field['id'], sanitize_text_field($_POST[$field['id']]));
        }
      }

      $result = 'Client updated.';
    }

    set_transient('cpt_notice_for_user_' . get_current_user_id(), $result, 15);
    wp_redirect($_POST['_wp_http_referer']);
    exit;
  } else {
    die();
  }
}

add_action('admin_post_cpt_project_updated', __NAMESPACE__ . '\cpt_process_project_update');


function cpt_delete_project_button($project_id) {
  if (!$project_id) return;
  ?>
    <form id="cpt_delete_project_button" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
      <?php wp_nonce_field('cpt_project_deleted', 'cpt_project_deleted_nonce'); ?>
      <input name="action" value="cpt_project_deleted" type="hidden">
      <input name="project_id" value="<?php echo $project_id ?>" type="hidden">
      <input name="submit" id="submit" class="button button-primary" type="submit" value="<?php _e('Delete this Project', 'client-power-tools'); ?>">
    </form>
  <?php
}


function cpt_process_delete_project() {
  if (isset($_POST['cpt_project_deleted_nonce']) && wp_verify_nonce($_POST['cpt_project_deleted_nonce'], 'cpt_project_deleted')) {
    $project_id = sanitize_key(intval($_POST['project_id']));
    $project_deleted = wp_delete_post($project_id);

    if ($project_deleted == true) {
      $result = __('Project deleted.', 'client-power-tools');
    } else {
      $result = __('Project could not be deleted.', 'client-power-tools');
    }

    set_transient('cpt_notice_for_user_' . get_current_user_id(), $result, 15);
    wp_redirect(remove_query_arg('user_id', $_POST['_wp_http_referer']));
    exit;
  } else {
    die();
  }
}

add_action('admin_post_cpt_project_deleted', __NAMESPACE__ . '\cpt_process_delete_project');
