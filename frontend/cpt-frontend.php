<?php

namespace Client_Power_Tools\Core\Frontend;
use Client_Power_Tools\Core\Common;

/**
 * Adds a body class for overriding CPT styles.
 */
add_filter('body_class', function($classes) {
  $body_classes = ['customize-cpt'];
  if (Common\cpt_is_client_dashboard()) $body_classes[] = 'client-dashboard';
  if (Common\cpt_is_client_dashboard('messages')) $body_classes[] = 'client-dashboard-messages';
  if (Common\cpt_is_client_dashboard('projects')) $body_classes[] = 'client-dashboard-projects';
  if (Common\cpt_is_client_dashboard('knowledge base')) $body_classes[] = 'client-dashboard-knowledge-base';
  return array_merge($classes, $body_classes);
});


/**
 * Loads the login modal in the footer.
 */
add_action('wp_footer', __NAMESPACE__ . '\cpt_login');
function cpt_login() {
  ?>
    <div id="cpt-login" class="cpt-modal">
      <div class="cpt-modal-card">
        <button class="cpt-dismiss-button cpt-modal-dismiss-button">
          <?php echo file_get_contents(CLIENT_POWER_TOOLS_DIR_PATH . 'assets/images/close.svg'); ?>
        </button>
        <?php if (!is_user_logged_in()): ?>
          <h2><?php _e('Client Login', 'client-power-tools'); ?></h2>
          <div id="cpt-login-messages"></div>
          <form id="cpt-login-form" name="cpt-login-form" action="<?php echo get_permalink(); ?>" method="post">
            <p id="cpt-login-email">
              <label for="cpt-login-email-field">Email Address</label>
              <input id="cpt-login-email-field" class="input" name="cpt-login-email-field" type="text" autocomplete="username" value="" size="20">
            </p>
            <p id="cpt-login-code" data-button-text="<?php _e('Send Code', 'client-power-tools'); ?>">
              <label for="cpt-login-code-field">Login Code</label>
              <input id="cpt-login-code-field" class="input" name="cpt-login-code-field" type="password" autocomplete="one-time-code" value="" size="20">
            </p>
            <p id="cpt-login-password" data-button-text="<?php _e('Log In', 'client-power-tools'); ?>">
              <label for="cpt-login-password-field">Password</label>
              <input id="cpt-login-password-field" class="input" name="cpt-login-password-field" type="password" autocomplete="current-password" value="" size="20">
            </p>
            <p id="cpt-login-type-links">
              <a id="cpt-login-code-link" href="#"><?php _e('Get a login code by email.', 'client-power-tools'); ?></a>
              <a id="cpt-password-link" href="#"><?php _e('Use a password instead.', 'client-power-tools'); ?></a>
            </p>
            <p id="cpt-login-submit">
              <input id="cpt-login-submit-button" class="button button-primary" name="cpt-login-submit-button" type="submit" value="<?php _e('Send Code', 'client-power-tools'); ?>">
            </p>
          </form>
        <?php else: ?>
          <h2><?php _e('Log Out?', 'client-power-tools'); ?></h2>
          <p><a id="cpt-logout" class="button" href="<?php echo wp_logout_url(home_url()); ?>" rel="nofollow"><?php _e('Log Out', 'client-power-tools'); ?></a></p>
        <?php endif; ?>
      </div>
    </div>
    <div class="cpt-modal-screen"></div>
  <?php
}

function cpt_is_cpt() {
  return Common\cpt_is_client_dashboard();
}
