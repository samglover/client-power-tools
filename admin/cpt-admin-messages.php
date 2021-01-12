<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Includes;

function cpt_admin_messages() {

  ob_start();

    ?>

      <div id="cpt-admin" class="wrap">

        <div id="cpt-admin-header">
          <img src="<?php echo CLIENT_POWER_TOOLS_DIR_URL; ?>admin/images/cpt-logo.svg" height="auto" width="100%" />
          <div id="cpt-admin-page-title">
            <h1 id="cpt-page-title">Messages</h1>
            <p id="cpt-subtitle">Client Power Tools</p>
          </div>
        </div>
        <hr class="wp-header-end">

        <?php cpt_get_message_list(); ?>

      </div>

    <?php

  echo ob_get_clean();

}

function cpt_get_message_list() {

  ob_start();

    $message_list = new Message_List_Table();
    $message_list->prepare_items();

    ?>

      <form id="cpt-message-list" method="get">
        <?php $message_list->display() ?>
      </form>

    <?php

  echo ob_get_clean();

}
