<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_projects() {
  if (!current_user_can('cpt_view_projects')) wp_die('<p>' . __('Sorry, you are not allowed to access this page.') . '</p>', 403);
  $projects_label = Common\cpt_get_projects_label();
  ?>
    <div id="cpt-admin" class="wrap">
      <?php if (isset($_REQUEST['projects_post_id'])) { ?>
        <p><a href="<?php echo remove_query_arg('projects_post_id'); ?>">&larr; <?php printf(__('Back to %s', 'client-power-tools'), $projects_label[1]); ?></a></p>
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
                  printf(__('Error: No such %s.', 'client-power-tools'), strtolower($projects_label[0]));
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
                  echo Common\cpt_get_name($client_data['user_id']) . '\'s ' . $projects_label[0];
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
                <button class="button cpt-click-to-expand"><?php printf(__('Add a %s', 'client-power-tools'), $projects_label[0]); ?></button>
                <div class="cpt-this-expands">
                  <?php include(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-new-project-form.php'); ?>
                </div>
              <?php
            } else {
                echo '<p>' . sprintf(__('In order to create a %s you must add a client.', 'client-power-tools'), strtolower($projects_label[0])) . '</p>';
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

function cpt_get_project_type_select($field_name = 'project_type', $selected = null) {
  if (!$selected) $selected = get_option('cpt_default_project_type');
  $project_types = get_terms([
    'taxonomy' => 'cpt-project-type',
    'hide_empty' => false,
    [
      'orderby' => isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'name',
      'order' => isset($_REQUEST['order']) ? sanitize_key($_REQUEST['order']) : 'ASC',
    ],
  ]);
  if ($project_types) {
    echo '<select name="' . $field_name . '" id="' . $field_name . '">';
      foreach ($project_types as $project_type) {
        echo '<option value="' . $project_type->term_id . '"' . selected($project_type->term_id, $selected) . '>' . $project_type->name . '</option>';
      }
    echo '</select>';
  }
}

function cpt_get_project_stage_select($projects_post_id = null, $field_name = 'cpt_project_stage', $selected = null) {
  if (!$projects_post_id) return;
  $project_type = get_post_meta($projects_post_id, 'cpt_project_type', true);
  $stages_array = explode("\n", get_term_meta($project_type, 'cpt_project_type_stages', true));
  $current_stage = get_post_meta($projects_post_id, 'cpt_project_stage', true);
  echo '<select name="' . $field_name . '" id="' . $field_name . '">';
    foreach ($stages_array as $stage) {
      $stage = trim($stage);
      echo '<option value="' . $stage . '"' . selected($stage, $selected) . '>' . $stage . '</option>';
    }
  echo '</select>';
}