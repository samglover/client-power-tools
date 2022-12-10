<?php

namespace Client_Power_Tools\Core\Frontend;
use Client_Power_Tools\Core\Common;

// Noindexes the client dashboard because it's none of Google's business.
function cpt_noindex_client_dashboard() {
  if (cpt_is_cpt()) echo '<meta name="robots" content="noindex" /><!-- Added by the Client Power Tools plugin. -->';
}

add_action('wp_head',  __NAMESPACE__ . '\cpt_noindex_client_dashboard');


function cpt_client_dashboard($content) {
  if (
    !cpt_is_cpt() ||
    !in_the_loop()
  ) return $content;
  if (!is_user_logged_in()) {
    return sprintf(__('%1$sPlease %2$slog in%3$s to view the client dashboard.%4$s', 'client-power-tools'),
      /* %1$s */ '<p>',
      /* %2$s */ '<a class="cpt-login-link" href="#">',
      /* %3$s */ '</a>',
      /* %4$s */ '</p>'
    );
  }
  if (!Common\cpt_is_client()) return '<p>' . __('Sorry, you don\'t have permission to view this page because your user account is missing the "Client" role.', 'client-power-tools') . '</p>';

  ob_start();
    cpt_nav();
    Common\cpt_get_notices();
    if (
      Common\cpt_is_knowledge_base() &&
      get_option('cpt_knowledge_base_page_selection') !== get_the_ID() &&
      get_option('cpt_show_knowledge_base_breadcrumbs')
    ) cpt_breadcrumbs();

    $user_id = get_current_user_id();
    update_user_meta($user_id, 'cpt_last_activity', current_time('U', true));

    if (Common\cpt_is_client_dashboard() && !Common\cpt_is_messages()) {
      $client_data = Common\cpt_get_client_data($user_id);
      ?>
        <p><strong><?php printf(__('Welcome back, %s!', 'client-power-tools'), $client_data['first_name']); ?></p></strong>
      <?php
      if (!has_shortcode($content, 'status-update-request-button')) Common\cpt_status_update_request_button($user_id);
      $content = ob_get_clean() . $content;
    } elseif (Common\cpt_is_messages()) {
      // Removes the current the_content filter so it doesn't execute within the
      // nested query for client messages.
      remove_filter(current_filter(), __FUNCTION__);
      Common\cpt_messages($user_id);
      $content = ob_get_clean();
    }
  return $content;
}

add_filter('the_content', __NAMESPACE__ . '\cpt_client_dashboard');


function cpt_nav() {
  $child_pages_array = [];
  ?>
    <nav id="cpt-nav">
      <ul class="cpt-tabs">
        <li class="cpt-tab"><a href="<?php echo Common\cpt_get_client_dashboard_url(); ?>" class="cpt-nav-menu-item<?php if (Common\cpt_is_client_dashboard() && ! Common\cpt_is_messages()) echo ' current'; ?>"><?php _e('Dashboard', 'client-power-tools'); ?></a></li>
        <?php if (get_option('cpt_module_messaging')) { ?>
          <li class="cpt-tab"><a href="<?php echo add_query_arg('tab', 'messages', Common\cpt_get_client_dashboard_url()); ?>" class="cpt-nav-menu-item<?php if (Common\cpt_is_messages()) echo ' current'; ?>"><?php _e('Messages', 'client-power-tools'); ?></a></li>
        <?php } ?>
        <?php
          if (get_option('cpt_module_knowledge_base')) {
            $knowledge_base_id  = get_option('cpt_knowledge_base_page_selection');
            $knowledge_base_url = Common\cpt_get_knowledge_base_url();
            $title              = get_the_title($knowledge_base_id);
            $classes            = 'cpt-nav-menu-item';
            if (Common\cpt_is_knowledge_base()) $classes .= ' current';
            if (cpt_get_child_pages($knowledge_base_id)) $child_pages_array[$knowledge_base_id] = cpt_get_child_pages($knowledge_base_id);
            if ($child_pages_array[$knowledge_base_id]) {
              $classes .= ' cpt-click-to-expand';
              echo '<li class="cpt-tab"><span class="' . $classes . '">' . $title . file_get_contents(CLIENT_POWER_TOOLS_DIR_PATH . 'assets/images/expand.svg') . '</span></li>';
            } else {
              echo '<li class="cpt-tab"><a href="' . $knowledge_base_url . '" class="' . $classes . '" title="' . $title . '">' . $title . '</a></li>';
            }
          }
          $addl_pages_array = get_option('cpt_client_dashboard_addl_pages') ? get_option('cpt_client_dashboard_addl_pages') : false;
          if ($addl_pages_array) $addl_pages_array = explode(',', $addl_pages_array);
          if ($addl_pages_array) {
            foreach($addl_pages_array as $page_id) {
              $page_id = trim($page_id);
              $classes = 'cpt-nav-menu-item';
              if ($page_id == get_the_ID() || in_array($page_id, get_post_ancestors(get_the_ID()))) $classes .= ' current';
              if (get_option('cpt_client_dashboard_addl_pages_children') && cpt_get_child_pages($page_id)) $child_pages_array[$page_id] = cpt_get_child_pages($page_id);
              if (isset($child_pages_array[$page_id])) {
                $classes .= ' cpt-click-to-expand';
                echo '<li class="cpt-tab"><span class="' . $classes . '">' . get_the_title($page_id) . file_get_contents(CLIENT_POWER_TOOLS_DIR_PATH . 'assets/images/expand.svg') . '</span></li>';
              } else {
                echo '<li class="cpt-tab"><a href="' . get_the_permalink($page_id) . '" class="' . $classes . '" title="' . get_the_title($page_id) . '">' . get_the_title($page_id) . '</a></li>';
              }
            }
          }
        ?>
      </ul>
      <?php
        // If adding more drop-down tabs, just keep them in the same order.
        if ($child_pages_array) {
          foreach ($child_pages_array as $parent => $child_pages) {
            echo cpt_nav_tabs_submenu($parent);
          }
        }
      ?>
    </nav>
  <?php
}


/**
 * Nav Submenu
 */
function cpt_get_child_pages($page_id) {
  if (!$page_id) return;

  $kb_child_pages = get_posts([
    'fields'          => 'ids',
    'order'           => 'ASC',
    'orderby'         => 'menu_order',
    'post_parent'			=> $page_id,
    'posts_per_page'  => -1,
    'post_status'     => 'publish',
    'post_type'				=> 'page',
 ]);

  if ($kb_child_pages) {
    return $kb_child_pages;
  } else {
    return false;
  }
}

function cpt_list_child_pages($page_id) {
  if (!$page_id) return;

  $current_page_id  = get_the_ID();
  $title            = get_the_title($page_id);
  $url              = get_the_permalink($page_id);

  if ($current_page_id == $page_id) {
    echo '<li><strong>' . $title . '</strong></li>';
  } else {
    echo '<li><a href="' . $url . '" title="' . $title . '">' . $title . '</a></li>';
  }

  $kb_child_pages = cpt_get_child_pages($page_id);

  if ($kb_child_pages) {
    ?>
      <ul>
        <?php foreach ($kb_child_pages as $child_page) cpt_list_child_pages($child_page); ?>
      </ul>
    <?php
  } else {
   return;
  }
}


function cpt_nav_tabs_submenu($parent_id) {
  if (!$parent_id) return;
  $kb_child_pages = cpt_get_child_pages($parent_id);
  if ($kb_child_pages) {
    ob_start();
      ?>
        <div class="cpt-this-expands cpt-nav-tabs-submenu">
          <ul>
            <?php cpt_list_child_pages($parent_id); ?>
          </ul>
        </div>
      <?php
    return ob_get_clean();
  }
}


/**
 * Breadcrumbs
 */
function cpt_breadcrumbs() {
  $breadcrumbs[]    = '<span class="breadcrumb last-breadcrumb"><strong>' . get_the_title(get_the_ID()) . '</strong></span>';
  $parent_id        = wp_get_post_parent_id(get_the_ID());

  while ($parent_id) {
    $parent_url     = get_the_permalink($parent_id);
    $parent_title   = get_the_title($parent_id);
    $breadcrumbs[]  = '<span class="breadcrumb"><a href="' . $parent_url . '">' . $parent_title . '</a></span>';
    $parent_id      = wp_get_post_parent_id($parent_id);
  }

  $breadcrumbs      = array_reverse($breadcrumbs);

  ?>
    <div id="cpt-breadcrumbs">
      <?php echo implode(' / ', $breadcrumbs); ?>
    </div>
  <?php
}
