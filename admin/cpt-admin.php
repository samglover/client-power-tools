<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_redirect_clients() {

  global $pagenow;

  /*
  * The $pagenow !== 'admin-post.php' exception allows us to handle form
  * submissions by users (i.e., client messages).
  */

  if ( Common\cpt_is_client() && ! current_user_can( 'cpt-manage-clients' ) && $pagenow !== 'admin-post.php' ) {
    wp_safe_redirect( home_url() );
    exit;
  }

}

add_action( 'admin_init', __NAMESPACE__ . '\cpt_redirect_clients' );


function cpt_security_warning() {

  if ( ! is_ssl() ) {

    ?>

      <div class="cpt-notice notice notice-warning">
        <p><?php _e( '<strong>Warning!</strong> It doesn\'t look like your website is using SSL (HTTPS) for security. Before using Client Power Tools with your clients, you should get an SSL certificate for your website and consider additional security precautions. <a href="https://clientpowertools.com/security/">Learn more.</a>' ); ?></p>
      </div>

    <?php

  }

}

add_action( 'admin_notices', __NAMESPACE__ . '\cpt_security_warning' );


function cpt_menu_pages() {

  add_menu_page(
    'Client Power Tools',
    'Clients',
    'cpt-view-clients',
    'cpt',
    __NAMESPACE__ . '\cpt_clients',
    CLIENT_POWER_TOOLS_DIR_URL . 'admin/images/cpt-icon.svg',
    '3', // Position
  );

  add_submenu_page(
    'cpt',
    'Client Power Tools: Clients',
    'Clients',
    'cpt-view-clients',
    'cpt',
    __NAMESPACE__ . '\cpt_clients',
  );

  add_submenu_page(
    'cpt',
    'Client Power Tools: Messages',
    'Messages',
    'cpt-manage-clients',
    'cpt-messages',
    __NAMESPACE__ . '\cpt_admin_messages',
  );

  add_submenu_page(
    'cpt',
    'Client Power Tools: Add New Client',
    'Add Client',
    'cpt-manage-clients',
    'cpt-new-client',
    __NAMESPACE__ . '\cpt_new_client',
  );

  add_submenu_page(
    'cpt',
    'Client Power Tools: Settings',
    'Settings',
    'cpt-manage-settings',
    'cpt-settings',
    __NAMESPACE__ . '\cpt_settings',
  );

}

add_action( 'admin_menu', __NAMESPACE__ . '\cpt_menu_pages' );


function cpt_get_admin_notices( $transient_key ) {

  if ( ! $transient_key ) { return; }

  $result = get_transient( $transient_key );

  if ( ! empty( $result ) ) {

    if ( is_wp_error( $result ) ) {
      echo '<div class="cpt-notice notice notice-error is-dismissible">';
    } else {
      echo '<div class="cpt-notice notice notice-success is-dismissible">';
    }

    echo '<p>' . __( $result ) . '</p>';
    echo '</div>';

  }

  delete_transient( $transient_key );

}

add_action( 'admin_notices', __NAMESPACE__ . '\cpt_get_admin_notices' );


function cpt_get_client_statuses_select( $name = 'cpt_client_statuses' ) {

  $statuses_array = explode( "\n", get_option( 'cpt_client_statuses' ) );
  $default_status = get_option( 'cpt_default_client_status' );

  ob_start();

    echo '<select name="' . $name . '" id="' . $name . '">';

    foreach ( $statuses_array as $status ) {

      echo '<option value="' . $status . '"';

      if ( trim( $status ) == $default_status ) {
        echo ' selected';
      }

      echo '>' . $status . '</option>';

    }

    echo '</select>';

  return ob_get_clean();

}


function cpt_show_wp_mail_errors( $wp_error ) {
  echo '<pre>';
  print_r( $wp_error );
  echo '</pre>';
}

add_action( 'wp_mail_failed', 'cpt_show_wp_mail_errors', 10, 1 );
