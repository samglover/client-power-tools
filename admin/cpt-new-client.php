<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_new_client_form() {

  ob_start();

    ?>

      <h3><?php _e( 'Add a Client' ); ?></h3>

      <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">

        <?php wp_nonce_field( 'cpt_new_client_added', 'cpt_new_client_nonce' ); ?>
        <input name="action" value="cpt_new_client_added" type="hidden">

        <table class="form-table" role="presentation">
          <tbody>
            <tr>
              <th scope="row">
                <label for="first_name">First Name<br /><small>(required)</small></label>
              </th>
              <td>
                <input name="first_name" id="first_name" class="regular-text" type="text" required aria-required="true">
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="last_name">Last Name<br /><small>(required)</small></label>
              </th>
              <td>
                <input name="last_name" id="last_name" class="regular-text" type="text" required aria-required="true">
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="email">Email Address<br /><small>(required)</small></label>
              </th>
              <td>
                <input name="email" id="email" class="regular-text" type="text" required aria-required="true" autocapitalize="none" autocorrect="off">
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="client_id">Client ID<br /><small>(optional)</small></label>
              </th>
              <td>
                <input name="client_id" id="client_id" class="regular-text" type="text" autocapitalize="none" autocorrect="off">
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="client_manager">Client Manager</label>
              </th>
              <td>
                <?php echo cpt_get_client_manager_select() ?>
              </td>
            </tr>
            <tr>
              <th scope="row">
                <label for="client_status">Client Status</label>
              </th>
              <td>
                <?php echo cpt_get_client_statuses_select() ?>
              </td>
            </tr>
          </tbody>
        </table>

        <p class="submit">
          <input name="submit" id="submit" class="button button-primary" type="submit" value="<?php _e( 'Add Client' ); ?>">
        </p>

      </form>

    <?php

  echo ob_get_clean();

}


function cpt_process_new_client() {

  if ( isset( $_POST[ 'cpt_new_client_nonce' ] ) && wp_verify_nonce( $_POST[ 'cpt_new_client_nonce' ], 'cpt_new_client_added' ) ) {

    $client_email       = sanitize_email( $_POST[ 'email' ] );
    $existing_client_id = email_exists( $client_email );

    if ( ! $existing_client_id ) {

      /**
      * Note. For some businesses (e.g., law firms), it could be a problem
      * if it were easy to guess a client's user_nicename. For example, WordPress
      * uses user_nicename for author URLs, so someone could find out whether the
      * company has a particular client by checking for valid URLs based on a
      * client's name or username. To avoid this, when creating a new user we
      * generate an md5 hash from the client's name plus a random integer, making
      * the user_nicename pretty much impossible to guess.
      */
      $userdata = [
        'first_name'            => sanitize_text_field( $_POST[ 'first_name' ] ),
        'last_name'             => sanitize_text_field( $_POST[ 'last_name' ] ),
        'display_name'          => sanitize_text_field( $_POST[ 'first_name' ] ) . ' ' . sanitize_text_field( $_POST[ 'last_name' ] ),
        'user_nicename'         => md5( sanitize_text_field( $_POST[ 'first_name' ] ) . sanitize_text_field( $_POST[ 'last_name' ] ) . random_int( 0, PHP_INT_MAX ) ),
        'user_email'            => $client_email,
        'user_login'            => sanitize_user( $_POST[ 'email' ] ),
        'user_pass'             => null,
        'role'                  => 'cpt-client',
        'show_admin_bar_front'  => 'false',
      ];

      $new_client = wp_insert_user( $userdata );

    } else {

      $userdata = [
        'ID'                    => $existing_client_id,
        'first_name'            => sanitize_text_field( $_POST[ 'first_name' ] ),
        'last_name'             => sanitize_text_field( $_POST[ 'last_name' ] ),
      ];

      $new_client = wp_update_user( $userdata );

      $user = new \WP_User( $new_client );
      $user->add_role( 'cpt-client' );

    }

    if ( is_wp_error( $new_client ) ) {

      $result = 'Client could not be created. Error message: ' . $new_client->get_error_message();

    } else {

      update_user_meta( $new_client, 'cpt_client_id', sanitize_text_field( $_POST[ 'client_id' ] ) );
      update_user_meta( $new_client, 'cpt_client_manager', sanitize_text_field( $_POST[ 'client_manager' ] ) );
      update_user_meta( $new_client, 'cpt_client_status', sanitize_text_field( $_POST[ 'client_status' ] ) );

      if ( ! $existing_client_id ) { cpt_new_client_email( $new_client ); }

      $client_profile_url = Common\cpt_get_client_profile_url( $new_client );

      $result = 'Client created. <a href="' . $client_profile_url . '">View ' . Common\cpt_get_name( $new_client ) . '\'s profile</a>.';

    }

    set_transient( 'cpt_new_client_result', $result, 45  );

    wp_redirect( $_POST[ '_wp_http_referer' ] );
    exit;

  } else {

    die();

  }

}

add_action( 'admin_post_cpt_new_client_added', __NAMESPACE__ . '\cpt_process_new_client' );


function cpt_new_client_email( $clients_user_id ) {

  if ( ! $clients_user_id ) { return; }

  $user           = get_userdata( $clients_user_id );
  $client_data    = Common\cpt_get_client_data( $clients_user_id );

  $from_name      = Common\cpt_get_name( $client_data[ 'manager_id' ] );
  $from_email     = $client_data[ 'manager_email' ];

  $headers[]      = 'Content-Type: text/html; charset=UTF-8';
  $headers[]      = $from_name ? 'From: ' . $from_name . ' <' . $from_email . '>' : 'From: ' . $from_email;

  $to             = $user->user_email;
  $subject        = get_option( 'cpt_new_client_email_subject_line' );

  $activation_key = get_password_reset_key( $user );
  $activation_url = Common\cpt_get_client_dashboard_url() . '?cpt_login=setpw&key=' . $activation_key . '&login=' . urlencode( $user->user_login );

  ob_start();

    ?>

      <p>Your username is your email address: <strong><?php echo $user->user_email; ?></strong></p>
      <p>You will need to activate your account and set a password in order to access your client dashboard.</p>

    <?php

  $card_content = ob_get_clean();


  ob_start();

    echo get_option( 'cpt_new_client_email_message_body' );

    echo Common\cpt_get_email_card( $subject, $card_content, 'Activate Your Account', $activation_url );

  $message = ob_get_clean();

  wp_mail( $to, $subject, $message, $headers );

}
