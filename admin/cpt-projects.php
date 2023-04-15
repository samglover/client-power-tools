<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_projects() {
  if (!current_user_can('cpt_view_projects')) wp_die('<p>' . __('Sorry, you are not allowed to access this page.') . '</p>', 403);
  $projects_label = Common\cpt_get_projects_label();
  ?>
    <div id="cpt-admin" class="wrap">
      <?php if (isset($_REQUEST['projects_post_id'])) { ?>
        <p><a href="<?php echo remove_query_arg('projects_post_id'); ?>">&larr; <?php _e('Back to Projects', 'client-power-tools'); ?></a></p>
      <?php } ?>
      <div id="cpt-admin-header">
        <?php echo file_get_contents(CLIENT_POWER_TOOLS_DIR_PATH . 'assets/images/cpt-logo.svg'); ?>
        <div id="cpt-admin-page-title">
          <?php if (!isset($_REQUEST['projects_post_id'])) { ?>
            <h1 id="cpt-page-title"><?php echo $projects_label[1]; ?></h1>
            <p id="cpt-subtitle">Client Power Tools</p>
          <?php } else { ?>
            <?php
              $projects_post_id = sanitize_key(intval($_REQUEST['projects_post_id']));
              $project_data = Common\cpt_get_project_data($projects_post_id);
              $clients_user_id = $project_data['clients_user_id'];
              $client_data = $clients_user_id ? Common\cpt_get_client_data($clients_user_id) : false;
            ?>
            <?php if ($project_data['project_status']) { ?>
              <p id="cpt-project-status"><?php echo $project_data['project_status']; ?></p>
            <?php } ?>
            <h1 id="cpt-page-title">
              <?php
                if ($projects_post_id) {
                  echo $project_data['project_name'];; 
                } else {
                  echo 'Error: No such project.';
                }
              ?>
              <?php if ($project_data['projects_post_id']) { ?>
                <span style="color:silver">(<?php echo $project_data['projects_post_id']; ?>)</span>
              <?php } ?>
            </h1>
            <?php if ($clients_user_id) { ?>
              <p id="cpt-project-client">
                <?php
                  if (get_current_user_id() == $client_data['manager_id']) echo __('Your Client', 'client-power-tools') . ' ';
                  echo Common\cpt_get_name($client_data['user_id']) . '\'s ' . __('Project', 'client-power-tools');
                ?>
              </p>
            <?php } ?>
          <?php } ?>
        </div>
      </div>
      <hr class="wp-header-end">
      <?php
        $clients = Common\cpt_get_clients(['fields' => 'ID']);
        if (!isset($_REQUEST['projects_post_id'])) {
          if (current_user_can('cpt_manage_projects')) {
            if ($clients) {
              ?>
                <button class="button cpt-click-to-expand"><?php echo __('Add a ', 'client-power-tools') . ' ' . Common\cpt_get_projects_label('singular'); ?></button>
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
          $projects_post_id = sanitize_key(intval($_REQUEST['projects_post_id']));
          cpt_get_project($projects_post_id);
        }
      ?>
    </div>
  <?php
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

function cpt_get_project($projects_post_id) {
  if (!$projects_post_id) return;
  $project_data = Common\cpt_get_project_data($projects_post_id);
  $clients_user_id = $project_data['clients_user_id'];
  $client_data = $clients_user_id ? Common\cpt_get_client_data($clients_user_id) : false;
  cpt_edit_project($projects_post_id);
}
