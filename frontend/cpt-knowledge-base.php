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

          Common\cpt_nav();

        } else {

          echo '<p>Sorry, you don\'t have permission to view this page.</p>';
          echo '<p>(You are logged in, but your user account is missing the "Client" role.)</p>';

        }

      } else {

        echo '<p>Please <a class="cpt-login-link" href="#">log in</a> to view the knowledge base.</p>';

      }

    $knowledge_base = ob_get_clean();

    return $knowledge_base . $content;

  } else {

    return $content;

  }

}

add_filter( 'the_content', __NAMESPACE__ . '\cpt_knowledge_base' );
