<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_new_client() {

  if ( ! current_user_can( 'cpt-manage-clients' ) ) {
    wp_die(
      '<p>' . __( 'Sorry, you are not allowed to access this page.' ) . '</p>',
      403
    );
  }

  cpt_get_results( 'cpt_new_client_result' );

  ob_start();

    ?>

      <div id="cpt-admin" class="wrap">

        <div id="cpt-admin-header">
          <img src="<?php echo CLIENT_POWER_TOOLS_DIR_URL; ?>admin/images/cpt-logo.svg" height="auto" width="100%" />
          <div id="cpt-admin-page-title">
            <h1 id="cpt-page-title">Add Client</h1>
            <p id="cpt-subtitle">Client Power Tools</p>
          </div>
        </div>
        <hr class="wp-header-end">

        <?php cpt_new_client_form(); ?>

      </div>

    <?php

  echo ob_get_clean();

}


function cpt_new_client_form() {

  ob_start();

    ?>

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
                <label for="client_status">Client Status</label>
              </th>
              <td>
                <?php echo cpt_get_client_statuses_select( 'client_status' ) ?>
              </td>
            </tr>
          </tbody>
        </table>

        <p class="submit">
          <input name="submit" id="submit" class="button button-primary" type="submit" value="Create Client">
        </p>

      </form>

    <?php

  echo ob_get_clean();

}


function cpt_process_new_client() {

  if ( isset( $_POST[ 'cpt_new_client_nonce' ] ) && wp_verify_nonce( $_POST[ 'cpt_new_client_nonce' ], 'cpt_new_client_added' ) ) {

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
      'user_email'            => sanitize_email( $_POST[ 'email' ] ),
      'user_login'            => sanitize_user( $_POST[ 'email' ] ),
      'user_pass'             => null,
      'role'                  => 'cpt-client',
      'show_admin_bar_front'  => 'false',
    ];

    $new_user = wp_insert_user( $userdata );

    if ( is_wp_error( $new_user ) ) {

      $result = 'Client could not be created. Error message: ' . $new_user->get_error_message();

    } else {

      update_user_meta( $new_user, 'cpt_client_id', sanitize_text_field( $_POST[ 'client_id' ] ) );
      update_user_meta( $new_user, 'cpt_client_status', sanitize_text_field( $_POST[ 'client_status' ] ) );

      cpt_new_client_email( $new_user );

      $client_profile_url = Common\cpt_get_client_profile_url( $new_user );

      $result = 'Client created. <a href="' . $client_profile_url . '">View ' . $userdata[ 'display_name' ] . '\'s profile</a>.';

    }

    set_transient( 'cpt_new_client_result', $result, 45  );

    wp_redirect( $_POST[ '_wp_http_referer' ] );
    exit;

  } else {

    die();

  }

}

add_action( 'admin_post_cpt_new_client_added', __NAMESPACE__ . '\cpt_process_new_client' );


function cpt_new_client_email( $user_id ) {

  if ( ! $user_id ) { return; }

  $user           = get_userdata( $user_id );

  $from_name      = get_option( 'cpt_new_client_email_from_name' );
  $from_email     = get_option( 'cpt_new_client_email_from_email' );

  $headers[]      = 'Content-Type: text/html; charset=UTF-8';
  $headers[]      = $from_name ? 'From: ' . $from_name . ' <' . $from_email . '>' : 'From: ' . $from_email;

  $to             = $user->user_email;
  $subject        = get_option( 'cpt_new_client_email_subject_line' );

  $activation_key = get_password_reset_key( $user );
  $activation_url = Common\cpt_get_client_dashboard_url() . '?cpt_login=setpw&key=' . $activation_key . '&login=' . rawurlencode( $user->user_login );

  ob_start();

    echo Common\get_email_styles();

    echo get_option( 'cpt_new_client_email_message_body' );

    ?>

      <div class="cpt-card" align="left">
        <p>Your username is your email address:</p>
        <p><strong><?php echo $user->user_email; ?></strong></p>
        <p>You will need to activate your account and set a password in order to access your client dashboard.</p>
        <p align="center"><a class="button" href="<?php echo $activation_url; ?>">Activate Your Account</a></p>
      </div>

    <?php

  $message = ob_get_clean();

  wp_mail( $to, $subject, $message, $headers );

}
