<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_client_managers() {

  if ( ! current_user_can( 'cpt-manage-settings' ) ) {
    wp_die(
      '<p>' . __( 'Sorry, you are not allowed to access this page.' ) . '</p>',
      403
    );
  }

  Common\cpt_get_notices( 'cpt_add_manager_result' );
  Common\cpt_get_notices( 'cpt_remove_manager_result' );

  ob_start();

    ?>

      <div id="cpt-admin" class="wrap">

        <div id="cpt-admin-header">
          <img src="<?php echo CLIENT_POWER_TOOLS_DIR_URL; ?>admin/images/cpt-logo.svg" height="auto" width="100%" />
          <div id="cpt-admin-page-title">
            <h1 id="cpt-page-title">Client Managers</h1>
            <p id="cpt-subtitle">Client Power Tools</p>
          </div>
        </div>
        <hr class="wp-header-end">

        <button class="button cpt-click-to-expand"><?php _e( 'Add a Client Manager' ); ?></button>

        <div class="cpt-this-expands">
          <?php cpt_add_client_manager_form(); ?>
        </div>

        <?php cpt_client_manager_list(); ?>

      </div>

    <?php

  echo ob_get_clean();

}


function cpt_add_client_manager_form() {

  ob_start();

    ?>

      <h4>Add a Client Manager</h4>

      <p><?php _e( 'Assign the client manager role to a new or existing user. Add the first and last name as you want clients to see them.' ); ?></p>

      <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">

        <?php wp_nonce_field( 'cpt_client_manager_added', 'cpt_client_manager_nonce' ); ?>
        <input name="action" value="cpt_client_manager_added" type="hidden">

        <table class="form-table" role="presentation">
          <tbody>
            <tr>
              <th scope="row">
                <label for="first_name">First Name<br /><small>(required)</small></label>
              </th>
              <td>
                <input name="first_name" id="first_name" class="regular-text" type="text" data-required="true">
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="last_name">Last Name<br /><small>(required)</small></label>
              </th>
              <td>
                <input name="last_name" id="last_name" class="regular-text" type="text" data-required="true">
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="email">Email Address<br /><small>(required)</small></label>
              </th>
              <td>
                <input name="email" id="email" class="regular-text" type="text" data-required="true" autocapitalize="none" autocorrect="off">
              </td>
          </tbody>
        </table>

        <p class="submit">
          <input name="submit" id="submit" class="button button-primary" type="submit" value="Add Client Manager">
        </p>

      </form>

    <?php

  echo ob_get_clean();

}


function cpt_client_manager_list() {

  ob_start();

    $client_manager_list = new Client_Manager_List_Table();
    $client_manager_list->prepare_items();

    ?>

      <form id="client-manager-list" method="get">
        <?php $client_manager_list->views(); ?>
        <?php $client_manager_list->display() ?>
      </form>

    <?php

  echo ob_get_clean();

}
