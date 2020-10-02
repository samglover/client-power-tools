<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_edit_client( $user_id ) {

  if ( ! $user_id || ! is_user_logged_in() ) { return; }

  $client_data = Common\cpt_get_client_data( $user_id );

  if ( is_admin() && current_user_can( 'cpt-manage-clients' ) ) {

    echo '<button class="button cpt-click-to-expand">Edit Client</button>';

    echo '<div class="cpt-this-expands">';
      cpt_edit_client_form( $client_data );
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
                <label for="client_status">Client Status</label>
              </th>
              <td>
                <select name="client_status" id="client_status">

                  <?php

                    $statuses = $statuses_array = explode( "\n", get_option( 'cpt_client_statuses' ) );

                    foreach ( $statuses as $status ) {

                      if ( $status == $client_data[ 'status' ] ) {
                        echo '<option selected>';
                      } else {
                        echo '<option>';
                      }

                      echo $status . '</option>';

                    }

                  ?>

                </select>
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
      $client_status  = sanitize_text_field( $_POST[ 'client_status' ] );

      update_user_meta( $user_id, 'cpt_client_id', $client_id );
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
