<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_clients() {

  if ( ! current_user_can( 'cpt-view-clients' ) ) {
    wp_die(
      '<p>' . __( 'Sorry, you are not allowed to access this page.' ) . '</p>',
      403
    );
  }

  Common\cpt_get_notices( 'cpt_update_client_result' );
  Common\cpt_get_notices( 'cpt_new_message_result' );

  ob_start();

    $page_header = '<div id="cpt-admin-page-title">';

    if ( isset( $_REQUEST[ 'user_id' ] ) ) {

      $user_id        = sanitize_key( intval( $_REQUEST[ 'user_id' ] ) );
      $client_data    = Common\cpt_get_client_data( $user_id );
      $client_id      = $client_data[ 'client_id' ];
      $client_status  = $client_data[ 'status' ];

      if ( $client_status ) {
        $page_header .= '<p id="cpt-client-status" class="dashicons-before status-' . strtolower( $client_status ) . '">' . $client_status . '</p>';
      }

      $page_header .= '<h1 id="cpt-page-title">' . Common\cpt_get_client_name( $user_id );

        if ( $client_id ) {
          $page_header .= ' <span style="color:silver">(' . $client_id . ')</span>';
        }

      $page_header .= '</h1>';

    } else {

      $page_header .= '<h1 id="cpt-page-title">Clients</h1>';

    }

    $page_header .= "\n" . '</div>';

    ?>

      <div id="cpt-admin" class="wrap">

        <?php

          if ( isset( $_REQUEST[ 'user_id' ] ) ) {
            echo '<p><a href="' . remove_query_arg( 'user_id' ) . '">&larr; Back to Clients</a></p>';
          }

        ?>

        <div id="cpt-admin-header">
          <img src="<?php echo CLIENT_POWER_TOOLS_DIR_URL; ?>admin/images/cpt-logo.svg" height="auto" width="100%" />
          <?php echo $page_header; ?>
        </div>
        <hr class="wp-header-end">

        <?php

          if ( isset( $_REQUEST[ 'user_id' ] ) ) {

            $user_id = sanitize_key( intval( $_REQUEST[ 'user_id' ] ) );
            cpt_get_client_profile( $user_id );

          } else {

            cpt_get_client_list();

          }

        ?>

      </div>

    <?php

  echo ob_get_clean();

}


function cpt_get_client_profile( $user_id ) {

  if ( ! $user_id ) { return; }

  cpt_edit_client( $user_id );
  Common\cpt_messages( $user_id );

}


function cpt_get_client_list() {

  ob_start();

    $client_list = new Client_List_Table();
    $client_list->prepare_items();

    ?>

      <form id="client-list" method="get">
        <?php $client_list->views(); ?>
        <?php $client_list->display() ?>
      </form>

    <?php

  echo ob_get_clean();

}
