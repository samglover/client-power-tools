<?php
/**
 * Admin messages page
 *
 * @file       cpt-admin-messages.php
 * @package    Client_Power_Tools
 * @subpackage Core\Admin
 * @since      1.0.0
 */

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Includes;

/**
 * Outputs the messages page
 */
function cpt_admin_messages() {
	?>
		<div 
			id="cpt-admin" 
			class="wrap"
		>
			<div id="cpt-admin-header">
			<img class="cpt-logo" src="<?php echo esc_url( CLIENT_POWER_TOOLS_DIR_URL ); ?>assets/images/cpt-logo.png" alt="Client Power Tools" />
				<div id="cpt-admin-page-title">
					<h1 id="cpt-page-title">
						<?php esc_html_e( 'Messages', 'client-power-tools' ); ?>
					</h1>
					<p id="cpt-subtitle">
						<?php esc_html_e( 'Client Power Tools', 'client-power-tools' ); ?>
					</p>
				</div>
			</div>
			<hr class="wp-header-end">
			<?php cpt_get_message_list(); ?>
		</div>
	<?php
}

/**
 * Outputs the list of messages.
 */
function cpt_get_message_list() {
	$message_list = new Message_List_Table();
	$message_list->prepare_items();

	?>
		<form id="cpt-message-list" method="get">
			<?php $message_list->display(); ?>
		</form>
	<?php
}

/**
 * Gets the page number of the message list on which a given message appears.
 *
 * @param int $clients_user_id Client's user ID.
 * @param int $message_id ID of the message to check.
 * @return int Page number.
 */
function cpt_get_message_pagenum( $clients_user_id, $message_id ) {
	if ( ! $clients_user_id || ! $message_id ) {
		return;
	}

	$cpt_messages = get_posts(
		array(
			'fields'      => 'ids',
			'meta_key'    => 'cpt_clients_user_id',
			'meta_value'  => $clients_user_id,
			'numberposts' => -1,
			'post_type'   => 'cpt_message',
		)
	);

	if ( ! $cpt_messages ) {
		return;
	}

	$posts_per_page = get_option( 'posts_per_page' );
	$pages          = array_chunk( $cpt_messages, $posts_per_page );

	foreach ( $pages as $pagenum => $page ) {
		if ( in_array( $message_id, $page, true ) ) {
			$page_number = $pagenum + 1;
			continue;
		}
	}

	return $page_number;
}
