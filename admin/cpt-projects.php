<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_projects() {
  if (!current_user_can('cpt_view_projects')) {
    wp_die(
      '<p>' . __('Sorry, you are not allowed to access this page.') . '</p>',
      403
    );
  }

  $projects_label = Common\cpt_get_projects_label();

  ?>
    <div id="cpt-admin" class="wrap">
      <div id="cpt-admin-header">
        <?php echo file_get_contents(CLIENT_POWER_TOOLS_DIR_PATH . 'assets/images/cpt-logo.svg'); ?>
        <div id="cpt-admin-page-title">
          <h1 id="cpt-page-title"><?php echo $projects_label[1]; ?></h1>
          <p id="cpt-subtitle">Client Power Tools</p>
        </div>
      </div>
      <hr class="wp-header-end">
      <?php
        $clients = get_users([
          'fields' => 'ID',
          'role' => 'cpt-client',
          'orderby' => isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'display_name',
          'order' => isset($_REQUEST['order']) ? sanitize_key($_REQUEST['order']) : 'ASC',
        ]);
        if ($clients && !isset($_REQUEST['project_id'])) {
          if (current_user_can('cpt_manage_projects')) {
            if ($clients) {
              ?>
                <button class="button cpt-click-to-expand"><?php _e('New Project'); ?></button>
                <div class="cpt-this-expands">
                  <?php include(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-new-project-form.php'); ?>
                </div>
              <?php
            } else {
              ?>
                <p>In order to create a project you must add a client.</p>
              <?php
            }
          }
          cpt_project_list();
        } else {
          $project_id = sanitize_key(intval($_REQUEST['project_id']));
          cpt_get_project($project_id);
        }
      ?>
    </div>
  <?php
}

function cpt_get_project($project_id) {
  return '<p>PROJECT PLACEHOLDER</p>';
}

function cpt_project_list() {
  $project_list = new Project_List_Table();
  $project_list->prepare_items();
  ?>
    <form id="project-list" method="GET">
      <?php $project_list->views(); ?>
      <?php $project_list->display() ?>
    </form>
  <?php
}