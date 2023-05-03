<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

add_action('admin_init', __NAMESPACE__ . '\cpt_redirect_clients');
function cpt_redirect_clients() {
  global $pagenow;
  if (
    Common\cpt_is_client() &&
    !current_user_can('cpt_manage_clients') &&
    !(defined('DOING_AJAX') && DOING_AJAX) &&
    $pagenow !== 'admin-post.php'
  ) {
    wp_safe_redirect(home_url());
    exit;
  }
}


add_action('wp_loaded', __NAMESPACE__ . '\cpt_admin_actions');
function cpt_admin_actions() {
  if (
    !isset($_REQUEST['action']) || 
    !isset($_REQUEST['page']) || 
    !isset($_REQUEST['_wpnonce'])
  ) return;
  if (!wp_verify_nonce($_REQUEST['_wpnonce'])) exit(__('Invalid nonce.', 'client-power-tools'));

  $page = sanitize_text_field($_REQUEST['page']);

  switch ($page) {
    case 'cpt-project-types':
      cpt_process_project_type_actions(sanitize_text_field($_REQUEST['action']));
      break;
    default:
      exit(__('Unknown page.', 'client-power-tools'));
  }
}


add_action('admin_notices', __NAMESPACE__ . '\cpt_security_warning', 1);
function cpt_security_warning() {
  global $pagenow;
  if (!is_ssl() && cpt_is_cpt_admin_page()) {
    ?>
      <div class="cpt-notice notice notice-warning">
        <p><?php _e('It doesn\'t look like your website is using SSL (HTTPS). Before using Client Power Tools with your clients, it\'s a good idea to get an SSL certificate for your website and consider additional security measures. <a href="https://clientpowertools.com/security/?utm_source=cpt_user&utm_medium=cpt_ssl_warning" target="_blank">Learn more.</a>'); ?></p>
      </div>
    <?php
  }
}


add_action('admin_notices', __NAMESPACE__ . '\cpt_welcome_message');
function cpt_welcome_message() {
  global $pagenow;
  if (cpt_is_cpt_admin_page() && get_transient('cpt_show_welcome_message')) {
    ?>
      <div class="cpt-notice notice notice-info">
        <h2><?php _e('Welcome to Client Power Tools!'); ?></h2>
        <p style="font-size: 125%;"><?php _e('You can view and manage your clients here, in the WordPress dashboard. You can add your first client on the <a href="' . esc_url(admin_url('admin.php?page=cpt')) . '" target="_blank">Clients page</a> (if you are an admin).'); ?></p>
        <p style="font-size: 125%;"><?php _e('Your clients can access their dashboard by visiting <a href="' . Common\cpt_get_client_dashboard_url() . '" target="_blank">this page</a> on the front end of your website (clients don\'t have access to the WordPress admin dashboard). You\'ll probably want to add that page to your navigation menu to make it easy for your clients to find.'); ?></p>
        <p style="font-size: 125%;"><?php _e('You can find options and customizations in the settings, and you can find additional documentation at <a href="https://clientpowertools.com/documentation/" target="_blank">clientpowertools.com</a>. If you need help, please use the <a href="https://wordpress.org/support/plugin/client-power-tools/" target="_blank">support forum</a>.'); ?></p>
        <p style="font-size: 125%;"><?php _e('Please let me know what you think on Twitter, where I\'m <a href="https://twitter.com/samglover" target="_blank">@samglover</a>, or <a href="https://wordpress.org/plugins/client-power-tools/#reviews" target="_blank">leave a review on WordPress.org</a>.'); ?></p>
        <p style="font-size: 125%;"><?php _e('—Sam'); ?></p>
      </div>
    <?php
    delete_transient('cpt_show_welcome_message');
  }
}


add_action('admin_menu', __NAMESPACE__ . '\cpt_menu_pages');
function cpt_menu_pages() {
  add_menu_page(
    'Client Power Tools',
    'Clients',
    'cpt_view_clients',
    'cpt',
    __NAMESPACE__ . '\cpt_clients',
    CLIENT_POWER_TOOLS_DIR_URL . 'assets/images/cpt-icon.svg',
    '3', // Position
  );

  add_submenu_page(
    'cpt',
    'Client Power Tools: Clients',
    'Clients',
    'cpt_view_clients',
    'cpt',
    __NAMESPACE__ . '\cpt_clients',
  );

  if (get_option('cpt_module_projects')) {
    $projects_label = Common\cpt_get_projects_label();
    add_submenu_page(
      'cpt',
      'Client Power Tools: ' . $projects_label[1],
      $projects_label[1],
      'cpt_view_projects',
      'cpt-projects',
      __NAMESPACE__ . '\cpt_projects',
    );

    add_submenu_page(
      'cpt',
      'Client Power Tools: ' . $projects_label[0] . ' Types',
      $projects_label[0] . ' Types',
      'cpt_view_projects',
      'cpt-project-types',
      __NAMESPACE__ . '\cpt_project_types',
    );
  }

  if (get_option('cpt_module_messaging')) {
    add_submenu_page(
      'cpt',
      'Client Power Tools: Messages',
      'Messages',
      'cpt_view_clients',
      'cpt-messages',
      __NAMESPACE__ . '\cpt_admin_messages',
    );
  }

  add_submenu_page(
    'cpt',
    'Client Power Tools: Client Managers',
    'Managers',
    'cpt_manage_team',
    'cpt-managers',
    __NAMESPACE__ . '\cpt_client_managers',
  );

  add_submenu_page(
    'cpt',
    'Client Power Tools: Settings',
    'Settings',
    'cpt_manage_settings',
    'cpt-settings',
    __NAMESPACE__ . '\cpt_settings',
  );
}


function cpt_is_cpt_admin_page() {
  global $pagenow;
  if ($pagenow == 'admin.php' && preg_match('/cpt-?\S*/', $_GET['page'])) {
    return true;
  } else {
    return false;
  }
}


function cpt_get_client_manager_select($name = null, $selected = null) {
  if (!$name) $name = 'client_manager';
  if (!$selected) $selected = get_option('cpt_default_client_manager');

  // Query Client Managers
  $client_manager_query = new \WP_USER_QUERY([
    'role__in'  => ['cpt-client-manager'],
    'orderby'   => 'display_name',
    'order'     => 'ASC',
  ]);

  $client_managers = $client_manager_query->get_results();

  if ($client_managers) {
    echo '<select name="' . $name . '" id="' . $name . '">';
      echo '<option value>'. __('Unassigned', 'client-power-tools') . '</option>';
      foreach ($client_managers as $client_manager) {
        echo '<option value="' . $client_manager->ID . '"' . selected($client_manager->ID, $selected) . '>' . $client_manager->display_name . '</option>';
      }
    echo '</select>';
  } else {
    echo '<p class="description"><a href="' . get_admin_url() . 'admin.php?page=cpt-managers">' . __('Add a client manager.', 'client-power-tools') . '</a></p>';
  }
}


function cpt_get_status_select($option = null, $name = null, $selected = null) {
  if (!$option || !$name) return;
  $statuses_array = explode("\n", get_option($option));
  if (!$selected) $selected = get_option($name);
  echo '<select name="' . $name . '" id="' . $name . '">';
    foreach ($statuses_array as $status) {
      $status = trim($status);
      echo '<option value="' . $status . '"' . selected($status, $selected) . '>' . $status . '</option>';
    }
  echo '</select>';
}

add_action('wp_mail_failed', __NAMESPACE__ . '\cpt_show_wp_mail_errors', 10, 1);
function cpt_show_wp_mail_errors($wp_error) {
  echo '<pre>';
    print_r($wp_error);
  echo '</pre>';
}