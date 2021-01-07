<?php

namespace Client_Power_Tools\Core\Common;

/**
 * Noindexes the client dashboard because it's none of Google's business.
 */
function cpt_noindex_client_dashboard() {

  if ( cpt_is_client_dashboard() ) {
    echo '<meta name="robots" content="noindex" />';
  }

}

add_action( 'wp_head',  __NAMESPACE__ . '\cpt_noindex_client_dashboard' );


function cpt_client_dashboard( $content ) {

  if ( cpt_is_client_dashboard() && in_the_loop() ) {

    ob_start();

      if ( is_user_logged_in() ) {

        if ( cpt_is_client() ) {

          $user_id = get_current_user_id();

          cpt_get_notices( [ 'cpt_new_message_result' ] );

          cpt_nav();

          if ( ! cpt_is_messages() ) {

            $user         = get_userdata( $user_id );
            $client_data  = cpt_get_client_data( $user_id );

            /**
             * translators:
             * 1: html
             * 2: client's name
             * 3: html
             */
            printf( __( '%1$sWelcome back, %2$s!%3$s', 'client-power-tools' ),
              '<p><strong>',
              $client_data[ 'first_name' ],
              '</strong></p>'
            );

            cpt_status_update_request_button( $user_id );

            return ob_get_clean() . $content;

          } elseif ( cpt_is_messages() ) {

            /**
             * Removes the current the_content filter so it doesn't execute
             * within the nested query for client messages.
             */
            remove_filter( current_filter(), __FUNCTION__ );

            cpt_messages( $user_id );

            return ob_get_clean();

          }

        } else {

          /**
           * translators:
           * 1: html
           * 2: html
           */
          printf( __( '%1$sSorry, you don\'t have permission to view this page.%2$s', 'client-power-tools' ),
            '<p>',
            '</p>'
          );

          /**
           * translators:
           * 1: html
           * 2: html
           */
          printf( __( '%1$s(You are logged in, but your user account is missing the "Client" role.)%2$s', 'client-power-tools' ),
            '<p>',
            '</p>'
          );

          return ob_get_clean();

        }

      } else {

        /**
         * translators:
         * 1: html
         * 2: html (<a> tag with link to launch login modal)
         * 3: html (closes <a> tag)
         * 4: html
         */
        printf( __( '%1$sPlease %2$slog in%3$s to view your client dashboard.', 'client-power-tools' ),
          '<p>',
          '<a class="cpt-login-link" href="#">',
          '</a>',
          '</p>'
        );

        return ob_get_clean();

      }

  } else {

    return $content;

  }

}

add_filter( 'the_content', __NAMESPACE__ . '\cpt_client_dashboard' );


function cpt_nav() {

  ob_start();

    ?>

      <div id="cpt-nav">
        <ul>

          <li><a href="<?php echo cpt_get_client_dashboard_url(); ?>" class="cpt-nav-menu-item<?php if ( cpt_is_client_dashboard() && ! cpt_is_messages() ) { echo ' current'; } ?>">Dashboard</a></li>

          <?php if ( get_option( 'cpt_module_messaging' ) ) { ?>
            <li><a href="<?php echo add_query_arg( 'tab', 'messages', cpt_get_client_dashboard_url() ); ?>" class="cpt-nav-menu-item<?php if ( cpt_is_messages() ) { echo ' current'; } ?>">Messages</a></li>
          <?php } ?>

          <?php if ( get_option( 'cpt_module_knowledge_base' ) ) { ?>
            <li><a href="<?php echo cpt_get_knowledge_base_url(); ?>" class="cpt-nav-menu-item<?php if ( cpt_is_knowledge_base() ) { echo ' current'; } ?>">Knowledge Base</a></li>
          <?php } ?>

        </ul>
      </div>

      <p style="line-height: 0 !important;"> </p>

    <?php

  echo ob_get_clean();

}


function cpt_status_update_request_button( $user_id ) {

  if ( ! $user_id ) { return; }

  // Return if the module is disabled.
  if ( ! get_option( 'cpt_module_status_update_req_button' ) ) { return; }

  // Return if the client clicked the button more recently than the request
  // frequency option allows.
  $request_frequency       = get_option( 'cpt_status_update_req_freq' );
  $days_since_last_request = cpt_days_since_last_request( $user_id );

  if ( ! is_null( $days_since_last_request ) && $days_since_last_request < $request_frequency ) { return; }

  ob_start();

    ?>

      <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">

        <?php wp_nonce_field( 'cpt_status_update_requested', 'cpt_status_update_request_nonce' ); ?>
        <input name="action" value="cpt_status_update_requested" type="hidden">
        <input name="clients_user_id" value="<?php echo $user_id; ?>" type="hidden">
        <p class="submit">
          <input name="submit" id="submit" class="button button-primary" type="submit" value="Request Status Update">
        </p>

      </form>

    <?php

  echo ob_get_clean();

}


/**
 * Calculates the number of days since the client last clicked the status update
 * request button. (Since status updates are just cpt_message posts with a custom
 * field, this is based on a custom query.)
 */
function cpt_days_since_last_request( $user_id ) {

  if ( ! $user_id ) { return; }

  $last_request_date = null;

  $args = [
    'meta_query'      => [
      'relation'      => 'AND',
      [
        'key'         => 'cpt_clients_user_id',
        'value'       => $user_id,
      ],
      [
        'key'         => 'cpt_status_update_request',
        'value'       => true,
      ],
    ],
    'order'           => 'DESC',
    'orderby'         => 'post_date',
    'post_type'       => 'cpt_message',
    'posts_per_page'  => 1,
  ];

  $status_update_requests = new \WP_Query( $args );

  if ( $status_update_requests->have_posts() ) : while ( $status_update_requests->have_posts() ) : $status_update_requests->the_post();

    $last_request_date = new \DateTime( get_the_date( 'Y-m-d' ) );

  endwhile; endif;

  $current_date             = new \DateTime( strtotime( date( get_option( 'Y-m-d' ) ) ) );
  $days_since_last_request  = $last_request_date ? $last_request_date->diff( $current_date )->days : null;

  return $days_since_last_request;

}


function cpt_process_status_update_request() {

  if ( isset( $_POST[ 'cpt_status_update_request_nonce' ] ) && wp_verify_nonce( $_POST[ 'cpt_status_update_request_nonce' ], 'cpt_status_update_requested' ) ) {

    $clients_user_id = sanitize_key( intval( $_POST[ 'clients_user_id' ] ) );

    $status_update_request = [
      'post_title'    => 'STATUS UPDATE REQUESTED',
      'post_content'  => 'The client would like a status update.',
      'post_name'     => md5( current_time( 'timestamp' ) . random_int( 0, PHP_INT_MAX ) ),
      'post_status'   => 'publish',
      'post_type'     => 'cpt_message',
      'meta_input'    => [
        'cpt_clients_user_id'         => $clients_user_id,
        'cpt_status_update_request'   => true,
      ],
    ];

    $post = wp_insert_post( $status_update_request, $wp_error );

    if ( is_wp_error( $post ) ) {

      $result = 'Your status update request could not be sent. Error message: ' . $post->get_error_message();

    } else {

      cpt_status_update_request_notification( $post );

      $result = 'Status update requested!';

    }

    set_transient( 'cpt_new_message_result', $result, 45  );

    wp_redirect( $_POST[ '_wp_http_referer' ] );
    exit;

  } else {

    die();

  }

}

add_action( 'admin_post_cpt_status_update_requested', __NAMESPACE__ . '\cpt_process_status_update_request' );


function cpt_status_update_request_notification( $message_id ) {

  if ( ! $message_id ) { return; }

  $msg_obj          = get_post( $message_id );
  $sender_id        = $msg_obj->post_author;
  $clients_user_id  = get_post_meta( $message_id, 'cpt_clients_user_id', true );
  $client_data      = cpt_get_client_data( $clients_user_id );

  $from_name        = get_the_author_meta( 'display_name', $msg_obj->post_author );
  $from_email       = get_the_author_meta( 'user_email', $msg_obj->post_author );

  $headers[]        = 'Content-Type: text/html; charset=UTF-8';
  $headers[]        = 'From: ' . $from_name . ' <' . $from_email . '>';

  $to               = $client_data[ 'manager_email' ];

  if ( get_option( 'cpt_status_update_req_notice_email' ) ) {
    $cc             = get_option( 'cpt_status_update_req_notice_email' );
    $headers[]      = 'Cc: ' . $cc;
  }

  $subject          = $msg_obj->post_title . ' by ' . $from_name;
  $subject_html     = $msg_obj->post_title . '&nbsp;<br />' . 'by ' . $from_name;

  $message          = '<p>Please post an update.</p>';

  $button_txt       = 'Go to ' . $from_name;
  $profile_url      = cpt_get_client_profile_url( $sender_id );

  $message          = cpt_get_email_card( $subject_html, $message, $button_txt, $profile_url );

  wp_mail( $to, $subject, $message, $headers );

}
