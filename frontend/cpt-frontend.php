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
        <button class="cpt-dismiss-button cpt-login-modal-dismiss"></button>
        <?php if (!is_user_logged_in()): ?>
          <h2><?php _e('Client Login', 'client-power-tools'); ?></h2>
          <div id="cpt-login-messages"></div>
          <form id="cpt-login-form" name="cpt-login-form" action="<?php echo get_permalink(); ?>" method="post">
            <?php wp_nonce_field('cpt-login', 'cpt-login-nonce'); ?>
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
              <input id="cpt-login-submit-button" class="button button-primary wp-element-button" name="cpt-login-submit-button" type="submit" value="<?php _e('Send Code', 'client-power-tools'); ?>">
            </p>
          </form>
        <?php else: ?>
          <h2><?php _e('Log Out?', 'client-power-tools'); ?></h2>
          <p><a id="cpt-logout" class="button wp-element-button" href="<?php echo wp_logout_url(home_url()); ?>" rel="nofollow"><?php _e('Log Out', 'client-power-tools'); ?></a></p>
        <?php endif; ?>
      </div>
    </div>
    <div class="cpt-modal-screen"></div>
  <?php
}


add_filter('the_title', __NAMESPACE__ . '\cpt_client_dashboard_page_titles', 10, 2);
function cpt_client_dashboard_page_titles($title, $post_id) {
  if (
    !is_main_query() 
    || !in_the_loop() 
    || !Common\cpt_is_client_dashboard()
  ) return $title;

  return get_post(get_option('cpt_client_dashboard_page_selection'))->post_title;
}

function cpt_get_the_title() {
  remove_filter('the_title', __NAMESPACE__ . '\cpt_client_dashboard_page_titles', 10, 2);
  if (Common\cpt_is_client_dashboard('dashboard')) return __('Home', 'client-power-tools');
  if (Common\cpt_is_client_dashboard('messages')) return __('Messages', 'client-power-tools');
  if (Common\cpt_is_client_dashboard('projects')) return Common\cpt_get_projects_label('plural');
  return get_the_title();
}


// @deprecated
function cpt_is_cpt() {
  trigger_error('Function ' . __FUNCTION__ . ' is deprecated as of 1.7.6 and will be removed soon.', E_USER_DEPRECATED);
  return Common\cpt_is_client_dashboard();
}
