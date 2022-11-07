<?php

namespace Client_Power_Tools\Core;
use Client_Power_Tools\Core\Common;
use Client_Power_Tools\Core\Frontend;

function status_update_request_button_shortcode() {
  if (!Common\cpt_is_client()) return;
  ob_start();
    Common\cpt_status_update_request_button(get_current_user_id());
  return ob_get_clean();
}

add_shortcode('status-update-request-button', __NAMESPACE__ . '\status_update_request_button_shortcode');
