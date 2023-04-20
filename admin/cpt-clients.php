<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_clients() {
  if (!current_user_can('cpt_view_clients')) wp_die('<p>' . __('Sorry, you are not allowed to access this page.') . '</p>', 403);
  ?>
    <div id="cpt-admin" class="wrap">
      <?php if (isset($_REQUEST['user_id'])) { ?>
        <p><a href="<?php echo remove_query_arg('user_id'); ?>">&larr; <?php _e('Back to Clients', 'client-power-tools'); ?></a></p>
      <?php } ?>
      <header id="cpt-admin-header">
        <?php echo file_get_contents(CLIENT_POWER_TOOLS_DIR_PATH . 'assets/images/cpt-logo.svg'); ?>
        <div id="cpt-admin-page-title">
          <?php if (!isset($_REQUEST['user_id'])) { ?>
            <h1 id="cpt-page-title"><?php _e('Clients', 'client-power-tools'); ?></h1>
            <p id="cpt-subtitle">Client Power Tools</p>
          <?php } else { ?>
            <?php
              $user_id = isset($_REQUEST['user_id']) ? sanitize_key(intval($_REQUEST['user_id'])) : false;
              $clients_user_id = Common\cpt_is_client($user_id) ? $user_id : false;
              $client_data = $clients_user_id ? Common\cpt_get_client_data($clients_user_id) : false;
              $client_id = $clients_user_id ? $client_data['client_id'] : false;
            ?>
            <?php if ($clients_user_id) { ?>
              <p id="cpt-client-status"><?php echo $client_data['status']; ?></p>
            <?php } ?>
            <h1 id="cpt-page-title">
              <?php 
                if ($clients_user_id) {
                  echo Common\cpt_get_name($clients_user_id); 
                } else {
                  echo 'Error: No such client.';
                }
              ?>
              <?php if ($client_id) { ?>
                <span style="color:silver">(<?php echo $client_id; ?>)</span>
              <?php } ?>
            </h1>
            <?php if (isset($client_data['manager_id']) && !empty($client_data['manager_id'])) { ?>
              <p id="cpt-client-manager">
                <?php
                  if (get_current_user_id() == $client_data['manager_id']) {
                    _e('Your Client', 'client-power-tools');
                  } else {
                    echo Common\cpt_get_name($client_data['manager_id']) . '\'s ' . __('Client', 'client-power-tools');
                  }
                ?>
              </p>
            <?php } ?>
          <?php } ?>
        </div>
      </header>
      <hr class="wp-header-end">
      <?php
        if (!isset($_REQUEST['user_id'])) {
          if (current_user_can('cpt_manage_clients')) {
            ?>
              <button class="button cpt-click-to-expand"><?php _e('Add a Client'); ?></button>
              <div class="cpt-this-expands">
                <div class="form-wrap">
                  <h2><?php _e('Add a Client', 'client-power-tools'); ?></h2>
                  <?php include(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-new-client-form.php'); ?>
                </div>
              </div>
            <?php
          }
          cpt_client_list();
        } else {
          if (Common\cpt_is_client($clients_user_id)) {
            $clients_user_id = sanitize_key(intval($_REQUEST['user_id']));
            cpt_get_client_profile($clients_user_id);
          }
        }
      ?>
    </div>
  <?php
}

function cpt_client_list() {
  $client_list = new Client_List_Table();
  $client_list->prepare_items();
  ?>
    <form id="client-list" method="GET">
      <?php $client_list->views(); ?>
      <?php $client_list->display() ?>
    </form>
  <?php
}

function cpt_get_client_profile($clients_user_id) {
  if (!$clients_user_id) return;
  cpt_edit_client($clients_user_id);
  if (get_option('cpt_module_projects')) {
    ?>
      <section id="cpt-projects">
        <h2 class="cpt-row">
          <?php echo Common\cpt_get_projects_label('plural'); ?>
          <button class="button cpt-click-to-expand"><?php echo __('Add a', 'client-power-tools') . ' ' . Common\cpt_get_projects_label('singular'); ?></button>
        </h2>
        <div class="cpt-this-expands">
          <?php include(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-new-project-form.php'); ?>
        </div>
        <?php Common\cpt_clients_projects($clients_user_id); ?>
      </section>
    <?php
  }
  if (get_option('cpt_module_messaging')) {
    ?>
      <section id="cpt-messages">
        <h2 class="cpt-row">
          <?php _e('Messages', 'client-power-tools'); ?>
          <button class="button cpt-click-to-expand"><?php _e('New Message', 'client-power-tools'); ?></button>
        </h2>
        <div class="cpt-this-expands">
          <?php Common\cpt_new_message_form(get_current_user_id()); ?>
        </div>
        <?php Common\cpt_messages($clients_user_id); ?>
      </section>
    <?php
  }
}
