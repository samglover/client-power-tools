<?php

namespace Client_Power_Tools\Core\Frontend;
use Client_Power_Tools\Core\Common;

/**
 * Adds a body class for overriding CPT styles.
 */
add_filter('body_class', function($classes) {
  return array_merge($classes, ['customize-cpt']);
});


add_filter('the_content', __NAMESPACE__ . '\cpt_add_nav_to_addl_pages');
function cpt_add_nav_to_addl_pages($content) {
  $addl_pages_array = explode(',', get_option('cpt_client_dashboard_addl_pages'));
  if (!is_main_query() || !in_the_loop() || !$addl_pages_array) return $content;

  $protected = false;
  $ancestors = get_post_ancestors(get_the_ID());

  foreach ($addl_pages_array as $page_id) {
    $page_id = trim($page_id);
    if (in_array(get_the_ID(), $addl_pages_array)) $protected = true;
    foreach ($ancestors as $ancestor) {
      if (in_array($ancestor, $addl_pages_array)) $protected = true;
    }
  }
  if (!$protected) return $content;
  
  if (!is_user_logged_in()) {
    return sprintf(__('%1$sPlease %2$slog in%3$s to view this page.%4$s', 'client-power-tools'),
      /* %1$s */ '<p>',
      /* %2$s */ '<a class="cpt-login-link" href="#">',
      /* %3$s */ '</a>',
      /* %4$s */ '</p>'
    );
  }
  if (!Common\cpt_is_client()) return '<p>' . __('Sorry, you don\'t have permission to view this page because your user account is missing the "Client" role.', 'client-power-tools') . '</p>';
  return cpt_nav() . $content;
}


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
  if (
    Common\cpt_is_client_dashboard() || 
    Common\cpt_is_client_dashboard('messages') || 
    Common\cpt_is_knowledge_base()
  ) {
    return true;
  } else {
    return false;
  }
}
