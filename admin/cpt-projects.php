<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_projects() {
  if (!current_user_can('cpt-view-clients')) {
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
      <?php cpt_project_list(); ?>
    </div>
  <?php
}

function cpt_project_list() {
  $project_list = new Client_List_Table();
  $project_list->prepare_items();
  ?>
    <form id="project-list" method="GET">
      <?php $project_list->views(); ?>
      <?php $project_list->display() ?>
    </form>
  <?php
}