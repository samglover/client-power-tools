<?php

namespace Client_Power_Tools\Core\Frontend;
use Client_Power_Tools\Core\Common;

// Noindexes the client dashboard because it's none of Google's business.
add_action('wp_head',  __NAMESPACE__ . '\cpt_noindex_client_dashboard');
function cpt_noindex_client_dashboard() {
  if (cpt_is_cpt()) echo '<meta name="robots" content="noindex" />';
}


add_filter('the_content', __NAMESPACE__ . '\cpt_client_dashboard');
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

  $clients_user_id = get_current_user_id();
  if (!Common\cpt_is_client($clients_user_id)) return '<p>' . __('Sorry, you don\'t have permission to view this page because your user account is missing the "Client" role.', 'client-power-tools') . '</p>';

  // Client Dashboard
  ob_start();
    // Nav menu tabs
    cpt_nav();

    // Notices
    Common\cpt_get_notices();

    // Knowledge Base Breadcrumbs
    $kb_id = get_option('cpt_knowledge_base_page_selection');
    $kb_child_pages = cpt_get_child_pages($kb_id);
    if (
      Common\cpt_is_knowledge_base() &&
      get_option('cpt_show_knowledge_base_breadcrumbs') &&
      get_the_ID() != $kb_id && 
      $kb_child_pages
    ) cpt_kb_breadcrumbs();

    // Last Activity Timestamp
    update_user_meta($clients_user_id, 'cpt_last_activity', current_time('U', true));

    // Dashboard
    if (Common\cpt_is_client_dashboard() && !isset($_REQUEST['tab'])) {
      $client_data = Common\cpt_get_client_data($clients_user_id);
      echo '<p><strong>' . sprintf(__('Welcome back, %s!', 'client-power-tools'), $client_data['first_name']) . '</p></strong>';
      if (
        get_option('cpt_module_status_update_req_button') && 
        !has_shortcode($content, 'status-update-request-button')
      ) {
        Common\cpt_status_update_request_button($clients_user_id);
      }
      $content = ob_get_clean() . $content;
    }

    // Projects
    if (Common\cpt_is_client_dashboard('projects')) {
      $projects_label = Common\cpt_get_projects_label();
      if (!isset($_REQUEST['projects_post_id'])) {
        Common\cpt_get_projects();
      } else {
        $projects_post_id = sanitize_key(intval($_REQUEST['projects_post_id']));
        echo '<p><a href="' . remove_query_arg('projects_post_id') . '">' . sprintf(__('%s Back to %s', 'client-power-tools'), '&larr;', $projects_label[1]) . '</a></p>';
        echo '<h2>' . get_the_title($projects_post_id) . '</h2>';
        Common\cpt_get_project_progress_bar($projects_post_id);
        Common\cpt_get_project_meta($projects_post_id);
      }
      $content = ob_get_clean();
    }

    // Messages
    if (Common\cpt_is_client_dashboard('messages')) {
      // Removes the current the_content filter so it doesn't execute within the
      // nested query for client messages.
      remove_filter(current_filter(), __FUNCTION__);
      Common\cpt_messages($clients_user_id);
      $content = ob_get_clean();
    }
  return $content;
}


function cpt_nav() {
  $child_pages_array = [];
  ?>
    <nav id="cpt-nav">
      <ul class="cpt-tabs">
        <li class="cpt-tab"><a href="<?php echo Common\cpt_get_client_dashboard_url(); ?>" class="cpt-nav-menu-item<?php if (Common\cpt_is_client_dashboard() && !isset($_REQUEST['tab'])) echo ' current'; ?>"><?php _e('Dashboard', 'client-power-tools'); ?></a></li>
        <?php if (get_option('cpt_module_projects')) { ?>
          <li class="cpt-tab"><a href="<?php echo add_query_arg('tab', 'projects', Common\cpt_get_client_dashboard_url()); ?>" class="cpt-nav-menu-item<?php if (Common\cpt_is_client_dashboard('projects')) echo ' current'; ?>"><?php echo Common\cpt_get_projects_label('plural'); ?></a></li>
        <?php } ?>
        <?php if (get_option('cpt_module_messaging')) { ?>
          <li class="cpt-tab"><a href="<?php echo add_query_arg('tab', 'messages', Common\cpt_get_client_dashboard_url()); ?>" class="cpt-nav-menu-item<?php if (Common\cpt_is_client_dashboard('messages')) echo ' current'; ?>"><?php _e('Messages', 'client-power-tools'); ?></a></li>
        <?php } ?>
        <?php
          $knowledge_base_id = get_option('cpt_knowledge_base_page_selection');
          $kb_pages_array = cpt_get_child_pages($knowledge_base_id);
          if (get_option('cpt_module_knowledge_base')) {
            $title = get_the_title($knowledge_base_id);
            $classes = 'cpt-nav-menu-item';
            if (Common\cpt_is_knowledge_base()) $classes .= ' current';
            if ($kb_pages_array) $child_pages_array[$knowledge_base_id] = $kb_pages_array;
            if ($kb_pages_array) {
              $classes .= ' cpt-click-to-expand';
              echo '<li class="cpt-tab"><span class="' . $classes . '">' . $title . file_get_contents(CLIENT_POWER_TOOLS_DIR_PATH . 'assets/images/expand.svg') . '</span></li>';
            } else {
              echo '<li class="cpt-tab"><a href="' . Common\cpt_get_knowledge_base_url() . '" class="' . $classes . '" title="' . $title . '">' . $title . '</a></li>';
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

  $child_pages = get_posts([
    'fields'          => 'ids',
    'order'           => 'ASC',
    'orderby'         => 'menu_order',
    'post_parent'			=> $page_id,
    'posts_per_page'  => -1,
    'post_status'     => 'publish',
    'post_type'				=> 'page',
 ]);

  if ($child_pages) {
    return $child_pages;
  } else {
    return false;
  }
}

function cpt_list_child_pages($page_id) {
  if (!$page_id) return;

  $current_page_id = get_the_ID();
  $title = get_the_title($page_id);
  $url = get_the_permalink($page_id);

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
 * Knowledge Base Breadcrumbs
 */
function cpt_kb_breadcrumbs() {
  $breadcrumbs[] = '<span class="breadcrumb last-breadcrumb"><strong>' . get_the_title(get_the_ID()) . '</strong></span>';
  $parent_id = wp_get_post_parent_id(get_the_ID());

  while ($parent_id) {
    $parent_url = get_the_permalink($parent_id);
    $parent_title = get_the_title($parent_id);
    $breadcrumbs[] = '<span class="breadcrumb"><a href="' . $parent_url . '">' . $parent_title . '</a></span>';
    $parent_id = wp_get_post_parent_id($parent_id);
  }

  $breadcrumbs = array_reverse($breadcrumbs);

  ?>
    <div id="cpt-breadcrumbs">
      <?php echo implode(' / ', $breadcrumbs); ?>
    </div>
  <?php
}
