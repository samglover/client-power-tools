<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_clients() {
  if (!current_user_can('cpt-view-clients')) wp_die('<p>' . __('Sorry, you are not allowed to access this page.') . '</p>', 403);

  Common\cpt_get_notices([
    'cpt_new_client_result',
    'cpt_update_client_result',
    'cpt_delete_client_result',
    'cpt_new_message_result'
  ]);

  ?>
    <div id="cpt-admin" class="wrap">
      <?php if (isset($_REQUEST['user_id'])) echo '<p><a href="' . remove_query_arg('user_id') . '">&larr; Back to Clients</a></p>'; ?>
      <div id="cpt-admin-header">
        <?php echo file_get_contents(CLIENT_POWER_TOOLS_DIR_PATH . 'assets/images/cpt-logo.svg'); ?>
        <div id="cpt-admin-page-title">
          <?php if (!isset($_REQUEST['user_id'])) { ?>
            <h1 id="cpt-page-title"><?php _e('Clients', 'client-power-tools'); ?></h1>
            <p id="cpt-subtitle">Client Power Tools</p>
          <?php } else { ?>
            <?php
              $user_id = sanitize_key(intval($_REQUEST['user_id']));
              $client_data = Common\cpt_get_client_data($user_id);
              $client_id = $client_data['client_id'];
            ?>
            <?php if (isset($client_data['status'])) { ?>
              <p id="cpt-client-status"><?php echo $client_data['status']; ?></p>
            <?php } ?>
            <h1 id="cpt-page-title">
              <?php echo Common\cpt_get_name($user_id); ?>
              <?php if ($client_id) { ?>
                <span style="color:silver">(<?php echo $client_id; ?>)</span>
              <?php } ?>
            </h1>
            <?php if (isset($client_data['manager_id'])) { ?>
              <p id="cpt-client-manager">
                <?php
                  if (get_current_user_id() == $client_data['manager_id']) {
                    _e('Your Client', 'client-power-tools');
                  } else {
                    echo Common\cpt_get_name($client_data['manager_id']) . '\'s Client';
                  }
                ?>
              </p>
            <?php } ?>
          <?php } ?>
        </div>
      </div>
      <hr class="wp-header-end">

      <?php
        if (isset($_REQUEST['user_id'])) {
          $user_id = sanitize_key(intval($_REQUEST['user_id']));
          cpt_get_client_profile($user_id);
        } else {
          if (current_user_can('cpt-manage-clients')) {
            ?>
              <button class="button cpt-click-to-expand"><?php _e('Add a Client'); ?></button>
              <div class="cpt-this-expands">
                <?php include(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-new-client-form.php'); ?>
              </div>
            <?php
          }
          cpt_client_list();
        }
      ?>
    </div>
  <?php
}

function cpt_get_client_profile($user_id) {
  if (!$user_id) return;
  cpt_edit_client($user_id);
  if (get_option('cpt_module_messaging')) {
    echo '<h2>' . __('Messages', 'client-power-tools') . '</h2>';
    Common\cpt_messages($user_id);
  }
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
