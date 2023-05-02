<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_project_types() {
  if (!current_user_can('cpt_view_projects')) wp_die('<p>' . __('Sorry, you are not allowed to access this page.') . '</p>', 403);
  $projects_label = Common\cpt_get_projects_label();
  ?>
    <div id="cpt-admin" class="wrap">
      <div id="cpt-admin-header">
        <?php echo file_get_contents(CLIENT_POWER_TOOLS_DIR_PATH . 'assets/images/cpt-logo.svg'); ?>
        <div id="cpt-admin-page-title">
          <h1 id="cpt-page-title"><?php printf(__('%s Types', 'client-power-tools'), $projects_label[0]); ?></h1>
          <p id="cpt-subtitle">Client Power Tools</p>
        </div>
      </div>
      <hr class="wp-header-end">
      <div id="col-container">
        <div id="col-left">
          <div class="col-wrap">
            <div class="form-wrap">
              <h2><?php printf(__('Add New %s Type', 'client-power-tools'), $projects_label[0]); ?></h2>
              <?php include(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-new-project-type-form.php'); ?>
            </div>
          </div>
        </div>
        <div id="col-right">
          <div class="col-wrap">
          <?php
            $project_types_list = new Project_Types_List_Table();
            if (isset($_REQUEST['action'])) {
              if (!wp_verify_nonce($_REQUEST['_wpnonce'])) exit(__('Invalid nonce.', 'client-power-tools'));
              cpt_process_project_type_actions($project_types_list->current_action());
            }
            $project_types_list->prepare_items();
            ?>
              <form id="project-list" method="GET">
                <?php $project_types_list->display() ?>
              </form>
            <?php
          ?>
          </div>
        </div>
      </div>
    </div>
  <?php
}


add_action('admin_post_cpt_new_project_type_added', __NAMESPACE__ . '\cpt_process_new_project_type');
function cpt_process_new_project_type() {
  if (!isset($_POST['cpt_new_project_type_nonce']) || !wp_verify_nonce($_POST['cpt_new_project_type_nonce'], 'cpt_new_project_type_added')) exit(__('Invalid nonce.', 'client-power-tools'));

  $new_project_type = wp_insert_term(
    sanitize_text_field($_POST['project_type']),
    'cpt-project-type'
  );

  if (is_wp_error($new_project_type)) {
    $result = sprintf(__('Project type could not be created. Error message: %s', 'client-power-tools'), $new_project_type->get_error_message());
  } else {
    $result = __('Project type created.', 'client-power-tools');
    $new_project_type_stages = add_term_meta(
      $new_project_type['term_id'],
      'cpt_project_type_stages',
      sanitize_textarea_field($_POST['project_type_stages']),
      true
    );
    
    if (is_wp_error($new_project_type_stages)) $result .= sprintf(__('Stages could not be added. Error message: %s', 'client-power-tools'), $new_project_type_stages->get_error_message());
  }

  set_transient('cpt_notice_for_user_' . get_current_user_id(), $result, 15);
  wp_redirect($_POST['_wp_http_referer']);
  exit;
}


function cpt_process_project_type_actions($action) {
  if (!$action) return;
  $redirect_url = remove_query_arg([
    '_wpnonce', 
    'project_type_term_id', 
    'error', 
    'message', 
    'paged'
  ]);

  switch ($action) {
    case 'delete':
      $term_id = intval($_REQUEST['project_type_term_id']);
      $term_deleted = wp_delete_term($term_id, 'cpt-project-type');
      if (is_wp_error($term_deleted)) {
        $result .= sprintf(__('Project type could not be deleted. Error message: %s', 'client-power-tools'), $term_deleted->get_error_message());
      } else {
        $result = __('Project type deleted.', 'client-power-tools');
      }
      set_transient('cpt_notice_for_user_' . get_current_user_id(), $result, 15);
      wp_redirect($redirect_url);
      exit;
      break;
  }
  
  return;
}