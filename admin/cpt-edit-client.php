<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_edit_client( $user_id ) {

  if ( ! $user_id || ! is_user_logged_in() ) { return; }

  $client_data  = Common\cpt_get_client_data( $user_id );
  $client_name  = Common\cpt_get_client_name( $user_id );

  if ( is_admin() && current_user_can( 'cpt-manage-clients' ) ) {

    echo '<button class="button cpt-click-to-expand">' . __( 'Edit Client' ) . '</button>';

    echo '<div class="cpt-this-expands">';
      cpt_edit_client_form( $client_data );
      echo '<p style="margin-bottom: 2em; margin-top: 0;"><span id="cpt-delete-client-link">' . __( 'Delete' ) . ' ' . $client_name . '</span></p>';
      cpt_delete_client_modal( $user_id );
    echo '</div>';

  }

}


function cpt_edit_client_form( $client_data ) {

  if ( ! $client_data ) { return; }

  ob_start();

    ?>

      <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">

        <?php wp_nonce_field( 'cpt_client_updated', 'cpt_client_updated_nonce' ); ?>
        <input name="action" value="cpt_client_updated" type="hidden">
        <input name="clients_user_id" value="<?php echo $client_data[ 'user_id' ]; ?>" type="hidden">

        <table class="form-table" role="presentation">
          <tbody>
            <tr>
              <th scope="row">
                <label for="first_name">First Name<br /><small>(required)</small></label>
              </th>
              <td>
                <input name="first_name" id="first_name" class="regular-text" type="text" required aria-required="true" value="<?php echo $client_data[ 'first_name' ]; ?>">
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="last_name">Last Name<br /><small>(required)</small></label>
              </th>
              <td>
                <input name="last_name" id="last_name" class="regular-text" type="text" required aria-required="true" value="<?php echo $client_data[ 'last_name' ]; ?>">
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="email">Email Address<br /><small>(required)</small></label>
              </th>
              <td>
                <input name="email" id="email" class="regular-text" type="text" required aria-required="true" autocapitalize="none" autocorrect="off" value="<?php echo $client_data[ 'email' ]; ?>">
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="client_id">Client ID<br /><small>(optional)</small></label>
              </th>
              <td>
                <input name="client_id" id="client_id" class="regular-text" type="text" autocapitalize="none" autocorrect="off" value="<?php echo $client_data[ 'client_id' ]; ?>">
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="client_manager">Client Manager</label>
              </th>
              <td>
                <?php echo cpt_get_client_manager_select( '', $client_data[ 'manager_id' ] ); ?>
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="client_status">Client Status</label>
              </th>
              <td>
                <?php echo cpt_get_client_statuses_select( '', $client_data[ 'status' ] ); ?>
              </td>
            </tr>
          </tbody>
        </table>

        <p class="submit">
          <input name="submit" id="submit" class="button button-primary" type="submit" value="Update Client">
        </p>

      </form>

    <?php

  echo ob_get_clean();

}


function cpt_process_client_update() {

  if ( isset( $_POST[ 'cpt_client_updated_nonce' ] ) && wp_verify_nonce( $_POST[ 'cpt_client_updated_nonce' ], 'cpt_client_updated' ) ) {

    $user_id = sanitize_key( intval( $_POST[ 'clients_user_id' ] ) );

    $userdata = [
      'ID'            => $user_id,
      'first_name'    => sanitize_text_field( $_POST[ 'first_name' ] ),
      'last_name'     => sanitize_text_field( $_POST[ 'last_name' ] ),
      'display_name'  => sanitize_text_field( $_POST[ 'first_name' ] ) . ' ' . sanitize_text_field( $_POST[ 'last_name' ] ),
      'user_email'    => sanitize_email( $_POST[ 'email' ] ),
    ];

    $user_id = wp_update_user( $userdata );

    if ( is_wp_error( $user_id ) ) {

      $result = 'Client could not be updated. Error message: ' . $user_id->get_error_message();

    } else {

      $client_id      = sanitize_text_field( $_POST[ 'client_id' ] );
      $client_manager = sanitize_text_field( $_POST[ 'client_manager' ] );
      $client_status  = sanitize_text_field( $_POST[ 'client_status' ] );

      update_user_meta( $user_id, 'cpt_client_id', $client_id );
      update_user_meta( $user_id, 'cpt_client_manager', $client_manager );
      update_user_meta( $user_id, 'cpt_client_status', $client_status );

      $result = 'Client updated.';

    }

    set_transient( 'cpt_update_client_result', $result, 45  );

    wp_redirect( $_POST[ '_wp_http_referer' ] );
    exit;

  } else {

    die();

  }

}

add_action( 'admin_post_cpt_client_updated', __NAMESPACE__ . '\cpt_process_client_update' );


function cpt_delete_client_modal( $user_id ) {

  if ( ! $user_id ) { return; }

  ob_start();

    ?>

      <div id="cpt-delete-client-modal" class="cpt-admin-modal" style="display: none;">
        <div class="cpt-admin-modal-card">

          <h2 style="color: red;"><?php _e( 'WARNING' ); ?></h2>

          <p><?php _e( '<strong>Deleting a client is permanent.</strong> There is no undo. Make sure you have a backup!' ); ?></p>
          <p><?php _e( 'Deleting a client will also remove the associated user account, client messages, and other client information.' ); ?></p>

          <?php cpt_delete_client_button( $user_id ); ?>
          <button class="button cpt-cancel-delete-client"><?php _e( 'Cancel' ); ?></button>

        </div>
      </div>
      <div class="cpt-admin-modal-screen" style="display: none;"></div>

    <?php

  echo ob_get_clean();

}


function cpt_delete_client_button( $user_id ) {

  if ( ! $user_id ) { return; }

  $client_name  = Common\cpt_get_client_name( $user_id );
  $button_txt   = __( 'Delete' ) . ' ' . $client_name;

  ob_start();

    ?>

      <form id="cpt_delete_client_button" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">

        <?php wp_nonce_field( 'cpt_client_deleted', 'cpt_client_deleted_nonce' ); ?>
        <input name="action" value="cpt_client_deleted" type="hidden">
        <input name="clients_user_id" value="<?php echo $user_id ?>" type="hidden">
        <input name="submit" id="submit" class="button button-primary" type="submit" value="<?php echo $button_txt; ?>">

      </form>

    <?php

  echo ob_get_clean();

}


function cpt_process_delete_client() {

  if ( isset( $_POST[ 'cpt_client_deleted_nonce' ] ) && wp_verify_nonce( $_POST[ 'cpt_client_deleted_nonce' ], 'cpt_client_deleted' ) ) {

    $user_id      = sanitize_key( intval( $_POST[ 'clients_user_id' ] ) );
    $client_name  = Common\cpt_get_client_name( $user_id );

    $args = [
      'fields'          => 'ids',
      'meta_key'        => 'cpt_clients_user_id',
      'meta_value'      => $user_id,
      'post_type'       => 'cpt_message',
      'posts_per_page'  => -1,
    ];

    $cpt_messages   = get_posts( $args );
    $message_count  = $cpt_messages ? count( $cpt_messages ) : 0;
    $delete_count   = 0;

    foreach( $cpt_messages as $post_id ) {

      $post_deleted = wp_delete_post( $post_id, true );

      if ( $post_deleted ) { $delete_count++; }

    }

    $client_deleted = wp_delete_user( $user_id );

    if ( $client_deleted == true ) {
      $result = $client_name . __( ' deleted.' );
    } else {
      $result = __( 'Client could not be deleted.' );
    }

    if ( $message_count > 0 ) {

      $result .= ' ' . $delete_count . '/' . $message_count . __( ' messages deleted.' );

      if ( $delete_count < $messager_count ) {
        $result .= __( ' <em>Not all messages could be deleted.</em>' );
      }

    }

    set_transient( 'cpt_delete_client_result', $result, 45  );

    wp_redirect( remove_query_arg( 'user_id', $_POST[ '_wp_http_referer' ] ) );
    exit;

  } else {

    die();

  }

}

add_action( 'admin_post_cpt_client_deleted', __NAMESPACE__ . '\cpt_process_delete_client' );
