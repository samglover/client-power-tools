<?php

namespace Client_Power_Tools\Core\Frontend;
use Client_Power_Tools\Core\Common;

/**
* Noindexes the knowledge base because it's none of Google's business.
*/
function cpt_noindex_knowledge_base() {

  if ( Common\cpt_is_knowledge_base() ) {
    echo '<meta name="robots" content="noindex" />';
  }

}

add_action( 'wp_head',  __NAMESPACE__ . '\cpt_noindex_knowledge_base' );


function cpt_knowledge_base( $content ) {

  if ( Common\cpt_is_knowledge_base() && in_the_loop() ) {

    ob_start();

      if ( is_user_logged_in() ) {

        if ( Common\cpt_is_client() ) {

          cpt_nav( get_the_ID() );

          if ( intval( get_option( 'cpt_knowledge_base_page_selection' ) ) !== get_the_ID() ) {
            cpt_knowledge_base_breadcrumbs();
          }

          cpt_knowledge_base_index();

          return ob_get_clean() . $content;

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

add_filter( 'the_content', __NAMESPACE__ . '\cpt_knowledge_base' );


/**
 * Knowledge Base Breadcrumbs
 */
function cpt_knowledge_base_breadcrumbs() {

  $page_id          = get_the_ID();
  $breadcrumbs[]    = '<span class="breadcrumb last-breadcrumb"><strong>' . get_the_title( $page_id ) . '</strong></span>';
  $parent_id        = wp_get_post_parent_id( $page_id );

  while ( $parent_id ) {

    $parent_url     = get_the_permalink( $parent_id );
    $parent_title   = get_the_title( $parent_id );

    $breadcrumbs[]  = '<span class="breadcrumb"><a href="' . $parent_url . '">' . $parent_title . '</a></span>';
    $parent_id      = wp_get_post_parent_id( $parent_id );

  }

  $breadcrumbs      = array_reverse( $breadcrumbs );

  ob_start();

    ?>

      <div id="cpt-knowledge-base-breadcrumbs">
        <?php echo implode( ' / ', $breadcrumbs ); ?>
      </div>

    <?php

  echo ob_get_clean();

}


/**
 * Knowledge Base Index
 */
function cpt_get_child_pages( $page_id ) {

  if ( ! $page_id ) { return; }

  $args = [
    'fields'          => 'ids',
    'order'           => 'ASC',
    'orderby'         => 'menu_order',
    'post_parent'			=> $page_id,
    'posts_per_page'  => -1,
    'post_status'     => 'publish',
    'post_type'				=> 'page',
  ];

  $child_pages = get_posts( $args );

  if ( $child_pages ) {

    return $child_pages;

  } else {

    return false;

  }

}

function cpt_list_child_pages( $page_id ) {

  if ( ! $page_id ) { return; }

  $current_page_id  = get_the_ID();
  $title            = get_the_title( $page_id );
  $url              = get_the_permalink( $page_id );

  if ( $current_page_id == $page_id ) {
    echo '<li><strong>' . $title . '</strong></li>';
  } else {
    echo '<li><a href="' . $url . '" title="' . $title . '">' . $title . '</a></li>';
  }

  $child_pages = cpt_get_child_pages( $page_id );

  if ( $child_pages ) {

    ob_start();

      echo '<ul>';

        foreach ( $child_pages as $child_page ) {
          cpt_list_child_pages( $child_page );
        }

      echo '</ul>';

    echo ob_get_clean();

  } else {

   return;

  }

}


function cpt_knowledge_base_index() {

  global $post;

  $knowledge_base_id    = get_option( 'cpt_knowledge_base_page_selection' );
  $knowledge_base_url   = Common\cpt_get_knowledge_base_url();
  $knowledge_base_title = get_the_title( $knowledge_base_id );
  $current_page_id      = get_the_ID();
  $child_pages          = cpt_get_child_pages( $knowledge_base_id );

  ob_start();

    ?>

      <div id="cpt-knowledge-base-index" class="cpt-this-expands">
        <ul>

          <?php

            if ( $current_page_id == $knowledge_base_id ) {
              echo '<li><strong>' . $knowledge_base_title . '</strong></li>';
            } else {
              echo '<li><a href="' . $knowledge_base_url . '" title="' . $knowledge_base_title . '">' . $knowledge_base_title . '</a></li>';
            }

          ?>

          <ul>
            <?php foreach( $child_pages as $child_page ) { cpt_list_child_pages( $child_page ); } ?>
          </ul>
        </ul>
      </div>

    <?php

  return ob_get_clean();

}
