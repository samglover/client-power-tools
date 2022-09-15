<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Includes;

function cpt_admin_messages() {
  ?>
    <div id="cpt-admin" class="wrap">
      <div id="cpt-admin-header">
        <?php echo file_get_contents(CLIENT_POWER_TOOLS_DIR_PATH . 'assets/images/cpt-logo.svg'); ?>
        <div id="cpt-admin-page-title">
          <h1 id="cpt-page-title"><?php _e('Messages', 'client-power-tools'); ?></h1>
          <p id="cpt-subtitle"><?php _e('Client Power Tools', 'client-power-tools'); ?></p>
        </div>
      </div>
      <hr class="wp-header-end">
      <?php cpt_get_message_list(); ?>
    </div>
  <?php
}

function cpt_get_message_list() {
  $message_list = new Message_List_Table();
  $message_list->prepare_items();

  ?>
    <form id="cpt-message-list" method="get">
      <?php $message_list->display() ?>
    </form>
  <?php
}

function cpt_get_message_pagenum($clients_user_id, $message_id) {
  if (!$clients_user_id || !$message_id) return;

  $cpt_messages = get_posts([
    'fields'      => 'ids',
    'meta_key'    => 'cpt_clients_user_id',
    'meta_value'  => $clients_user_id,
    'numberposts' => -1,
    'post_type'   => 'cpt_message',
  ]);

  if (!$cpt_messages) return;

  $posts_per_page = get_option('posts_per_page');
  $pages          = array_chunk($cpt_messages, $posts_per_page);

  foreach ($pages as $pagenum => $page) {
    if (in_array($message_id, $page)) {
      $page_number = $pagenum + 1;
      continue;
    }
  }

  return $page_number;
}
