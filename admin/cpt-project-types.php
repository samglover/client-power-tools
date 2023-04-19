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
          <h1 id="cpt-page-title"><?php echo $projects_label[0] . ' ' . __('Types', 'client-power-tools'); ?></h1>
          <p id="cpt-subtitle">Client Power Tools</p>
        </div>
      </div>
      <hr class="wp-header-end">
  <?php
  $project_types = get_terms([
    'taxonomy'   => 'cpt-project-type',
    'hide_empty' => false,
  ]);
  if ($project_types) {
    ?>
      <ul>
        <?php foreach($project_types as $key => $project_type) { ?>
          <li><?php echo $key . ': ' . $project_type->name; ?> <a>delete</a></li>
        <?php } ?>
      </ul>
      <fieldset>
        <input name="cpt_new_project_type" type="text">
        <label for="cpt_new_project_type">New project type</label>
      </fieldset>
    <?php
  }
}