<?php

namespace Client_Power_Tools\Core\Common;

/**
* Adds the Client and Client Manager user roles and capabilities, and assigns
* all CPT capabilities to admins.
*/
function cpt_add_roles() {

  add_role(
    'cpt-client',
    'Client'
  );

  add_role(
    'cpt-client-manager',
    'Client Manager',
    [
      'cpt-view-clients'    => true,
      'cpt-manage-clients'  => true,
    ]
  );

  $role = get_role( 'administrator' );

  $role->add_cap( 'cpt-view-clients' );
  $role->add_cap( 'cpt-manage-clients' );
  $role->add_cap( 'cpt-manage-team' );
  $role->add_cap( 'cpt-manage-settings' );

}

add_action( 'init', __NAMESPACE__ . '\cpt_add_roles' );


/**
* Checks to see whether the current user is a client. Returns true if the current
* user has the cpt-client role, false if not.
*
* If no user ID is provided, checks to see whether a user is logged-in with the
* cpt-client role.
*/
function cpt_is_client( $user_id = null ) {

  if ( is_null( $user_id ) && is_user_logged_in() ) {
    $user_id = get_current_user_id();
  }

  $user = get_userdata( $user_id );

  if ( in_array( 'cpt-client', $user->roles ) ) {
    return true;
  } else {
    return false;
  }

}


function cpt_get_client_profile_url( $clients_user_id ) {
  if ( ! $clients_user_id ) { return; }
  return add_query_arg( 'user_id', $clients_user_id, admin_url( 'admin.php?page=cpt' ) );
}


function cpt_get_client_dashboard_url() {
  $page_id = get_option( 'cpt_client_dashboard_page_selection' );
  return get_permalink( $page_id );
}


function cpt_is_client_dashboard() {

  global $wp_query;

  $client_dashboard_id  = get_option( 'cpt_client_dashboard_page_selection' );
  $this_page_id         = isset( $wp_query->post->ID ) ? $wp_query->post->ID : false;

  if ( $this_page_id && $client_dashboard_id == $this_page_id ) {
    return true;
  } else {
    return false;
  }

}


function cpt_get_name( $user_id ) {

  if ( ! $user_id ) { return; }

  $userdata = get_userdata( $user_id );

  if ( isset( $userdata->first_name ) && isset( $userdata->last_name ) ) {
    $name = $userdata->first_name . ' ' . $userdata->last_name;
  } else {
    $name = $userdata->display_name;
  }

  return $name;

}


// Returns an array with the user's details.
function cpt_get_client_data( $clients_user_id ) {

  if ( ! $clients_user_id ) { return; }

  $userdata = get_userdata( $clients_user_id );

  $client_data = [
    'user_id'       => $clients_user_id,
    'first_name'    => get_user_meta( $clients_user_id, 'first_name', true ),
    'last_name'     => get_user_meta( $clients_user_id, 'last_name', true ),
    'email'         => $userdata->user_email,
    'client_id'     => get_user_meta( $clients_user_id, 'cpt_client_id', true ),
    'manager_id'    => cpt_get_client_manager_id( $clients_user_id ),
    'manager_email' => cpt_get_client_manager_email( $clients_user_id ),
    'status'        => get_user_meta( $clients_user_id, 'cpt_client_status', true ),
  ];

  return $client_data;

}


function cpt_get_client_manager_id( $clients_user_id ) {

  if ( ! $clients_user_id ) { return; }

  $userdata = get_userdata( get_user_meta( $clients_user_id, 'cpt_client_manager', true ) );

  if ( $userdata && isset( $userdata->ID ) ) {

    $manager_id = $userdata->ID;

  } else if ( get_option( 'cpt_default_client_manager' ) ) {

    $manager_id = get_option( 'cpt_default_client_manager' );

  } else {

    $userdata   = get_user_by_email( get_bloginfo( 'admin_email' ) );
    $manager_id = $userdata->ID;

  }

  return $manager_id;

}


function cpt_get_client_manager_email( $clients_user_id ) {

  if ( ! $clients_user_id ) { return; }

  $userdata = get_userdata( get_user_meta( $clients_user_id, 'cpt_client_manager', true ) );

  if ( $userdata && isset( $userdata->user_email ) ) {

    $manager_email = $userdata->user_email;

  } else if ( get_option( 'cpt_default_client_manager' ) ) {

    $userdata       = get_userdata( get_option( 'cpt_default_client_manager' ) );
    $manager_email  = $userdata->user_email;

  } else {

    $manager_email = get_bloginfo( 'admin_email' );

  }

  return $manager_email;

}


function cpt_get_email_card( $title = null, $content = null, $button_txt = 'Go', $button_url = null ) {

  $card_style     = 'border: 1px solid #ddd; box-sizing: border-box; font-family: Jost, Helvetica, Arial, sans-serif; margin: 30px 3px 30px 0; padding: 30px; max-width: 500px;';
  $h2_style       = 'margin-top: 0;';
  $button_style   = 'background-color: #eee; border: 1px solid #ddd; box-sizing: border-box; display: block; margin: 0; padding: 1em; width: 100%; text-align: center;';

  ob_start();

    echo '<div class="cpt-card" align="left" style="' . $card_style . '">';

      if ( ! empty( $title ) ) {
        echo '<h2 style="' . $h2_style . '">' . $title . '</h2>';
      }

      if ( ! empty( $content ) ) {
        echo $content;
      }

      if ( ! empty( $button_url ) ) {
        echo '<a class="button" href="' . $button_url . '" style="' . $button_style . '">' . $button_txt . '</a>';
      }

    echo '</div>';

  return ob_get_clean();

}


/**
* Checks for a transient with the results of an action, and if one exists,
* outputs a notice. In the admin, this is a standard WordPress admin notice. On
* the front end, this is a modal.
*/
function cpt_get_notices( $transient_key ) {

  if ( ! $transient_key ) { return; }

  $result = get_transient( $transient_key );

  if ( ! empty( $result ) ) {

    if ( is_admin() ) {

      if ( is_wp_error( $result ) ) {
        $wrapper = '<div class="cpt-notice notice notice-error is-dismissible">';
      } else {
        $wrapper = '<div class="cpt-notice notice notice-success is-dismissible">';
      }

    } else {

      ob_start();

        ?>

          <button class="cpt-notice-dismiss-button">
            <img src="<?php echo CLIENT_POWER_TOOLS_DIR_URL; ?>frontend/images/cpt-dismiss-button.svg" height="25px" width="25px" />
          </button>

        <?php

      $dismiss_button = ob_get_clean();

      $wrapper = '<div class="cpt-inline-modal">' . "\n" . $dismiss_button;

    }



    echo $wrapper;
    echo '<p>' . __( $result ) . '</p>';
    echo '</div>';

  }

  delete_transient( $transient_key );

}

add_action( 'admin_notices', __NAMESPACE__ . '\cpt_get_notices' );


/**
* Redirects the user to the Client Dashboard page with an error query parameter
* if the login form contained a redirect to the Client Dashboard. (In other
* words, if the user started on the frontend login form.)
*
* Works when the username or password fields are empty.
*
* The error message itself is handled in cpt-frontend.php.
*/
function cpt_login_missing( $redirect_to, $requested_redirect_to, $user ) {

  if ( $redirect_to == cpt_get_client_dashboard_url() ) {
    wp_redirect( cpt_get_client_dashboard_url() . "?cpt_error=login_failed" );
    exit;
  }

}

add_filter( 'login_redirect', __NAMESPACE__ . '\cpt_login_missing', 10, 3);

/**
* Same as above, but works when the login is entered but fails.
*/
function cpt_login_failure( $user_login ) {

  if ( $_REQUEST[ 'redirect_to' ] == cpt_get_client_dashboard_url() ) {
    wp_redirect( cpt_get_client_dashboard_url() . "?cpt_error=login_failed" );
    exit;
  }

}

add_action( 'wp_login_failed', __NAMESPACE__ . '\cpt_login_failure' );
