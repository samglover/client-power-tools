<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_redirect_clients() {

  /**
  * The $pagenow !== 'admin-post.php' exception allows us to handle form
  * submissions by users (i.e., client messages).
  */

  global $pagenow;

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
        <p><?php _e( '<strong>Warning!</strong> It doesn\'t look like your website is using SSL (HTTPS) for security. Before using Client Power Tools with your clients, you should get an SSL certificate for your website and consider additional security precautions. <a href="https://clientpowertools.com/security/?utm_source=cpt_user&utm_medium=cpt_ssl_warning" target="_blank">Learn more.</a>' ); ?></p>
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
    'Client Power Tools: Client Managers',
    'Managers',
    'cpt-manage-settings',
    'cpt-managers',
    __NAMESPACE__ . '\cpt_client_managers',
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


function cpt_get_client_manager_select( $name = null, $selected = null ) {

  if ( ! $name ) {
    $name = 'client_manager';
  }

  if ( ! $selected ) {
    $admin    = get_user_by_email( get_bloginfo( 'admin_email' ) );
    $selected = get_option( 'cpt_default_client_manager' ) ? get_option( 'cpt_default_client_manager' ) : $admin->ID;
  }

  /**
  * Query Client Managers
  */
  $args = [
    'role__in'  => [ 'administrator', 'cpt-client-manager' ],
    'orderby'   => 'display_name',
    'order'     => 'ASC',
  ];

  $client_manager_query = new \WP_USER_QUERY( $args );
  $client_managers      = $client_manager_query->get_results();

  ob_start();

    echo '<select name="' . $name . '" id="' . $name . '">';

    foreach ( $client_managers as $client_manager ) {
      echo '<option value="' . $client_manager->ID . '"';

      if ( $client_manager->ID == $selected ) {
        echo ' selected';
      }

      echo '>' . $client_manager->display_name . '</option>';
    }

    echo '</select>';

  return ob_get_clean();

}


function cpt_get_client_statuses_select( $name = null, $selected = null ) {

  $statuses_array = explode( "\n", get_option( 'cpt_client_statuses' ) );

  if ( ! $name ) {
    $name = 'client_status';
  }

  if ( ! $selected ) {
    $selected = get_option( 'cpt_default_client_status' );
  }

  ob_start();

    echo '<select name="' . $name . '" id="' . $name . '">';

    foreach ( $statuses_array as $status ) {

      echo '<option value="' . $status . '"';

      if ( trim( $status ) == $selected ) {
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
