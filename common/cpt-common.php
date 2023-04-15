<?php

namespace Client_Power_Tools\Core\Common;

/**
 * Adds the Client and Client Manager user roles and capabilities, and assigns
 * all CPT capabilities to admins.
 */
function cpt_add_roles() {
  add_role(
    'cpt-client',
    'Client'
  );

  add_role(
    'cpt-client-manager',
    'Client Manager',
    [
      'cpt_view_clients'  => true,
      'cpt_view_projects' => true,
      'read'              => true,
    ]
  );

  $admin = get_role('administrator');
  $admin->add_cap('cpt_view_clients');
  $admin->add_cap('cpt_manage_clients');
  $admin->add_cap('cpt_manage_team');
  $admin->add_cap('cpt_view_projects');
  $admin->add_cap('cpt_manage_projects');
  $admin->add_cap('cpt_manage_settings');
}

add_action('init', __NAMESPACE__ . '\cpt_add_roles');

function cpt_is_client_dashboard() {
  global $wp_query;

  $client_dashboard_id = get_option('cpt_client_dashboard_page_selection');
  $this_page_id = isset($wp_query->post->ID) ? $wp_query->post->ID : false;

  if ($this_page_id && $client_dashboard_id == $this_page_id) {
    return true;
  } else {
    return false;
  }
}

function cpt_is_messages() {
  if (cpt_is_client_dashboard() && isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'messages') {
    return true;
  } else {
    return false;
  }
}

function cpt_is_knowledge_base() {
  global $wp_query;

  $knowledge_base_id    = get_option('cpt_knowledge_base_page_selection');
  $this_page_id         = isset($wp_query->post->ID) ? $wp_query->post->ID : false;
  $this_page_ancestors  = get_post_ancestors($this_page_id);

  if ($this_page_id && ($knowledge_base_id == $this_page_id || in_array($knowledge_base_id, $this_page_ancestors))) {
    return true;
  } else {
    return false;
  }
}


/**
 * Checks to see whether the current user is a client. Returns true if the current
 * user has the cpt-client role, false if not.
 *
 * If no user ID is provided, checks to see whether a user is logged-in with the
 * cpt-client role.
 */
function cpt_is_client($user_id = null) {
  if (!$user_id && !is_user_logged_in()) return false;
  if (!$user_id) $user_id = get_current_user_id();

  $user = get_userdata($user_id);

  if (
    $user && 
    $user->roles && 
    in_array('cpt-client', $user->roles)
  ) {
    return true;
  } else {
    return false;
  }
}

function cpt_get_clients($args = []) {
  $client_query_args = [
    'role' => 'cpt-client',
    'orderby' => isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'display_name',
    'order' => isset($_REQUEST['order']) ? sanitize_key($_REQUEST['order']) : 'ASC',
  ];
  $client_query_args = array_merge($client_query_args, $args);
  $clients = get_users($client_query_args);
  return $clients;
}

function cpt_get_client_profile_url($clients_user_id) {
  if (!$clients_user_id) return;
  return add_query_arg('user_id', $clients_user_id, admin_url('admin.php?page=cpt'));
}

function cpt_get_client_dashboard_url() {
  $page_id = get_option('cpt_client_dashboard_page_selection');
  return get_permalink($page_id);
}

function cpt_get_knowledge_base_url() {
  $page_id = get_option('cpt_knowledge_base_page_selection');
  return get_permalink($page_id);
}

function cpt_get_name($user_id) {
  if (!$user_id) return;
  $userdata = get_userdata($user_id);
  if (!$userdata) return;
  if (isset($userdata->first_name) && isset($userdata->last_name)) {
    $name = $userdata->first_name . ' ' . $userdata->last_name;
  } else {
    $name = $userdata->display_name;
  }
  return $name;
}

function cpt_custom_client_fields() {
  return apply_filters('cpt_custom_fields', []);
}

// Returns an array with the user's details.
function cpt_get_client_data($clients_user_id) {
  if (!$clients_user_id) return;
  $userdata = get_userdata($clients_user_id);
  $client_data = [
    'user_id'       => $clients_user_id,
    'first_name'    => get_user_meta($clients_user_id, 'first_name', true),
    'last_name'     => get_user_meta($clients_user_id, 'last_name', true),
    'email'         => $userdata->user_email,
    'client_id'     => get_user_meta($clients_user_id, 'cpt_client_id', true),
    'manager_id'    => cpt_get_client_manager_id($clients_user_id),
    'manager_email' => cpt_get_client_manager_email($clients_user_id),
    'status'        => get_user_meta($clients_user_id, 'cpt_client_status', true),
  ];
  
  $custom_fields = cpt_custom_client_fields();
  if ($custom_fields) {
    foreach ($custom_fields as $field) {
      $client_data[$field['id']] = get_user_meta($clients_user_id, $field['id'], true);
    }
  }
  return $client_data;
}

function cpt_get_client_manager_id($clients_user_id) {
  if (!$clients_user_id) return;
  $client_manager = get_user_meta($clients_user_id, 'cpt_client_manager', true);
  return $client_manager ? $client_manager : false;
}

function cpt_get_client_manager_email($clients_user_id) {
  if (!$clients_user_id) return;
  $userdata = get_userdata(get_user_meta($clients_user_id, 'cpt_client_manager', true));
  return isset($userdata->user_email) ? $userdata->user_email : false;
}


function cpt_get_email_card(
  $title = null,
  $content = null,
  $button_txt = 'Go',
  $button_url = null
) {
  $card_style = 'border: 1px solid #ddd; box-sizing: border-box; font-family: Jost, Helvetica, Arial, sans-serif; margin: 30px 3px 30px 0; padding: 30px; max-width: 500px;';
  $h2_style = 'margin-top: 0;';
  $button_style = 'background-color: #eee; border: 1px solid #ddd; box-sizing: border-box; display: block; margin: 0; padding: 1em; width: 100%; text-align: center;';

  ob_start();
    ?>
      <div class="cpt-card" align="left" style="<?php echo $card_style; ?>">
        <?php if (!empty($title)) { ?>
          <h2 style="<?php echo $h2_style; ?>"><?php echo $title; ?></h2>
        <?php } ?>
        <?php if (!empty($content)) echo $content; ?>
        <?php if (!empty($button_url)) { ?>
          <a class="button" href="<?php echo esc_url($button_url); ?>" style="<?php echo esc_attr($button_style); ?>"><?php echo $button_txt; ?></a>
        <?php } ?>
      </div>
    <?php
  return ob_get_clean();
}


/**
 * Checks for a transient with the results of an action, and if one exists,
 * outputs a notice. In the admin, this is a standard WordPress admin notice. On
 * the front end, this is a modal.
 */
function cpt_get_notices() {
  $transient = 'cpt_notice_for_user_' . get_current_user_id();
  $notice = get_transient($transient);
  if (!$notice) return;

  $classes = [
    'cpt-notice',
    'notice',
    'is-dismissible',
  ];
  $classes[] = is_wp_error($notice) ? 'notice-error' : 'notice-success';
  ?>
    <div class="<?php echo implode(' ', $classes); ?>">
      <?php if (!is_admin()) { ?>
        <button class="cpt-dismiss-button cpt-notice-dismiss-button">
          <?php echo file_get_contents(CLIENT_POWER_TOOLS_DIR_PATH . 'assets/images/close.svg'); ?>
        </button>
      <?php } ?>
      <p><?php echo $notice; ?></p>
    </div>
  <?php
  delete_transient($transient);
}

add_action('admin_notices', __NAMESPACE__ . '\cpt_get_notices');
