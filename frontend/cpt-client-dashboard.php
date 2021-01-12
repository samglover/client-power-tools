<?php

namespace Client_Power_Tools\Core\Frontend;
use Client_Power_Tools\Core\Common;

/**
 * Noindexes the client dashboard because it's none of Google's business.
 */
function cpt_noindex_client_dashboard() {

  if ( Common\cpt_is_client_dashboard() ) {
    echo '<meta name="robots" content="noindex" />';
  }

}

add_action( 'wp_head',  __NAMESPACE__ . '\cpt_noindex_client_dashboard' );


function cpt_client_dashboard( $content ) {

  if ( Common\cpt_is_client_dashboard() && in_the_loop() ) {

    ob_start();

      if ( is_user_logged_in() ) {

        if ( Common\cpt_is_client() ) {

          $user_id = get_current_user_id();

          Common\cpt_get_notices( [ 'cpt_new_message_result' ] );

          cpt_nav();

          if ( ! Common\cpt_is_messages() ) {

            $user         = get_userdata( $user_id );
            $client_data  = Common\cpt_get_client_data( $user_id );

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

            Common\cpt_status_update_request_button( $user_id );

            return ob_get_clean() . $content;

          } elseif ( Common\cpt_is_messages() ) {

            /**
             * Removes the current the_content filter so it doesn't execute
             * within the nested query for client messages.
             */
            remove_filter( current_filter(), __FUNCTION__ );

            Common\cpt_messages( $user_id );

            return ob_get_clean();

          }

        } else {

          echo '<p>' . __( 'Sorry, you don\'t have permission to view this page.', 'client-power-tools' ) . '</p>';
          echo '<p>' . __( '(You are logged in, but your user account is missing the "Client" role.)', 'client-power-tools' ) . '</p>';

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

      <nav id="cpt-nav">
        <ul class="tabs">

          <li><a href="<?php echo Common\cpt_get_client_dashboard_url(); ?>" class="cpt-nav-menu-item<?php if ( Common\cpt_is_client_dashboard() && ! Common\cpt_is_messages() ) { echo ' current'; } ?>"><?php _e( 'Dashboard', 'client-power-tools' ); ?></a></li>

          <?php if ( get_option( 'cpt_module_messaging' ) ) { ?>
            <li><a href="<?php echo add_query_arg( 'tab', 'messages', Common\cpt_get_client_dashboard_url() ); ?>" class="cpt-nav-menu-item<?php if ( Common\cpt_is_messages() ) { echo ' current'; } ?>"><?php _e( 'Messages', 'client-power-tools' ); ?></a></li>
          <?php } ?>

          <?php

            if ( get_option( 'cpt_module_knowledge_base' ) ) {

              $knowledge_base_id  = get_option( 'cpt_knowledge_base_page_selection' );
              $knowledge_base_url = Common\cpt_get_knowledge_base_url();
              $title              = get_the_title( $knowledge_base_id );
              $child_pages        = cpt_get_child_pages( $knowledge_base_id );
              $classes            = 'cpt-nav-menu-item';

              Common\cpt_is_knowledge_base()  ? $classes .= ' current' : null;
              $child_pages                    ? $classes .= ' cpt-click-to-expand' : null;

              echo '<li><a href="' . $knowledge_base_url . '" class="' . $classes . '" title="' . $title . '">' . $title . '</a></li>';
              $knowledge_base_index = cpt_knowledge_base_index();

            }

          ?>

        </ul>

        <?php

          /**
           * If adding more drop-down tabs, just keep them in the same order.
           */
          if ( get_option( 'cpt_module_knowledge_base' ) && $child_pages ) { echo $knowledge_base_index; }

        ?>

      </nav>

    <?php

  echo ob_get_clean();

}
