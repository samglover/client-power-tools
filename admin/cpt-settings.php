<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;
use Client_Power_Tools\Core\Frontend;

function cpt_settings() {

  if ( ! current_user_can( 'cpt-manage-settings' ) ) {
    wp_die(
      '<p>' . __( 'Sorry, you are not allowed to access this page.', 'client-power-tools' ) . '</p>',
      403
    );
  }

  ob_start();

    ?>

      <div id="cpt-admin" class="wrap">

        <div id="cpt-admin-header">
          <img src="<?php echo CLIENT_POWER_TOOLS_DIR_URL; ?>admin/images/cpt-logo.svg" height="auto" width="100%" />
          <div id="cpt-admin-page-title">
            <h1 id="cpt-page-title"><?php _e( 'Settings', 'client-power-tools' ); ?></h1>
            <p id="cpt-subtitle">Client Power Tools</p>
          </div>
        </div>
        <hr class="wp-header-end">

        <?php if ( isset( $_REQUEST[ 'settings-updated' ] ) && $_REQUEST[ 'settings-updated' ] == true ) { ?>

          <div class="cpt-notice notice notice-success is-dismissible">
            <p><?php _e( 'Settings updated!', 'client-power-tools' ); ?></p>
          </div>

        <?php } ?>

        <form method="POST" action="options.php">
          <?php settings_fields( 'cpt-settings' ); ?>
          <?php do_settings_sections( 'cpt-settings' ); ?>
          <?php submit_button( __( 'Save Settings', 'client-power-tools' ) ); ?>
        </form>

      </div>

    <?php

  echo ob_get_clean();

}


// Client Dashboard
function cpt_general_settings_init() {

  add_settings_section(
    'cpt-general-settings',
    __( 'General Settings', 'client-power-tools' ),
    __NAMESPACE__ . '\cpt_general_settings_section',
    'cpt-settings',
  );

  add_settings_field(
    'cpt_client_dashboard_page_selection',
    '<label for="cpt_client_dashboard_page_selection">' . __( 'Client Dashboard Page', 'client-power-tools' ) . '</label>',
    __NAMESPACE__ . '\cpt_client_dashboard_page_selection',
    'cpt-settings',
    'cpt-general-settings',
  );

  register_setting( 'cpt-settings', 'cpt_client_dashboard_page_selection' );

  add_settings_field(
    'cpt_default_client_manager',
    '<label for="cpt_default_client_manager">' . __( 'Default Client Manager', 'client-power-tools' ) . '</label>',
    __NAMESPACE__ . '\cpt_default_client_manager',
    'cpt-settings',
    'cpt-general-settings',
  );

  register_setting( 'cpt-settings', 'cpt_default_client_manager' );

  add_settings_field(
    'cpt_client_statuses',
    '<label for="cpt_client_statuses">' . __( 'Client Statuses', 'client-power-tools' ) . '</label>',
    __NAMESPACE__ . '\cpt_client_statuses',
    'cpt-settings',
    'cpt-general-settings',
  );

  register_setting( 'cpt-settings', 'cpt_client_statuses' );

  add_settings_field(
    'cpt_default_client_status',
    '<label for="cpt_default_client_status">' . __( 'Default New-Client Status', 'client-power-tools' ) . '</label>',
    __NAMESPACE__ . '\cpt_default_client_status',
    'cpt-settings',
    'cpt-general-settings',
  );

  register_setting( 'cpt-settings', 'cpt_default_client_status' );

}

add_action( 'admin_init', __NAMESPACE__ . '\cpt_general_settings_init' );


function cpt_general_settings_section() {
  echo '<p>' . __( 'Customize the core features of Client Power Tools.', 'client-power-tools' ) . '</p>';
}


function cpt_client_dashboard_page_selection() {

  $args = [
    'post_type'       => 'page',
    'posts_per_page'  => -1,
    'post_status'     => 'publish',
  ];

  $page_query = new \WP_Query( $args );

  if ( $page_query->have_posts() ) :

    echo '<select name="cpt_client_dashboard_page_selection">';

      $selected = get_option( 'cpt_client_dashboard_page_selection' );

      while ( $page_query->have_posts() ) : $page_query->the_post();

        $page_id = get_the_ID();

        echo '<option value="' . $page_id . '"';

        if ( $selected == $page_id ) {
          echo ' selected';
        }

        echo '>' . get_the_title() . '</option>';

      endwhile;

    echo '</select>';

    echo '<p class="description">';
      _e( 'When clients visit this page they will be prompted to log in and then shown their client dashboard.', 'client-power-tools' );
      echo ' ' . '<a href="' . Common\cpt_get_client_dashboard_url() . '" target="_blank">' . __( 'Visit the client dashboard.', 'client-power-tools' ) . '</a>';
    echo '</p>';

  else :

    echo '<p>' . __( 'Sorry, you don\'t have any published pages.', 'client-power-tools' ) . '</p>';

  endif;

}


function cpt_default_client_manager() {
  echo cpt_get_client_manager_select( 'cpt_default_client_manager', get_option( 'cpt_default_client_manager' ) );
}


function cpt_client_statuses() {

  $statuses_array = explode( "\n", get_option( 'cpt_client_statuses' ) );

  ob_start();

    foreach ( $statuses_array as $i => $status ) {

      echo sanitize_text_field( $status );

      if ( $i + 1 < count( $statuses_array ) ) {
        echo "\n";
      }

    }

  $statuses = ob_get_clean();

  echo '<textarea name="cpt_client_statuses" class="small-text" rows="5">' . $statuses . '</textarea>';
  echo '<p class="description">' . __( 'Enter one status per line.', 'client-power-tools' ) . '</p>';

}


function cpt_default_client_status() {
  echo cpt_get_client_statuses_select( 'cpt_default_client_status' );
}


// New Client Email Settings
function cpt_new_client_email_settings_init() {

  add_settings_section(
    'cpt-new-client-email-settings',
    __( 'Client Account Activation Email', 'client-power-tools' ),
    __NAMESPACE__ . '\cpt_new_client_email_section',
    'cpt-settings',
  );

  // Subject Line
  add_settings_field(
    'cpt_new_client_email_subject_line',
    '<label for="cpt_new_client_email_subject_line">' . __( 'Subject Line', 'client-power-tools' ) . '<br /><small>' . __( '(required)', 'client-power-tools' ) . '</small></label>',
    __NAMESPACE__ . '\cpt_new_client_email_subject_line',
    'cpt-settings',
    'cpt-new-client-email-settings',
  );

  register_setting( 'cpt-settings', 'cpt_new_client_email_subject_line', 'sanitize_text_field' );

  // Message Body
  add_settings_field(
    'cpt_new_client_email_message_body',
    '<label for="cpt_new_client_email_message_body">' . __( 'Message Body', 'client-power-tools' ) . '<br /><small>' . __( '(optional)', 'client-power-tools' ) . '</small></label>',
    __NAMESPACE__ . '\cpt_new_client_email_message_body',
    'cpt-settings',
    'cpt-new-client-email-settings',
  );

  register_setting( 'cpt-settings', 'cpt_new_client_email_message_body', 'sanitize_textarea_field' );

}

add_action( 'admin_init', __NAMESPACE__ . '\cpt_new_client_email_settings_init' );


function cpt_new_client_email_section() {
  echo '<p>' . __( 'Newly added clients will receive an email notification from their client manager with an account activation link. You can customize the subject line or add a message to the body of the email.' ) . '</p>';
}

function cpt_new_client_email_subject_line() {
  echo '<input name="cpt_new_client_email_subject_line" class="large-text" type="text" required aria-required="true" value="' . get_option( 'cpt_new_client_email_subject_line' ) . '">';
}

function cpt_new_client_email_message_body() {
  echo '<textarea name="cpt_new_client_email_message_body" class="large-text" rows="5">' . get_option( 'cpt_new_client_email_message_body' ) . '</textarea>';
}


// Status Update Request Button settings
function cpt_status_update_request_button_settings_init() {

  add_settings_section(
    'cpt-status-update-request-button-settings',
    __( 'Status Update Request Button', 'client-power-tools' ),
    __NAMESPACE__ . '\cpt_status_update_request_button_section',
    'cpt-settings',
  );

  // Enable Status Update Request Button
  add_settings_field(
    'cpt_module_status_update_req_button',
    __( 'Enable', 'client-power-tools' ),
    __NAMESPACE__ . '\cpt_module_status_update_req_button',
    'cpt-settings',
    'cpt-status-update-request-button-settings',
  );

  register_setting( 'cpt-settings', 'cpt_module_status_update_req_button', 'absint' );

  if ( get_option( 'cpt_module_status_update_req_button' ) ) {

    // Status Update Request Frequency
    add_settings_field(
      'cpt_status_update_req_freq',
      '<label for="cpt_status_update_req_freq">' . __( 'Status Update Request Frequency', 'client-power-tools' ) . '<br /><small>' . __( '(required)', 'client-power-tools' ) . '</small></label>',
      __NAMESPACE__ . '\cpt_status_update_req_freq',
      'cpt-settings',
      'cpt-status-update-request-button-settings',
    );

    register_setting( 'cpt-settings', 'cpt_status_update_req_freq', 'absint' );

    // Status Update Request Notification Email
    add_settings_field(
      'cpt_status_update_req_notice_email',
      '<label for="cpt_status_update_req_notice_email">' . __( 'Additional Status Update Request Notification Email', 'client-power-tools' ) . '<br /><small>' . __( '(optional)', 'client-power-tools' ) . '</small></label>',
      __NAMESPACE__ . '\cpt_status_update_req_notice_email',
      'cpt-settings',
      'cpt-status-update-request-button-settings',
    );

    register_setting( 'cpt-settings', 'cpt_status_update_req_notice_email', 'sanitize_email' );

  }

}

add_action( 'admin_init', __NAMESPACE__ . '\cpt_status_update_request_button_settings_init' );


function cpt_status_update_request_button_section() {
  _e( 'The status update request button makes it easy for clients to prompt you for a status update—but only as frequently as you specify.', 'client-power-tools' );
}

function cpt_module_status_update_req_button() {

  ob_start();

    ?>

      <fieldset>
        <label for="cpt_module_status_update_req_button">
          <input name="cpt_module_status_update_req_button" id="cpt_module_status_update_req_button" type="checkbox" value="1" <?php checked( get_option( 'cpt_module_status_update_req_button' ) ); ?>>
          <?php _e( 'Enable the status update request button.', 'client-power-tools' ); ?>
        </label>
      </fieldset>

    <?php

  echo ob_get_clean();

}

function cpt_status_update_req_freq() {
  echo '<input name="cpt_status_update_req_freq" class="small-text" type="number" required aria-required="true" value="' . get_option( 'cpt_status_update_req_freq' ) . '"> ' . __( 'days', 'client-power-tools' );
  echo '<p class="description">' . sprintf( __( 'Enter how frequently you want to allow your clients to request a status update using the %sRequest Status Update%s button on their client dashboard.', 'client-power-tools' ), '<strong>', '</strong>' ) . '</p>';
}

function cpt_status_update_req_notice_email() {
  echo '<input name="cpt_status_update_req_notice_email" class="regular-text" type="email" value="' . get_option( 'cpt_status_update_req_notice_email' ) . '">';
  echo '<p class="description">' . __( 'This address will be CC\'d when a client requests a status update.', 'client-power-tools' ) . '</p>';
}


// Client Messages Settings
function cpt_client_messaging_settings_init() {

  add_settings_section(
    'cpt-client-messaging-settings',
    __( 'Messages', 'client-power-tools' ),
    __NAMESPACE__ . '\cpt_client_messaging_section',
    'cpt-settings',
  );

  // Enable Messaging
  add_settings_field(
    'cpt_module_messaging',
    __( 'Enable', 'client-power-tools' ),
    __NAMESPACE__ . '\cpt_module_messaging',
    'cpt-settings',
    'cpt-client-messaging-settings',
  );

  register_setting( 'cpt-settings', 'cpt_module_messaging', 'absint' );

  if ( get_option( 'cpt_module_messaging' ) ) {

    // Send Message Content
    add_settings_field(
      'cpt_send_message_content',
      __( 'Email Notification Content', 'client-power-tools' ),
      __NAMESPACE__ . '\cpt_send_message_content',
      'cpt-settings',
      'cpt-client-messaging-settings',
    );

    register_setting( 'cpt-settings', 'cpt_send_message_content', 'absint' );

  }

}

add_action( 'admin_init', __NAMESPACE__ . '\cpt_client_messaging_settings_init' );


function cpt_client_messaging_section() {
  _e( 'Messages lets you keep all your client communications in one place so nothing gets lost. You can customize whether clients receive a notice of new messages or the full message.', 'client-power-tools' );
}


function cpt_module_messaging() {

  ob_start();

    ?>

      <fieldset>
        <label for="cpt_module_messaging">
          <input name="cpt_module_messaging" id="cpt_module_messaging" type="checkbox" value="1" <?php checked( get_option( 'cpt_module_messaging' ) ); ?>>
          <?php _e( 'Enable messaging.', 'client-power-tools' ); ?>
        </label>
      </fieldset>

    <?php

  echo ob_get_clean();

}


function cpt_send_message_content() {

  $send_message_content = get_option( 'cpt_send_message_content' );

  ob_start();

    ?>

      <fieldset>
        <label for="cpt_send_message_content">
          <input name="cpt_send_message_content" id="cpt_send_message_content" type="checkbox" value="1" <?php checked( $send_message_content ); ?>>
          <?php _e( 'Send message content.', 'client-power-tools' ); ?>
          <p class="description"><?php _e( 'If checked, the client will receive the full content of messages by email instead of a notification with a prompt to log into their client portal. This is less secure.', 'client-power-tools' ); ?></p>
        </label>
      </fieldset>

    <?php

  echo ob_get_clean();

}


// Knowledge Base
function cpt_knowledge_base_settings_init() {

  add_settings_section(
    'cpt-knowledge-base-settings',
    __( 'Knowledge Base', 'client-power-tools' ),
    __NAMESPACE__ . '\cpt_knowledge_base_section',
    'cpt-settings',
  );

  // Enable Messaging
  add_settings_field(
    'cpt_module_knowledge_base',
    __( 'Enable', 'client-power-tools' ),
    __NAMESPACE__ . '\cpt_module_knowledge_base',
    'cpt-settings',
    'cpt-knowledge-base-settings',
  );

  register_setting( 'cpt-settings', 'cpt_module_knowledge_base', 'absint' );

  if ( get_option( 'cpt_module_knowledge_base' ) ) {

    add_settings_field(
      'cpt_knowledge_base_page_selection',
      '<label for="cpt_knowledge_base_page_selection">' . __( 'Knowledge Base Page', 'client-power-tools' ) . '</label>',
      __NAMESPACE__ . '\cpt_knowledge_base_page_selection',
      'cpt-settings',
      'cpt-knowledge-base-settings',
    );

    register_setting( 'cpt-settings', 'cpt_knowledge_base_page_selection' );

  }

}

add_action( 'admin_init', __NAMESPACE__ . '\cpt_knowledge_base_settings_init' );


function cpt_knowledge_base_section() {
  _e( 'The knowledge base is a restricted page you can use to share information and resources with your clients.', 'client-power-tools' );
}


function cpt_module_knowledge_base() {

  ob_start();

    ?>

      <fieldset>
        <label for="cpt_module_knowledge_base">
          <input name="cpt_module_knowledge_base" id="cpt_module_knowledge_base" type="checkbox" value="1" <?php checked( get_option( 'cpt_module_knowledge_base' ) ); ?>>
          <?php _e( 'Enable knowledge base.', 'client-power-tools' ); ?>
        </label>
      </fieldset>

    <?php

  echo ob_get_clean();

}


function cpt_knowledge_base_page_selection() {

  $args = [
    'post_type'       => 'page',
    'posts_per_page'  => -1,
    'post_status'     => 'publish',
  ];

  $page_query = new \WP_Query( $args );

  if ( $page_query->have_posts() ) :

    echo '<select name="cpt_knowledge_base_page_selection">';

    $selected = get_option( 'cpt_knowledge_base_page_selection' );

    while ( $page_query->have_posts() ) : $page_query->the_post();

      $page_id = get_the_ID();

      echo '<option value="' . $page_id . '"';

      if ( $selected == $page_id ) {
        echo ' selected';
      }

      echo '>' . get_the_title() . '</option>';

    endwhile;

    echo '</select>';

    echo '<p class="description">' . __( 'This page and its child pages will be restricted to clients.', 'client-power-tools' ) . ' <a href="' . Common\cpt_get_knowledge_base_url() . '" target="_blank">' . __( 'Visit the knowledge base.', 'client-power-tools' ) . '</a></p>';

  else :

    echo '<p>Sorry, you don\'t have any published pages.</p>';

  endif;

}
