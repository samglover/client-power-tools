<?php

namespace Client_Power_Tools\Core\Admin;

function cpt_settings() {

  if ( ! current_user_can( 'cpt-manage-settings' ) ) {
    wp_die(
      '<p>' . __( 'Sorry, you are not allowed to access this page.' ) . '</p>',
      403
    );
  }

  ob_start();

    ?>

      <div id="cpt-admin" class="wrap">

        <div id="cpt-admin-header">
          <img src="<?php echo CLIENT_POWER_TOOLS_DIR_URL; ?>admin/images/cpt-logo.svg" height="auto" width="100%" />
          <div id="cpt-admin-page-title">
            <h1 id="cpt-page-title">Settings</h1>
            <p id="cpt-subtitle">Client Power Tools</p>
          </div>
        </div>
        <hr class="wp-header-end">

        <?php if ( isset( $_REQUEST[ 'settings-updated' ] ) && $_REQUEST[ 'settings-updated' ] == true ) { ?>

          <div class="cpt-notice notice notice-success is-dismissible">
            <p><?php _e( 'Settings updated!' ); ?></p>
          </div>

        <?php } ?>

        <form method="POST" action="options.php">
          <?php settings_fields( 'cpt-settings' ); ?>
          <?php do_settings_sections( 'cpt-settings' ); ?>
          <?php submit_button( 'Save Settings' ); ?>
        </form>

        <!-- Insert selector for frontend client dashboard page. -->

      </div>

    <?php

  echo ob_get_clean();

}


// Client Dashboard
function cpt_client_dashboard_settings_init() {

  add_settings_section(
    'cpt-client-dashboard-settings',
    'Client Dashboard',
    __NAMESPACE__ . '\cpt_client_dashboard_section',
    'cpt-settings',
  );

  add_settings_field(
    'cpt_client_dashboard_page_selection',
    '<label for="cpt_client_dashboard_page_selection">Select Page</label>',
    __NAMESPACE__ . '\cpt_client_dashboard_page_selection',
    'cpt-settings',
    'cpt-client-dashboard-settings',
  );

  register_setting( 'cpt-settings', 'cpt_client_dashboard_page_selection' );

}

add_action( 'admin_init', __NAMESPACE__ . '\cpt_client_dashboard_settings_init' );


function cpt_client_dashboard_section() {
  echo '<p>' . __( 'When clients visit the page selected below, they will be prompted to log in and then shown their client dashboard.' ) . '</p>';
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

  else :

    echo '<p>Sorry, you don\'t have any published pages.</p>';

  endif;

}

// Client Managers
function cpt_client_managers_settings_init() {

  add_settings_section(
    'cpt-client-managers-settings',
    'Client Managers',
    __NAMESPACE__ . '\cpt_client_managers_section',
    'cpt-settings',
  );

  add_settings_field(
    'cpt_client_managers',
    '<label for="cpt_client_managers">Client Managers</label>',
    __NAMESPACE__ . '\cpt_client_managers',
    'cpt-settings',
    'cpt-client-managers-settings',
  );
  // The client managers list isn't an actual setting, so there's no need to use register_setting.

  add_settings_field(
    'cpt_default_client_manager',
    '<label for="cpt_default_client_manager">Default Client Manager</label>',
    __NAMESPACE__ . '\cpt_default_client_manager',
    'cpt-settings',
    'cpt-client-managers-settings',
  );

  register_setting( 'cpt-settings', 'cpt_default_client_manager' );

}

add_action( 'admin_init', __NAMESPACE__ . '\cpt_client_managers_settings_init' );

function cpt_client_managers_section() {
  echo '<p>' . __( 'Client managers can be assigned to individual clients.' ) . '</p>';
}


function cpt_client_managers() {

  $args = [
    'role__in'  => [ 'administrator', 'cpt-client-manager' ],
    'orderby'   => 'display_name',
    'order'     => 'ASC',
  ];

  $client_managers_query  = new \WP_USER_QUERY( $args );
  $client_managers        = $client_managers_query->get_results();

  if ( ! empty( $client_managers ) ) {

    echo '<ul id="client-managers">';

      foreach ( $client_managers as $client_manager ) {

        echo '<li>';

          echo $client_manager->display_name;

          if ( in_array( 'administrator', $client_manager->roles ) ) {
            echo ' <span style="color: silver;">(admin)</span>';
          }

        echo '</li>';

      }

    echo '</ul>';

    echo '<button class="button cpt-click-to-expand">' . __( 'Add a Client Manager' ) . '</button>';

    ob_start();

      ?>

        <div class="cpt-this-expands">

          <h4>Add a Client Manager</h4>

          <p><?php _e( 'Assign the client manager role to a new or existing user. Add the first and last name as you want clients to see them.' ); ?></p>

          <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">

            <?php wp_nonce_field( 'cpt_new_client_manager_added', 'cpt_new_client_manager_nonce' ); ?>
            <input name="action" value="cpt_new_client_manager_added" type="hidden">

            <table class="form-table" role="presentation">
              <tbody>
                <tr>
                  <th scope="row">
                    <label for="first_name">First Name<br /><small>(required)</small></label>
                  </th>
                  <td>
                    <input name="first_name" id="first_name" class="regular-text" type="text" data-required="true">
                  </td>
                </tr>
                <tr>
                  <th scope="row">
                    <label for="last_name">Last Name<br /><small>(required)</small></label>
                  </th>
                  <td>
                    <input name="last_name" id="last_name" class="regular-text" type="text" data-required="true">
                  </td>
                </tr>
                <tr>
                  <th scope="row">
                    <label for="email">Email Address<br /><small>(required)</small></label>
                  </th>
                  <td>
                    <input name="email" id="email" class="regular-text" type="text" data-required="true" autocapitalize="none" autocorrect="off">
                  </td>
              </tbody>
            </table>

            <p class="submit">
              <input name="submit" id="submit" class="button button-primary" type="submit" value="Add Client Manager">
            </p>

          </form>

        </div>

        <p></p>
        <button class="button cpt-click-to-expand">Click to Expand</button>
        <div class="cpt-this-expands"><h3>Hello world!</h3></div>

      <?php

    echo ob_get_clean();

  }

}


function cpt_default_client_manager() {
  echo cpt_get_client_manager_select( 'cpt_default_client_manager', get_option( 'cpt_default_client_manager' ) );
}


// Client Statuses
function cpt_client_status_settings_init() {

  add_settings_section(
    'cpt-client-status-settings',
    'Client Statuses',
    __NAMESPACE__ . '\cpt_client_status_section',
    'cpt-settings',
  );

  add_settings_field(
    'cpt_client_statuses',
    '<label for="cpt_client_statuses">Statuses</label>',
    __NAMESPACE__ . '\cpt_client_statuses',
    'cpt-settings',
    'cpt-client-status-settings',
  );

  register_setting( 'cpt-settings', 'cpt_client_statuses' );

  add_settings_field(
    'cpt_default_client_status',
    '<label for="cpt_default_client_status">Default Status</label>',
    __NAMESPACE__ . '\cpt_default_client_status',
    'cpt-settings',
    'cpt-client-status-settings',
  );

  register_setting( 'cpt-settings', 'cpt_default_client_status' );

}

add_action( 'admin_init', __NAMESPACE__ . '\cpt_client_status_settings_init' );


function cpt_client_status_section() {}


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
  echo '<p class="description">' . __( 'Enter one status per line.' ) . '</p>';

}


function cpt_default_client_status() {
  echo cpt_get_client_statuses_select( 'cpt_default_client_status' );
}


// Status Update Request Button settings
function cpt_status_update_request_button_settings_init() {

  add_settings_section(
    'cpt_status_update_request_button_settings',
    'Status Update Request Button',
    __NAMESPACE__ . '\cpt_status_update_request_button_section',
    'cpt-settings',
  );

  // Show Status Update Request Button
  add_settings_field(
    'cpt_show_status_update_req_button',
    'Show/Hide',
    __NAMESPACE__ . '\cpt_show_status_update_req_button',
    'cpt-settings',
    'cpt_status_update_request_button_settings',
  );

  register_setting( 'cpt-settings', 'cpt_show_status_update_req_button', 'absint' );

  // Status Update Request Frequency
  add_settings_field(
    'cpt_status_update_req_freq',
    '<label for="cpt_status_update_req_freq">Status Update Request Frequency<br /><small>(required)</small></label>',
    __NAMESPACE__ . '\cpt_status_update_req_freq',
    'cpt-settings',
    'cpt_status_update_request_button_settings',
  );

  register_setting( 'cpt-settings', 'cpt_status_update_req_freq', 'absint' );

  // Status Update Request Notification Email
  add_settings_field(
    'cpt_status_update_req_notice_email',
    '<label for="cpt_status_update_req_notice_email">Additional Status Update Request Notification Email<br /><small>(optional)</small></label>',
    __NAMESPACE__ . '\cpt_status_update_req_notice_email',
    'cpt-settings',
    'cpt_status_update_request_button_settings',
  );

  register_setting( 'cpt-settings', 'cpt_status_update_req_notice_email', 'sanitize_email' );

}

add_action( 'admin_init', __NAMESPACE__ . '\cpt_status_update_request_button_settings_init' );


function cpt_status_update_request_button_section() {}

function cpt_show_status_update_req_button() {

  $show_button = get_option( 'cpt_show_status_update_req_button', true );

  ob_start();

    ?>

      <fieldset>
        <label for="cpt_show_status_update_req_button">
          <input name="cpt_show_status_update_req_button" id="cpt_show_status_update_req_button" type="checkbox" value="1" <?php checked( $show_button ); ?>>
          Show button?
          <p class="description"><?php _e( 'Uncheck this box to hide the Status Update Request Button on the client dashboard.' ); ?></p>
        </label>
      </fieldset>

    <?php

  echo ob_get_clean();

}

function cpt_status_update_req_freq() {
  echo '<input name="cpt_status_update_req_freq" class="small-text" type="number" required aria-required="true" value="' . get_option( 'cpt_status_update_req_freq' ) . '"> days';
  echo '<p class="description">' . __( 'Enter how frequently you want to allow your clients to request a status update using the <strong>Request Status Update</strong> button on their client dashboard.' ) . '</p>';
}

function cpt_status_update_req_notice_email() {
  echo '<input name="cpt_status_update_req_notice_email" class="regular-text" type="email" value="' . get_option( 'cpt_status_update_req_notice_email' ) . '">';
  echo '<p class="description">' . __( 'This address will be CC\'d when a client requests a status update.' ) . '</p>';
}


// Client Messaging Settings
function cpt_client_messaging_settings_init() {

  add_settings_section(
    'cpt_client_messaging_settings',
    'Messages',
    __NAMESPACE__ . '\cpt_client_messaging_section',
    'cpt-settings',
  );

  // Send Message Content
  add_settings_field(
    'cpt_send_message_content',
    'Email Notification Content',
    __NAMESPACE__ . '\cpt_send_message_content',
    'cpt-settings',
    'cpt_client_messaging_settings',
  );

  register_setting( 'cpt-settings', 'cpt_send_message_content', 'absint' );

}

add_action( 'admin_init', __NAMESPACE__ . '\cpt_client_messaging_settings_init' );


function cpt_client_messaging_section() {}

function cpt_send_message_content() {

  $send_message_content = get_option( 'cpt_send_message_content' );

  ob_start();

    ?>

      <fieldset>
        <label for="cpt_send_message_content">
          <input name="cpt_send_message_content" id="cpt_send_message_content" type="checkbox" value="1" <?php checked( $send_message_content ); ?>>
          Send message content
          <p class="description"><?php _e( 'If checked, the client will receive the full content of messages by email instead of a notification with a prompt to log into their client portal. This is less secure.' ); ?></p>
        </label>
      </fieldset>

    <?php

  echo ob_get_clean();

}


// New Client Email Settings
function cpt_new_client_email_settings_init() {

  add_settings_section(
    'cpt-new-client-email-settings',
    'Client Account Activation Email',
    __NAMESPACE__ . '\cpt_new_client_email_section',
    'cpt-settings',
  );

  // Subject Line
  add_settings_field(
    'cpt_new_client_email_subject_line',
    '<label for="cpt_new_client_email_subject_line">Subject Line<br /><small>(required)</small></label>',
    __NAMESPACE__ . '\cpt_new_client_email_subject_line',
    'cpt-settings',
    'cpt-new-client-email-settings',
  );

  register_setting( 'cpt-settings', 'cpt_new_client_email_subject_line', 'sanitize_text_field' );

  // Message Body
  add_settings_field(
    'cpt_new_client_email_message_body',
    '<label for="cpt_new_client_email_message_body">Message Body<br /><small>(optional)</small></label>',
    __NAMESPACE__ . '\cpt_new_client_email_message_body',
    'cpt-settings',
    'cpt-new-client-email-settings',
  );

  register_setting( 'cpt-settings', 'cpt_new_client_email_message_body', 'sanitize_textarea_field' );

}

add_action( 'admin_init', __NAMESPACE__ . '\cpt_new_client_email_settings_init' );


function cpt_new_client_email_section() {
  echo '<p>' . __( 'When you add a new client, they will receive an email notification from their client manager with an account activation link. If you like, you can customize the subject line or add a message to the body of the email.' ) . '</p>';
}

function cpt_new_client_email_subject_line() {
  echo '<input name="cpt_new_client_email_subject_line" class="large-text" type="text" required aria-required="true" value="' . get_option( 'cpt_new_client_email_subject_line' ) . '">';
}

function cpt_new_client_email_message_body() {
  echo '<textarea name="cpt_new_client_email_message_body" class="large-text" rows="5">' . get_option( 'cpt_new_client_email_message_body' ) . '</textarea>';
}
