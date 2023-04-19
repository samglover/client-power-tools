<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;
use Client_Power_Tools\Core\Frontend;

function cpt_settings() {
  if (!current_user_can('cpt_manage_settings')) {
    wp_die(
      '<p>' . __('Sorry, you are not allowed to access this page.', 'client-power-tools') . '</p>',
      403
   );
  }

  ?>
    <div id="cpt-admin" class="wrap">
      <div id="cpt-admin-header">
        <?php echo file_get_contents(CLIENT_POWER_TOOLS_DIR_PATH . 'assets/images/cpt-logo.svg'); ?>
        <div id="cpt-admin-page-title">
          <h1 id="cpt-page-title"><?php _e('Settings', 'client-power-tools'); ?></h1>
          <p id="cpt-subtitle">Client Power Tools</p>
        </div>
      </div>
      <hr class="wp-header-end">

      <?php if (isset($_REQUEST['settings-updated']) && $_REQUEST['settings-updated'] == true) { ?>
        <div class="cpt-notice notice notice-success is-dismissible">
          <p><?php _e('Settings updated!', 'client-power-tools'); ?></p>
        </div>
      <?php } ?>

      <form method="POST" action="options.php">
        <?php settings_fields('cpt-settings'); ?>
        <?php do_settings_sections('cpt-settings'); ?>
        <?php submit_button(__('Save Settings', 'client-power-tools')); ?>
      </form>
    </div>
  <?php
}


// Client Dashboard
add_action('admin_init', __NAMESPACE__ . '\cpt_general_settings_init');
function cpt_general_settings_init() {
  add_settings_section(
    'cpt-general-settings',
    __('General Settings', 'client-power-tools'),
    __NAMESPACE__ . '\cpt_general_settings_section',
    'cpt-settings',
  );

  register_setting('cpt-settings', 'cpt_client_dashboard_page_selection');
  add_settings_field(
    'cpt_client_dashboard_page_selection',
    '<label for="cpt_client_dashboard_page_selection">' . __('Client Dashboard Page', 'client-power-tools') . '</label>',
    __NAMESPACE__ . '\cpt_client_dashboard_page_selection',
    'cpt-settings',
    'cpt-general-settings',
  );

  register_setting('cpt-settings', 'cpt_client_dashboard_addl_pages');
  add_settings_field(
    'cpt_client_dashboard_addl_pages',
    '<label for="cpt_client_dashboard_addl_pages">' . __('Additional Pages', 'client-power-tools') . '</label>',
    __NAMESPACE__ . '\cpt_client_dashboard_addl_pages',
    'cpt-settings',
    'cpt-general-settings',
  );

  register_setting('cpt-settings', 'cpt_client_dashboard_addl_pages_children', 'absint');
  add_settings_field(
    'cpt_client_dashboard_addl_pages_children',
    __('Include Child Pages?', 'client-power-tools'),
    __NAMESPACE__ . '\cpt_client_dashboard_addl_pages_children',
    'cpt-settings',
    'cpt-general-settings',
  );

  register_setting('cpt-settings', 'cpt_default_client_manager');
  add_settings_field(
    'cpt_default_client_manager',
    '<label for="cpt_default_client_manager">' . __('Default Client Manager', 'client-power-tools') . '</label>',
    __NAMESPACE__ . '\cpt_default_client_manager',
    'cpt-settings',
    'cpt-general-settings',
  );

  register_setting('cpt-settings', 'cpt_client_statuses');
  add_settings_field(
    'cpt_client_statuses',
    '<label for="cpt_client_statuses">' . __('Client Statuses', 'client-power-tools') . '</label>',
    __NAMESPACE__ . '\cpt_client_statuses',
    'cpt-settings',
    'cpt-general-settings',
  );

  register_setting('cpt-settings', 'cpt_default_client_status');
  add_settings_field(
    'cpt_default_client_status',
    '<label for="cpt_default_client_status">' . __('Default New Client Status', 'client-power-tools') . '</label>',
    __NAMESPACE__ . '\cpt_default_client_status',
    'cpt-settings',
    'cpt-general-settings',
  );
}


function cpt_general_settings_section() {
}


function cpt_client_dashboard_page_selection() {
  $page_query = new \WP_Query([
    'post_type'       => 'page',
    'posts_per_page'  => -1,
    'post_status'     => 'publish',
  ]);

  if ($page_query->have_posts()) :
    ?>
      <select name="cpt_client_dashboard_page_selection">
        <?php $selected = get_option('cpt_client_dashboard_page_selection'); ?>

        <?php while ($page_query->have_posts()) : $page_query->the_post(); ?>
          <?php $page_id = get_the_ID(); ?>
          <option value="<?php echo $page_id; ?>"<?php selected($selected, $page_id); ?>><?php the_title(); ?></option>
        <?php endwhile; ?>
      </select>

      <p class="description"><?php _e('When clients visit this page they will be prompted to log in and then shown their client dashboard.', 'client-power-tools'); ?> <a href="<?php echo Common\cpt_get_client_dashboard_url(); ?>" target="_blank"><?php _e('Visit the client dashboard.', 'client-power-tools'); ?></a></p>
    <?php
  else :
    ?>
      <p><?php _e('Sorry, you don\'t have any published pages.', 'client-power-tools'); ?></p>
    <?php
  endif;
}

function cpt_client_dashboard_addl_pages() {
  ?>
    <input name="cpt_client_dashboard_addl_pages" class="regular-text" type="text" value="<?php echo get_option('cpt_client_dashboard_addl_pages'); ?>">
    <p class="description"><?php _e('Add page IDs separated by commas. Note: adding a page will restrict that page to logged-in clients.', 'client-power-tools'); ?></p>
  <?php
}

function cpt_client_dashboard_addl_pages_children() {
  ?>
    <fieldset>
      <label for="cpt_client_dashboard_addl_pages_children">
        <input name="cpt_client_dashboard_addl_pages_children" id="cpt_client_dashboard_addl_pages_children" type="checkbox" value="1" <?php checked(get_option('cpt_client_dashboard_addl_pages_children')); ?>>
        <?php _e('Include a drop-down menu with descendants of additional pages (if any).', 'client-power-tools'); ?>
      </label>
    </fieldset>
  <?php
}

function cpt_default_client_manager() {
  echo cpt_get_client_manager_select('cpt_default_client_manager', get_option('cpt_default_client_manager'));
}


function cpt_client_statuses() {
  $statuses_array = explode("\n", get_option('cpt_client_statuses'));
  ob_start();
    foreach ($statuses_array as $i => $status) {
      echo sanitize_text_field($status);
      if ($i + 1 < count($statuses_array)) echo "\n";
    }
  $statuses = ob_get_clean();

  ?>
    <textarea name="cpt_client_statuses" class="small-text" rows="5"><?php echo $statuses; ?></textarea>
    <p class="description"><?php _e('Enter one status per line.', 'client-power-tools'); ?></p>
  <?php
}


function cpt_default_client_status() {
  echo cpt_get_status_select('cpt_client_statuses', 'cpt_default_client_status');
}


// New Client Email Settings
add_action('admin_init', __NAMESPACE__ . '\cpt_new_client_email_settings_init');
function cpt_new_client_email_settings_init() {
  add_settings_section(
    'cpt-new-client-email-settings',
    __('Client Account Activation Email', 'client-power-tools'),
    __NAMESPACE__ . '\cpt_new_client_email_section',
    'cpt-settings',
  );

  // Subject Line
  register_setting('cpt-settings', 'cpt_new_client_email_subject_line', 'sanitize_text_field');
  add_settings_field(
    'cpt_new_client_email_subject_line',
    '<label for="cpt_new_client_email_subject_line">' . __('Subject Line', 'client-power-tools') . '<br /><small>' . __('(required)', 'client-power-tools') . '</small></label>',
    __NAMESPACE__ . '\cpt_new_client_email_subject_line',
    'cpt-settings',
    'cpt-new-client-email-settings',
  );

  // Message Body
  register_setting('cpt-settings', 'cpt_new_client_email_message_body', 'sanitize_textarea_field');
  add_settings_field(
    'cpt_new_client_email_message_body',
    '<label for="cpt_new_client_email_message_body">' . __('Message Body', 'client-power-tools') . '<br /><small>' . __('(optional)', 'client-power-tools') . '</small></label>',
    __NAMESPACE__ . '\cpt_new_client_email_message_body',
    'cpt-settings',
    'cpt-new-client-email-settings',
  );
}


function cpt_new_client_email_section() {
  ?>
    <p><?php _e('Newly added clients will receive an email notification from their client manager with an account activation link. You can customize the subject line or add a message to the body of the email.', 'client-power-tools'); ?></p>
  <?php
}

function cpt_new_client_email_subject_line() {
  ?>
    <input name="cpt_new_client_email_subject_line" class="large-text" type="text" required aria-required="true" value="<?php echo get_option('cpt_new_client_email_subject_line'); ?>">
  <?php
}

function cpt_new_client_email_message_body() {
  ?>
    <textarea name="cpt_new_client_email_message_body" class="large-text" rows="5"><?php echo get_option('cpt_new_client_email_message_body'); ?></textarea>
    <p class="description"><?php _e('New users will be sent their username and an account activation link along with any additional message you choose to add here.', 'client-power-tools'); ?></p>
  <?php
}


// Status Update Request Button settings
add_action('admin_init', __NAMESPACE__ . '\cpt_status_update_request_button_settings_init');
function cpt_status_update_request_button_settings_init() {
  add_settings_section(
    'cpt-status-update-request-button-settings',
    __('Status Update Request Button', 'client-power-tools'),
    __NAMESPACE__ . '\cpt_status_update_request_button_section',
    'cpt-settings',
  );

  // Enable Status Update Request Button
  register_setting('cpt-settings', 'cpt_module_status_update_req_button', 'absint');
  add_settings_field(
    'cpt_module_status_update_req_button',
    __('Enable', 'client-power-tools'),
    __NAMESPACE__ . '\cpt_module_status_update_req_button',
    'cpt-settings',
    'cpt-status-update-request-button-settings',
  );

  if (get_option('cpt_module_status_update_req_button')) {
    // Status Update Request Frequency
    register_setting('cpt-settings', 'cpt_status_update_req_freq', 'absint');
    add_settings_field(
      'cpt_status_update_req_freq',
      '<label for="cpt_status_update_req_freq">' . __('Status Update Request Frequency', 'client-power-tools') . '<br /><small>' . __('(required)', 'client-power-tools') . '</small></label>',
      __NAMESPACE__ . '\cpt_status_update_req_freq',
      'cpt-settings',
      'cpt-status-update-request-button-settings',
    );

    // Status Update Request Notification Email
    register_setting('cpt-settings', 'cpt_status_update_req_notice_email', 'sanitize_email');
    add_settings_field(
      'cpt_status_update_req_notice_email',
      '<label for="cpt_status_update_req_notice_email">' . __('Additional Status Update Request Notification Email', 'client-power-tools') . '<br /><small>' . __('(optional)', 'client-power-tools') . '</small></label>',
      __NAMESPACE__ . '\cpt_status_update_req_notice_email',
      'cpt-settings',
      'cpt-status-update-request-button-settings',
    );
  }
}


function cpt_status_update_request_button_section() {
  _e('The status update request button makes it easy for clients to prompt you for a status update—but only as frequently as you specify.', 'client-power-tools');
}

function cpt_module_status_update_req_button() {
  ?>
    <fieldset>
      <label for="cpt_module_status_update_req_button">
        <input name="cpt_module_status_update_req_button" id="cpt_module_status_update_req_button" type="checkbox" value="1" <?php checked(get_option('cpt_module_status_update_req_button')); ?>>
        <?php _e('Enable the status update request button.', 'client-power-tools'); ?>
      </label>
    </fieldset>
  <?php
}

function cpt_status_update_req_freq() {
  ?>
    <input name="cpt_status_update_req_freq" class="small-text" type="number" required aria-required="true" value="<?php echo get_option('cpt_status_update_req_freq'); ?>"> <?php _e('days', 'client-power-tools'); ?>
    <p class="description"><?php printf(__('Enter how frequently you want to allow your clients to request a status update using the %sRequest Status Update%s button on their client dashboard.', 'client-power-tools'), '<strong>', '</strong>'); ?></p>
  <?php
}

function cpt_status_update_req_notice_email() {
  ?>
    <input name="cpt_status_update_req_notice_email" class="regular-text" type="email" value="<?php echo get_option('cpt_status_update_req_notice_email'); ?>">
    <p class="description"><?php _e('Status update request notifications are sent to the assigned client manager. This address will be CC\'d.', 'client-power-tools'); ?></p>
  <?php
}


// Client Messages Settings
add_action('admin_init', __NAMESPACE__ . '\cpt_client_messaging_settings_init');
function cpt_client_messaging_settings_init() {
  add_settings_section(
    'cpt-client-messaging-settings',
    __('Messages', 'client-power-tools'),
    __NAMESPACE__ . '\cpt_client_messaging_section',
    'cpt-settings',
  );

  // Enable Messaging
  register_setting('cpt-settings', 'cpt_module_messaging', 'absint');
  add_settings_field(
    'cpt_module_messaging',
    __('Enable', 'client-power-tools'),
    __NAMESPACE__ . '\cpt_module_messaging',
    'cpt-settings',
    'cpt-client-messaging-settings',
  );

  if (get_option('cpt_module_messaging')) {
    // Send Message Content
    register_setting('cpt-settings', 'cpt_send_message_content', 'absint');
    add_settings_field(
      'cpt_send_message_content',
      __('Email Notification Content', 'client-power-tools'),
      __NAMESPACE__ . '\cpt_send_message_content',
      'cpt-settings',
      'cpt-client-messaging-settings',
    );
  }

}


function cpt_client_messaging_section() {
  _e('Messages lets you keep all your client communications in one place so nothing gets lost. You can customize whether clients receive a notice of new messages or the full message.', 'client-power-tools');
}


function cpt_module_messaging() {
  ?>
    <fieldset>
      <label for="cpt_module_messaging">
        <input name="cpt_module_messaging" id="cpt_module_messaging" type="checkbox" value="1" <?php checked(get_option('cpt_module_messaging')); ?>>
        <?php _e('Enable messaging.', 'client-power-tools'); ?>
      </label>
    </fieldset>
  <?php
}


function cpt_send_message_content() {
  $send_message_content = get_option('cpt_send_message_content');

  ?>
    <fieldset>
      <label for="cpt_send_message_content">
        <input name="cpt_send_message_content" id="cpt_send_message_content" type="checkbox" value="1" <?php checked($send_message_content); ?>>
        <?php _e('Send message content.', 'client-power-tools'); ?>
        <p class="description"><?php _e('If checked, the client will receive the full content of messages by email instead of a notification with a prompt to log into their client portal. This is less secure.', 'client-power-tools'); ?></p>
      </label>
    </fieldset>
  <?php
}


// Projects/Matters
add_action('admin_init', __NAMESPACE__ . '\cpt_projects_settings_init');
function cpt_projects_settings_init() {
  add_settings_section(
    'cpt-projects-settings',
    __('Projects', 'client-power-tools'),
    __NAMESPACE__ . '\cpt_projects_section',
    'cpt-settings',
  );

  // Enable Projects
  register_setting('cpt-settings', 'cpt_module_projects', 'absint');
  add_settings_field(
    'cpt_module_projects',
    __('Enable', 'client-power-tools'),
    __NAMESPACE__ . '\cpt_module_projects',
    'cpt-settings',
    'cpt-projects-settings',
  );

  if (get_option('cpt_module_projects')) {
    register_setting('cpt-settings', 'cpt_projects_label');
    add_settings_field(
      'cpt_projects_label',
      __('Projects Label', 'client-power-tools'),
      __NAMESPACE__ . '\cpt_projects_label',
      'cpt-settings',
      'cpt-projects-settings',
    );

    register_setting('cpt-settings', 'cpt_project_statuses');
    add_settings_field(
      'cpt_project_statuses',
      '<label for="cpt_project_statuses">' . __('Project Statuses', 'client-power-tools') . '</label>',
      __NAMESPACE__ . '\cpt_project_statuses',
      'cpt-settings',
      'cpt-projects-settings',
    );

    register_setting('cpt-settings', 'cpt_default_project_status');
    add_settings_field(
      'cpt_default_project_status',
      '<label for="cpt_default_project_status">' . __('Default Project Status', 'client-power-tools') . '</label>',
      __NAMESPACE__ . '\cpt_default_project_status',
      'cpt-settings',
      'cpt-projects-settings',
    );

    add_settings_section(
      'cpt-stages-settings',
      __('Stages', 'client-power-tools'),
      __NAMESPACE__ . '\cpt_stages_section',
      'cpt-settings',
    );

    // Enable Stages
    register_setting('cpt-settings', 'cpt_module_stages', 'absint');
    add_settings_field(
      'cpt_module_stages',
      __('Enable', 'client-power-tools'),
      __NAMESPACE__ . '\cpt_module_stages',
      'cpt-settings',
      'cpt-stages-settings',
    );

    if (get_option('cpt_module_stages')) {
    }
  }
}


function cpt_projects_section() {
}

function cpt_module_projects() {
  ?>
    <fieldset>
      <label for="cpt_module_projects">
        <input name="cpt_module_projects" id="cpt_module_projects" type="checkbox" value="1" <?php checked(get_option('cpt_module_projects')); ?>>
        <?php _e('Enable projects.', 'client-power-tools'); ?>
      </label>
    </fieldset>
  <?php
}

if (get_option('cpt_module_projects')) {
  function cpt_projects_label() {
    ?>
      <p class="description"><?php _e('What do you want to call your projects?', 'client-power-tools'); ?></p>
      <ul>
        <li>
          <fieldset>
            <input name="cpt_projects_label[0]" type="text" value="<?php echo Common\cpt_get_projects_label('singular'); ?>">
            <label for="cpt_projects_label[0]">Singular</label>
          </fieldset>
        </li>
        <li>
          <fieldset>
            <input name="cpt_projects_label[1]" type="text" value="<?php echo Common\cpt_get_projects_label('plural'); ?>">
            <label for="cpt_projects_label[1]">Plural</label>
          </fieldset>
        </li>
      </ul>
    <?php
  }
  
  
  function cpt_project_statuses() {
    $statuses_array = explode("\n", get_option('cpt_project_statuses'));
    ob_start();
      foreach ($statuses_array as $i => $status) {
        echo sanitize_text_field($status);
        if ($i + 1 < count($statuses_array)) echo "\n";
      }
    $statuses = ob_get_clean();
  
    ?>
      <textarea name="cpt_project_statuses" class="small-text" rows="5"><?php echo $statuses; ?></textarea>
      <p class="description"><?php _e('Enter one status per line. Statuses apply to all project types.', 'client-power-tools'); ?></p>
    <?php
  }
  
  
  function cpt_default_project_status() {
    echo cpt_get_status_select('cpt_project_statuses', 'cpt_default_project_status');
  }

  function cpt_stages_section() {
  }

  function cpt_module_stages() {
    ?>
      <fieldset>
        <label for="cpt_module_stages">
          <input name="cpt_module_stages" id="cpt_module_stages" type="checkbox" value="1" <?php checked(get_option('cpt_module_stages')); ?>>
          <?php _e('Enable stages.', 'client-power-tools'); ?>
        </label>
      </fieldset>
    <?php
  }

  if (get_option('cpt_module_stages')) {
  }
}

// Knowledge Base
add_action('admin_init', __NAMESPACE__ . '\cpt_knowledge_base_settings_init');
function cpt_knowledge_base_settings_init() {
  add_settings_section(
    'cpt-knowledge-base-settings',
    __('Knowledge Base', 'client-power-tools'),
    __NAMESPACE__ . '\cpt_knowledge_base_section',
    'cpt-settings',
  );

  // Enable Knowledge Base
  register_setting('cpt-settings', 'cpt_module_knowledge_base', 'absint');
  add_settings_field(
    'cpt_module_knowledge_base',
    __('Enable', 'client-power-tools'),
    __NAMESPACE__ . '\cpt_module_knowledge_base',
    'cpt-settings',
    'cpt-knowledge-base-settings',
  );

  register_setting('cpt-settings', 'cpt_show_knowledge_base_breadcrumbs', 'absint');
  add_settings_field(
    'cpt_show_knowledge_base_breadcrumbs',
    __('Breadcrumbs', 'client-power-tools'),
    __NAMESPACE__ . '\cpt_show_knowledge_base_breadcrumbs',
    'cpt-settings',
    'cpt-knowledge-base-settings',
  );

  if (get_option('cpt_module_knowledge_base')) {
    register_setting('cpt-settings', 'cpt_knowledge_base_page_selection');
    add_settings_field(
      'cpt_knowledge_base_page_selection',
      '<label for="cpt_knowledge_base_page_selection">' . __('Knowledge Base Page', 'client-power-tools') . '</label>',
      __NAMESPACE__ . '\cpt_knowledge_base_page_selection',
      'cpt-settings',
      'cpt-knowledge-base-settings',
    );
  }
}


function cpt_knowledge_base_section() {
  _e('The knowledge base is a restricted page you can use to share information and resources with your clients.', 'client-power-tools');
}


function cpt_module_knowledge_base() {
  ?>
    <fieldset>
      <label for="cpt_module_knowledge_base">
        <input name="cpt_module_knowledge_base" id="cpt_module_knowledge_base" type="checkbox" value="1" <?php checked(get_option('cpt_module_knowledge_base')); ?>>
        <?php _e('Enable knowledge base.', 'client-power-tools'); ?>
      </label>
    </fieldset>
  <?php
}

function cpt_show_knowledge_base_breadcrumbs() {
  ?>
    <fieldset>
      <label for="cpt_show_knowledge_base_breadcrumbs">
        <input name="cpt_show_knowledge_base_breadcrumbs" id="cpt_show_knowledge_base_breadcrumbs" type="checkbox" value="1" <?php checked(get_option('cpt_show_knowledge_base_breadcrumbs')); ?>>
        <?php _e('Show breadcrumb navigation within the knowledge base.', 'client-power-tools'); ?>
      </label>
    </fieldset>
  <?php
}


function cpt_knowledge_base_page_selection() {
  $page_query = new \WP_Query([
    'post_type'       => 'page',
    'posts_per_page'  => -1,
    'post_status'     => 'publish',
  ]);

  if ($page_query->have_posts()) :
    ?>
      <select name="cpt_knowledge_base_page_selection">
        <?php $selected = get_option('cpt_knowledge_base_page_selection'); ?>
        <?php while ($page_query->have_posts()) : $page_query->the_post(); ?>
          <?php $page_id = get_the_ID(); ?>
          <option value="<?php echo $page_id; ?>"<?php selected($selected, $page_id); ?>><?php the_title(); ?></option>
        <?php endwhile; ?>
      </select>
      <p class="description"><?php _e('This page and its child pages will be restricted to clients.', 'client-power-tools') . ' <a href="' . Common\cpt_get_knowledge_base_url() . '" target="_blank">' . __('Visit the knowledge base.', 'client-power-tools'); ?></a></p>
    <?php
  else :
    ?>
      <p>Sorry, you don't have any published pages.</p>
    <?php
  endif;
}