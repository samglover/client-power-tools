<?php
/**
 * Shortcodes for Client Power Tools:
 * - status-update-request-button
 * - client-dashboard
 *
 * @file       shortcodes.php
 * @package    Client_Power_Tools
 * @subpackage Core
 * @since      1.5.2
 */

namespace Client_Power_Tools\Core;

use Client_Power_Tools\Core\Common;
use Client_Power_Tools\Core\Frontend;

add_shortcode( 'status-update-request-button', __NAMESPACE__ . '\status_update_request_button_shortcode' );
/**
 * Shortcode for inserting the status update request button on the client dashboard.
 *
 * @since 1.5.2
 *
 * @return string Status update request button HTML.
 */
function status_update_request_button_shortcode() {
	if ( ! Common\cpt_is_client() ) {
		return;
	}
	ob_start();
		Common\cpt_status_update_request_button( get_current_user_id() );
	return ob_get_clean();
}


add_shortcode( 'client-dashboard', __NAMESPACE__ . '\client_dashboard_shortcode' );
/**
 * Shortcode for inserting the client dashboard on the client dashboard page. Mostly intended for page builders that interfere with the output.
 *
 * @since 1.9.2
 * @return string Client dashboard HTML.
 */
function client_dashboard_shortcode() {
	return Frontend\cpt_get_client_dashboard();
}
