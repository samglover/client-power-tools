<?php

namespace Client_Power_Tools\Core\Frontend;
use Client_Power_Tools\Core\Common;


/**
 * Adds a body class for overriding CPT styles.
 */
add_filter('body_class', function($classes) {
  return array_merge($classes, ['customize-cpt']);
});


function add_login_code_links_to_login_form() {
  ob_start();
    ?>
      <a id="cpt-login-code-link" href="#">Or, get a login code by email.</a>
      <a id="cpt-password-link" href="#">Use your password.</a>
    <?php
  return ob_get_clean();
}

add_filter('login_form_middle', __NAMESPACE__ . '\add_login_code_links_to_login_form');


/**
 * Loads the login modal in the footer. There are four possible results:
 *
 * 1. The login form.
 * 2. The password reset request form.
 * 3. The password change form. (Either after a password reset request, or for
 *    newly added clients.)
 * 4. The logout button.
 *
 * 1 & 2 are shown together, with a link to navigate between them. If 2 is called
 * for with cpt_login=resetpw in the URL, then it will be shown "on top" instead
 * of 1.
 *
 * If 3 or 4 are called for, either cpt_login=setpw in the URL for 3 or because
 * the user is logged in and clicks a login/logout link for 4, then they are
 * shown the password change form instead of the 1/2 modal.
 */
function cpt_login() {
  // Hides the modal by default, sets the login form to show, and sets the
  // resetpw form to hide.
  $modal_styles = ' style="display: none;"';
  $login_styles = '';
  $resetpw_styles = ' style="display: none;"';

  // Shows the login modal if there is a cpt_login parameter in the URL. Shows
  // the password reset panel of the login panel if the URL contains
  // cpt_login=resetpw.
  if (isset($_REQUEST['cpt_login'])) {
    $modal_styles = '';
    if ($_REQUEST['cpt_login'] == 'resetpw') {
      $login_styles = ' style="display: none;"';
      $resetpw_styles = '';
    }
  }

  ?>
    <div id="cpt-login" class="cpt-modal"<?php echo $modal_styles; ?>>
      <div class="cpt-modal-card">
        <button class="cpt-dismiss-button cpt-modal-dismiss-button">
          <?php echo file_get_contents(CLIENT_POWER_TOOLS_DIR_PATH . 'assets/images/close.svg'); ?>
        </button>
        <?php
          // First, checks to make sure the user is not logged in. Because if
          // they are, the logout button will be output instead.
          if (!is_user_logged_in()):

            // Outputs the password change form if the URL contains
            // cpt_login=setpw and a key and login email.
            if ($_REQUEST['cpt_login'] == 'setpw' && isset($_REQUEST['key']) && isset($_REQUEST['login'])) {
              $key = sanitize_text_field($_REQUEST['key']);
              $login = sanitize_user(urldecode($_REQUEST['login']));
              cpt_password_change_form($key, $login);

            // Otherwise, outputs the login/password reset form.
            } else {
              ?>
                <div id="cpt-login-modal-login" class="cpt-modal-inner"<?php echo $login_styles; ?>>
                  <h2><?php _e('Client Login', 'client-power-tools'); ?></h2>
                  <?php
                    cpt_error_messages();
                    cpt_success_messages();
                    wp_login_form([
                      'form_id'         => 'cpt-loginform',
                      'id_username'     => 'cpt-login-modal-username',
                      'id_password'     => 'cpt-login-modal-password',
                      'id_submit'       => 'cpt-login-modal-submit',
                      'label_username'  => __('Email Address', 'client-power-tools'),
                      'redirect'        => Common\cpt_get_client_dashboard_url(),
                      'remember'        => false,
                   ]);
                  ?>
                  <p><small><a id="cpt-login-go-to-resetpw" href="cpt-login-modal-resetpw" rel="nofollow"><?php _e('Forgot Your Password?', 'client-power-tools'); ?></a></small></p>
                </div>
                <div id="cpt-login-modal-resetpw" class="cpt-modal-inner"<?php echo $resetpw_styles; ?>>
                  <h2><?php _e('Forgot Your Password?', 'client-power-tools'); ?></h2>
                  <?php
                    cpt_error_messages();
                    cpt_success_messages();
                    // Outputs a password reset request form.
                    $cpt_lostpassword_url = remove_query_arg('action', wp_lostpassword_url());
                    $cpt_lostpassword_url = add_query_arg('action', 'cpt_lostpassword', $cpt_lostpassword_url);
                  ?>
                  <p><?php _e('Enter your email address and you will receive a link to reset your password.'); ?></p>
                  <form id="cpt-lostpasswordform" action="<?php echo $cpt_lostpassword_url; ?>" method="post">
                    <p>
                      <label for="lostpassword_user_login"><?php _e('Email'); ?></label>
                      <input id="lostpassword_user_login" class="input" type="text" name="user_login">
                    </p>
                    <p class="submit">
                      <input type="submit" name="submit" class="lostpassword-button button" value="<?php _e('Reset Password'); ?>"/>
                    </p>
                  </form>
                  <p><small><a id="cpt-login-go-to-login" href="cpt-login-modal-login" rel="nofollow"><?php _e('Back to Login'); ?></a></small></p>
                </div>
                <div id="cpt-login-code">
                  <h2><?php _e('Client Login', 'client-power-tools'); ?></h2>
                  <form id="cpt-check-login-code-form">
                    <p>
                      <label for="cpt-check-login-code">Enter Login Code</label>
                      <input id="cpt-check-login-code" class="input" type="text" maxlength="8">
                    </p>
                  </form>
                </div>
              <?php
            }
          else:
            // Outputs the logout button if the user is already logged in.
            ?>
              <div id="cpt-login-modal-already-logged-in" class="cpt-modal-inner">
                <h2><?php _e('Log Out?'); ?></h2>
                <p><a id="cpt-logout" class="button" href="<?php echo wp_logout_url(home_url()); ?>" rel="nofollow"><?php _e('Log Out'); ?></a></p>
              </div>
            <?php
          endif;
        ?>
      </div>
    </div>
    <div class="cpt-modal-screen"<?php echo $modal_styles; ?>></div>
  <?php
}

add_action('wp_footer', __NAMESPACE__ . '\cpt_login');


function cpt_error_messages() {
  if (!isset($_REQUEST['cpt_error'])) return;
  switch($_REQUEST['cpt_error']) {
    case 'login_failed':
      $message = __('Sorry, but the email address or password you entered didn\'t work. Please try again.', 'client-power-tools');
      break;
    case 'invalid_key':
      $message = __('Your password reset key is invalid. Please try again.', 'client-power-tools');
      break;
    default:
      $message = __('Something went wrong; that didn\'t work.', 'client-power-tools');
  }
  echo '<p class="cpt-error">' . $message . '</p>';
}

function cpt_success_messages() {
  if (!isset($_REQUEST['cpt_success'])) return;
  switch ($_REQUEST['cpt_success']) {
    case 'password_changed':
      $message = __('Password successfully changed.', 'client-power-tools') . '</p>';
      break;
    default:
      $message = __('Success!', 'client-power-tools');
  }
  echo '<p class="cpt-success">' . $message . '</p>';
}


function cpt_process_password_reset_request() {
  if ('POST' == $_SERVER['REQUEST_METHOD']) retrieve_password();
  $redirect_url = home_url();
  $redirect_url = add_query_arg('cpt_notice', 'rp_checkemail', $redirect_url);
  wp_safe_redirect($redirect_url);
  exit;
}

add_action('login_form_cpt_lostpassword', __NAMESPACE__ . '\cpt_process_password_reset_request');


/**
 * Alters the password reset email slightly, so that the URL points to the client
 * dashboard instead of wp-login.php.
 */
function cpt_password_reset_message($message, $key, $user_login, $user_data) {
  if (Common\cpt_is_client($user_data->ID)) {
    $site_name = get_bloginfo('name');
    $url = Common\cpt_get_client_dashboard_url() . '?cpt_login=setpw&key=' . $key . '&login=' . urlencode($user_login);

    $message = __('Someone has requested a password reset for the following account:') . "\r\n\r\n";
    $message .= 'User ID: ' . $user_data->ID . "\r\n\r\n";
    $message .= sprintf(__('Site Name: %s'), $site_name) . "\r\n\r\n";
    $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
    $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
    $message .= __('To set a new password, visit the following address:') . "\r\n\r\n";
    $message .= $url . "\r\n";
  }
  return $message;
}

add_filter('retrieve_password_message', __NAMESPACE__ . '\cpt_password_reset_message', 99, 4);


function cpt_password_change_form($key, $login) {
  if (!$key || !$login) return;
  ?>
    <div id="cpt-login-modal-setpw" class="cpt-modal-inner">
      <h2><?php _e('Set Your Password'); ?></h2>
      <?php
        if (isset($_REQUEST['cpt_error'])) {
          $error_val = sanitize_key($_REQUEST['cpt_error']);

          switch ($error_val) {
            case 'password_reset_empty':
              echo '<p class="cpt-error">' . __('You did not enter a new password. Please try again.') . '</p>';
              break;
            case 'password_mismatch':
              echo '<p class="cpt-error">' . __('Your passwords did not match. Please try again.') . '</p>';
              break;
            default:
              echo '<p class="cpt-error">' . __('Error: ') . $error_val . '</p>';
          }
        }
      ?>

      <form name="resetpassform" id="resetpassform" action="<?php echo site_url('wp-login.php?action=resetpass'); ?>" method="post" autocomplete="off">
        <input type="hidden" id="login" name="login" value="<?php echo $login; ?>" autocomplete="off" />
        <input type="hidden" id="key" name="key" value="<?php echo $key; ?>" />
        <p>
          <label for="pass1"><?php _e('New Password', 'personalize-login') ?></label>
          <input type="password" name="pass1" id="pass1" class="input" size="20" value="" autocomplete="off" />
        </p>
        <p>
          <label for="pass2"><?php _e('Repeat New Password', 'personalize-login') ?></label>
          <input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off" />
        </p>
        <p class="description"><?php echo wp_get_password_hint(); ?></p>
        <p class="resetpass-submit">
          <input type="submit" name="submit" id="resetpass-button" class="button" value="<?php _e('Set Password', 'personalize-login'); ?>" />
        </p>
      </form>
    </div>
  <?php
}


function cpt_process_password_change() {
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $key = sanitize_text_field($_REQUEST['key']);
    $login = sanitize_user($_REQUEST['login']);
    $user = check_password_reset_key($key, $login);

    $dashboard = Common\cpt_get_client_dashboard_url();

    if (!$user || is_wp_error($user)) {
      $redirect_url = add_query_arg('cpt_login', 'resetpw', $dashboard);
      $redirect_url = add_query_arg('cpt_error', 'invalid_key', $redirect_url);
      wp_redirect($redirect_url);
      exit;
    }

    if (isset($_POST['pass1'])) {
      $pass1 = $_POST['pass1'];
      $pass2 = $_POST['pass2'];

      // Sends back the password_reset_empty error code if the pass1 field is
      // blank.
      if (empty($pass1)) {
        $redirect_url = add_query_arg('cpt_login', 'setpw', $dashboard);
        $redirect_url = add_query_arg('key', $key, $redirect_url);
        $redirect_url = add_query_arg('login', urlencode($login), $redirect_url);
        $redirect_url = add_query_arg('cpt_error', 'password_reset_empty', $redirect_url);
        wp_redirect($redirect_url);
        exit;
      }

      // Sends back the password_mismatch error code if the password fields
      // don't match.
      if ($pass1 !== $pass2) {
        $redirect_url = add_query_arg('cpt_login', 'setpw', $dashboard);
        $redirect_url = add_query_arg('key', $key, $redirect_url);
        $redirect_url = add_query_arg('login', urlencode($login), $redirect_url);
        $redirect_url = add_query_arg('cpt_error', 'password_mismatch', $redirect_url);
        wp_redirect($redirect_url);
        exit;
      }

      // Resets the password.
      reset_password($user, $pass1);

      // Determines the $redirect destination based on the user's role.
      if (in_array('cpt-client-manager', $user->roles)) {
        $redirect_url = admin_url('admin.php?page=cpt');
      } else {
        $redirect_url = $dashboard;
      }

      // Redirect the user with the password_changed success code.
      wp_redirect(add_query_arg('cpt_success', 'password_changed', $redirect_url));
      exit;
    } else {
      echo "Invalid request.";
    }
  }
}

add_action('login_form_rp', __NAMESPACE__ . '\cpt_process_password_change');
add_action('login_form_resetpass', __NAMESPACE__ . '\cpt_process_password_change');


/**
* Handles modal notices. (There is currently only one, for password reset
* requests. But it can also handle errors.)
*/
function cpt_notices() {
  if (isset($_REQUEST['cpt_notice'])) {
    $notice_val = sanitize_key($_REQUEST['cpt_notice']);

    switch ($notice_val) {
      case 'rp_checkemail':
        $heading = '<h2>' . __('Please Check Your Email') . '</h2>';
        $notice = '<p>' . __('If you submitted a valid email address you will receive a link to reset your password. If you do not receive an email shortly, please check your spam folder or contact us for help.') . '</p>';
        break;
      default:
        $heading = '<h2>' . __('Sorry') . '</h2>';
        $notice = '<p>' . __('Something went wrong. The page you were looking for doesn\'t seem to exist.') . '</p>';
    }

    ?>
      <div id="cpt-notice" class="cpt-modal">
        <div class="cpt-modal-card">
          <button class="cpt-dismiss-button cpt-modal-dismiss-button">
            <?php echo file_get_contents(CLIENT_POWER_TOOLS_DIR_PATH . 'assets/images/close.svg'); ?>
          </button>
          <div class="cpt-modal-inner">
            <?php echo $heading; ?>
            <?php echo $notice;?>
          </div>
        </div>
      </div>
      <div class="cpt-modal-screen"></div>
    <?php
  }
}

add_action('wp_footer', __NAMESPACE__ . '\cpt_notices');
