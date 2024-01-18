<?php

namespace Client_Power_Tools\Core\Frontend;
use Client_Power_Tools\Core\Common;

// Noindexes the client dashboard because it's none of Google's business.
add_action('wp_head',  __NAMESPACE__ . '\cpt_noindex_client_dashboard');
function cpt_noindex_client_dashboard() {
  if (Common\cpt_is_client_dashboard()) echo '<meta name="robots" content="noindex" />';
}


add_filter('the_content', __NAMESPACE__ . '\cpt_client_dashboard');
function cpt_client_dashboard($content) {
  if (
    !Common\cpt_is_client_dashboard() 
    || !is_main_query() 
    || !in_the_loop() 
    || get_post_type(get_the_ID()) == 'cpt_message'
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
    cpt_nav();
    cpt_breadcrumbs();
    echo '<h1 class="entry-title wp-block-post-title cpt-entry-title">' . cpt_get_the_title() . '</h1>';
    Common\cpt_get_notices();

    // Last Activity Timestamp
    update_user_meta($clients_user_id, 'cpt_last_activity', current_time('U', true));

    // Dashboard
    if (Common\cpt_is_client_dashboard('dashboard')) {
      $client_data = Common\cpt_get_client_data($clients_user_id);
      echo '<p><strong>' . sprintf(__('Welcome back, %s!', 'client-power-tools'), $client_data['first_name']) . '</p></strong>';
      if (
        get_option('cpt_module_status_update_req_button') && 
        !has_shortcode($content, 'status-update-request-button')
      ) Common\cpt_status_update_request_button($clients_user_id);
      return ob_get_clean() . $content;
    }

    // Projects
    if (get_option('cpt_module_projects') && Common\cpt_is_client_dashboard('projects')) {
      $projects_label = Common\cpt_get_projects_label();
      if (!isset($_REQUEST['projects_post_id'])) {
        Common\cpt_get_projects();
      } else {
        $projects_post_id = sanitize_key(intval($_REQUEST['projects_post_id']));
        $project_data = Common\cpt_get_project_data($projects_post_id);
        echo '<p><a href="' . remove_query_arg('projects_post_id') . '">' . sprintf(__('%s Back to %s', 'client-power-tools'), '&larr;', $projects_label[1]) . '</a></p>';
        if ($project_data['project_status']) {
          echo '<p class="cpt-project-status">';
            echo $project_data['project_status'];
            if ($project_data['project_type']) echo ' ' . $project_data['project_type'];
            echo ' ' . $projects_label[0];
          echo '</p>';
        }
        echo '<h2 class="cpt-project-title">';
          echo get_the_title($projects_post_id);
          if ($project_data['project_id']) echo ' <span style="color:silver">(' . $project_data['project_id'] . ')</span>';
        echo '</h2>';
        Common\cpt_get_project_progress_bar($projects_post_id);
      }
      return ob_get_clean();
    }

    // Messages
    if (get_option('cpt_module_messaging') && Common\cpt_is_client_dashboard('messages')) {
      Common\cpt_messages($clients_user_id);
      ?>
        <div class="form-wrap cpt-new-message-form">
          <h3><?php _e('New Message', 'client-power-tools'); ?></h3>
          <?php Common\cpt_new_message_form($clients_user_id); ?>
        </div>
      <?php
      return ob_get_clean();
    }
    
  return $content;
}


function cpt_nav() {
  remove_filter('the_title', 'Client_Power_Tools\Core\Frontend\cpt_client_dashboard_page_titles', 10);
  ?>
    <nav id="cpt-nav" class="wp-block-group has-global-padding is-layout-constrained">
      <ul class="cpt-tabs menu">
        <li class="cpt-tab menu-item<?php if (Common\cpt_is_client_dashboard('dashboard') && !isset($_REQUEST['tab'])) echo ' current-menu-item'; ?>"><a href="<?php echo Common\cpt_get_client_dashboard_url(); ?>"><?php _e('Home', 'client-power-tools'); ?></a></li>
        <?php if (get_option('cpt_module_messaging')) { ?>
          <li class="cpt-tab menu-item<?php if (Common\cpt_is_client_dashboard('messages')) echo ' current-menu-item'; ?>">
            <a href="<?php echo add_query_arg('tab', 'messages', Common\cpt_get_client_dashboard_url()); ?>"><?php _e('Messages', 'client-power-tools'); ?></a>
          </li>
        <?php } ?>
        <?php if (get_option('cpt_module_projects')) { ?>
          <li class="cpt-tab menu-item<?php if (Common\cpt_is_client_dashboard('projects')) echo ' current-menu-item'; ?>">
            <a href="<?php echo add_query_arg('tab', 'projects', Common\cpt_get_client_dashboard_url()); ?>"><?php echo Common\cpt_get_projects_label('plural'); ?></a>
          </li>
        <?php } ?>
        <?php if (get_option('cpt_module_knowledge_base')) { ?>
          <?php
            $kb_id = get_option('cpt_knowledge_base_page_selection');
            $kb_children_ids = cpt_get_child_pages($kb_id);
            
            $kb_classes = 'cpt-tab menu-item';
            if ($kb_children_ids) $kb_classes .= ' menu-item-has-children';
            if (Common\cpt_is_knowledge_base()) $kb_classes .= ' current-menu-item';
          ?>
          <li class="<?php echo $kb_classes; ?>">
            <a href="<?php echo Common\cpt_get_knowledge_base_url(); ?>"><?php echo get_the_title($kb_id); ?></a>
            <?php if ($kb_children_ids) echo cpt_get_submenu($kb_id); ?>
          </li>
        <?php } ?>
        <?php
          $addl_pages_ids = get_option('cpt_client_dashboard_addl_pages') ? explode(',', get_option('cpt_client_dashboard_addl_pages')) : false;
          if ($addl_pages_ids) {
            foreach($addl_pages_ids as $addl_page_id) {
              $addl_page_id = intval(trim($addl_page_id));
              $addl_page_children_ids = cpt_get_child_pages($addl_page_id);
              $addl_page_classes = 'cpt-tab menu-item';
              if (get_option('cpt_client_dashboard_addl_pages_children') && $addl_page_children_ids) $addl_page_classes .= ' menu-item-has-children';
              if ($addl_page_id == get_the_ID() || in_array($addl_page_id, get_post_ancestors(get_the_ID()))) $addl_page_classes .= ' current-menu-item';
              ?>
                <li class="<?php echo $addl_page_classes; ?>">
                  <a href="<?php echo get_permalink($addl_page_id); ?>"><?php echo get_the_title($addl_page_id); ?></a>
                  <?php if (get_option('cpt_client_dashboard_addl_pages_children') && $addl_page_children_ids) echo cpt_get_submenu($addl_page_id); ?>
                </li>
              <?php
            }
          }
        ?>
      </ul>
    </nav>
  <?php
}


/**
 * Nav Submenus
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

function cpt_get_submenu($page_id) {
  if (!$page_id) return;

  $child_pages = cpt_get_child_pages($page_id);
  if (!$child_pages) return;

  ob_start();
    ?>
      <ul class="sub-menu">
        <?php foreach($child_pages as $id) { ?>
          <?php
            $children = cpt_get_child_pages($id);
            $classes = 'menu-item';
            if ($id == get_the_ID()) $classes .= ' current-menu-item';
            if ($children) $classes .= ' menu-item-has-children';
          ?>
          <li class="<?php echo $classes; ?>">
            <a href="<?php echo get_permalink($id); ?>"><?php echo get_the_title($id); ?></a>
            <?php if ($children) cpt_get_submenu($id); ?>
          </li>
        <?php } ?>
      </ul>
    <?php
  return ob_get_clean();
}


/**
 * Knowledge Base Breadcrumbs
 */
function cpt_breadcrumbs() {
  if (!get_option('cpt_show_knowledge_base_breadcrumbs')) return;
  if (!Common\cpt_is_knowledge_base() && !Common\cpt_is_additional_page()) return;
  
  $this_page_id = get_the_ID();
  $parent_id = wp_get_post_parent_id($this_page_id);
  $breadcrumbs[] = '<span class="breadcrumb last-breadcrumb"><strong>' . get_the_title($this_page_id) . '</strong></span>';
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
