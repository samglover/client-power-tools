<?php

namespace Client_Power_Tools\Core\Common;

/**
* Adds the Client and Client Manager user roles, and the cpt-manage-clients
* capability for Client Managers.
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
      'cpt-manage-team'     => true,
      'cpt-manage-settings' => true,
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


function cpt_get_client_profile_url( $user_id ) {
  return add_query_arg( 'user_id', $user_id, admin_url( 'admin.php?page=cpt' ) );
}


function cpt_get_client_profile_link( $user_id ) {

  if ( ! $user_id ) { return; }

  return '<a href="' . cpt_get_client_profile_url( $user_id ) . '">' . _( 'View profile.' ) . '</a>';

}


function cpt_get_client_dashboard_url() {
  $page_id = get_option( 'cpt_client_dashboard_page_selection' );
  return get_permalink( $page_id );
}


function cpt_is_client_dashboard() {

  global $wp_query;

  $client_dashboard_ID  = get_option( 'cpt_client_dashboard_page_selection' );
  $this_page_ID         = isset( $wp_query->post->ID ) ? $wp_query->post->ID : false;

  if ( $this_page_ID && $client_dashboard_ID == $this_page_ID ) {
    return true;
  } else {
    return false;
  }

}


function cpt_get_client_name( $user_id ) {

  if ( ! $user_id ) { return; }

  $user_meta = get_userdata( $user_id );

  if ( $user_meta->first_name && $user_meta->last_name ) {
    $client_name = $user_meta->first_name . ' ' . $user_meta->last_name;
  } else {
    $client_name = $user_meta->display_name;
  }

  return $client_name;

}


function cpt_get_client_id( $user_id ) {

  if ( ! $user_id ) { return; }

  $client_id = get_user_meta( $user_id, 'cpt_client_id', true );

  return $client_id;

}


// Returns an array with the user's details.
function cpt_get_client_data( $user_id ) {

  if ( ! $user_id ) { return; }

  $userdata = get_userdata( $user_id );

  $client_data = [
    'user_id'     => $user_id,
    'first_name'  => get_user_meta( $user_id, 'first_name', true ),
    'last_name'   => get_user_meta( $user_id, 'last_name', true ),
    'email'       => $userdata->user_email,
    'client_id'   => get_user_meta( $user_id, 'cpt_client_id', true ),
    'status'      => get_user_meta( $user_id, 'cpt_client_status', true ),
  ];

  return $client_data;

}

function get_email_styles() {

  ob_start();

    ?>

      <style>

        @import url( 'https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,400;0,700;1,400;1,700&display=swap' );

        body {
          font-family: 'Jost', sans-serif;
        }

        .cpt-card {
          background-color: #fff;
          box-shadow:
            0 0 0 1px #ddd,
            1px 1px 3px rgba( 0, 0, 0, 0.2 )
          ;
          box-sizing: border-box;
          margin: 30px 3px 30px 0;
          padding: 30px;
          max-width: 500px;
        }

        .cpt-card p {
          margin: 0 0 1.5em 0;
        }

        .cpt-card *:first-child {
          margin-top: 0;
        }

        .cpt-card *:last-child {
          margin-bottom: 0;
        }

        .cpt-card .button {
          background-color: #eee;
          border: 1px solid #ddd;
          box-sizing: border-box;
          display: block;
          padding: 1em;
          width: 100%;
        }

      </style>

    <?php

  return ob_get_clean();

}


/**
* Checks for a transient with the results of an action, and if one exists,
* outputs a notice. In the admin, this is a standard WordPress admin notice. On
* the front end, this is a modal.
*/
function cpt_get_results( $transient_key ) {

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

add_action( 'admin_notices', __NAMESPACE__ . '\cpt_get_results' );
