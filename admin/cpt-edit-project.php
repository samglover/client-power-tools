<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_edit_project($projects_post_id) {
  if (!$projects_post_id || !is_user_logged_in()) return;
  $project_data = Common\cpt_get_project_data($projects_post_id);

  if (is_admin() && current_user_can('cpt_manage_projects')) {
    ?>
      <button class="button cpt-click-to-expand"><?php _e('Edit Project', 'client-power-tools'); ?></button>
      <div class="cpt-this-expands">
        <?php include(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-edit-project-form.php'); ?>
        <?php cpt_delete_project_button($projects_post_id); ?>
      </div>
    <?php
  }
}

function cpt_process_project_update() {
  if (isset($_POST['cpt_project_updated_nonce']) && wp_verify_nonce($_POST['cpt_project_updated_nonce'], 'cpt_project_updated')) {
    $projects_post_id = sanitize_key(intval($_POST['projects_post_id']));
    $project_data = [
      'ID' => $projects_post_id,
      'post_title' => sanitize_text_field($_POST['project_name']),
      'meta_input' => [
        'cpt_project_id' => sanitize_text_field($_POST['project_id']),
        'cpt_project_status' => sanitize_text_field($_POST['project_status']),
        'cpt_client_id' => sanitize_text_field($_POST['client_id']),
      ],
    ];

    $update_project = wp_update_post($project_data);

    $result = 'Project updated.';
    if (is_wp_error($update_project)) $result = 'Project could not be updated. Error message: ' . $update_project->get_error_message();
    set_transient('cpt_notice_for_user_' . get_current_user_id(), $result, 15);

    wp_redirect($_POST['_wp_http_referer']);
    exit;
  } else {
    die();
  }
}

add_action('admin_post_cpt_project_updated', __NAMESPACE__ . '\cpt_process_project_update');


function cpt_delete_project_button($projects_post_id) {
  if (!$projects_post_id) return;
  ?>
    <form id="cpt_delete_project_button" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
      <?php wp_nonce_field('cpt_project_deleted', 'cpt_project_deleted_nonce'); ?>
      <input name="action" value="cpt_project_deleted" type="hidden">
      <input name="projects_post_id" value="<?php echo $projects_post_id ?>" type="hidden">
      <input name="submit" id="submit" type="submit" value="<?php _e('Delete this Project', 'client-power-tools'); ?>">
    </form>
  <?php
}


function cpt_process_delete_project() {
  if (isset($_POST['cpt_project_deleted_nonce']) && wp_verify_nonce($_POST['cpt_project_deleted_nonce'], 'cpt_project_deleted')) {
    $projects_post_id = sanitize_key(intval($_POST['projects_post_id']));
    $project_deleted = wp_delete_post($projects_post_id);

    if ($project_deleted == true) {
      $result = __('Project deleted.', 'client-power-tools');
    } else {
      $result = __('Project could not be deleted.', 'client-power-tools');
    }

    set_transient('cpt_notice_for_user_' . get_current_user_id(), $result, 15);
    wp_redirect(remove_query_arg('projects_post_id', $_POST['_wp_http_referer']));
    exit;
  } else {
    die();
  }
}

add_action('admin_post_cpt_project_deleted', __NAMESPACE__ . '\cpt_process_delete_project');
