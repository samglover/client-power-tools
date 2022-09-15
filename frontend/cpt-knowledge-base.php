<?php

namespace Client_Power_Tools\Core\Frontend;
use Client_Power_Tools\Core\Common;

/**
 * Noindexes the knowledge base because it's none of Google's business.
 */
function cpt_noindex_knowledge_base() {
  if (Common\cpt_is_knowledge_base()) {
    echo '<meta name="robots" content="noindex" />';
  }
}

add_action('wp_head',  __NAMESPACE__ . '\cpt_noindex_knowledge_base');


function cpt_knowledge_base($content) {
  if (Common\cpt_is_knowledge_base() && in_the_loop()) {
    ob_start();
      if (is_user_logged_in()) {
        if (Common\cpt_is_client()) {
          cpt_nav();

          if (get_option('cpt_knowledge_base_page_selection') != get_the_ID()) {
            cpt_breadcrumbs();
          }

          return ob_get_clean() . $content;
        } else {
          echo '<p>' . __('Sorry, you don\'t have permission to view this page.', 'client-power-tools') . '</p>';
          echo '<p>' . __('(You are logged in, but your user account is missing the "Client" role.)', 'client-power-tools') . '</p>';

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
        printf(__('%1$sPlease %2$slog in%3$s to view your client dashboard.', 'client-power-tools'),
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

add_filter('the_content', __NAMESPACE__ . '\cpt_knowledge_base');
