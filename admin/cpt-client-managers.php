<?php
/**
 * Admin client managers page
 *
 * @file       cpt-client-managers.php
 * @package    Client_Power_Tools
 * @subpackage Core\Admin
 * @since      1.2.1
 */

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

/**
 * Outputs the client managers page
 */
function cpt_client_managers() {
	if ( ! current_user_can( 'cpt_manage_team' ) ) {
		wp_die(
			'<p>' . esc_html__( 'Sorry, you are not allowed to access this page.' ) . '</p>',
			403
		);
	}

	if ( isset( $_REQUEST['cpt_action'] ) && isset( $_REQUEST['user_id'] ) ) {
		$action_user_id = intval( wp_unslash( $_REQUEST['user_id'] ) );
		$action         = intval( wp_unslash( $_REQUEST['cpt_action'] ) );
		switch ( $action ) {
			case 'cpt_remove_client_manager':
				cpt_remove_client_manager( $action_user_id );
				break;
		}
	}

	Common\cpt_get_notices(
		array(
			'cpt_add_manager_result',
			'cpt_remove_manager_result',
		)
	);

	?>
	<div id="cpt-admin" class="wrap">
		<div id="cpt-admin-header">
		<img class="cpt-logo" src="<?php echo esc_url( CLIENT_POWER_TOOLS_DIR_URL ); ?>assets/images/cpt-logo.png" alt="Client Power Tools" />
		<div id="cpt-admin-page-title">
			<h1 id="cpt-page-title">Client Managers</h1>
			<p id="cpt-subtitle">Client Power Tools</p>
		</div>
		</div>
		<hr class="wp-header-end">

		<?php if ( current_user_can( 'cpt_manage_team' ) ) { ?>
		<button class="button wp-element-button cpt-click-to-expand">
			<?php esc_html_e( 'Add a Client Manager' ); ?>
		</button>
		<div class="cpt-this-expands">
			<?php cpt_add_client_manager_form(); ?>
		</div>
		<?php } ?>

		<?php cpt_client_manager_list(); ?>
	</div>
	<?php
}

/**
 * Add client manager form
 */
function cpt_add_client_manager_form() {
	?>
	<h3><?php esc_html_e( 'Add a Client Manager' ); ?></h3>
	<p>
		<?php esc_html_e( 'Assign the client manager role to a new or existing user. Add the first and last name as you want clients to see them.' ); ?>
	</p>

	<form 
		action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" 
		method="POST"
	>
		<?php wp_nonce_field( 'cpt_new_client_manager_added', 'cpt_new_client_manager_nonce' ); ?>
		<input 
			name="action" 
			value="cpt_new_client_manager_added" 
			type="hidden"
		>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="first_name">
							First Name<br />
							<small class="required">(<?php esc_html_e( 'required', 'client-power-tools' ); ?>)</small>
						</label>
					</th>
					<td>
						<input 
							name="first_name" 
							id="first_name" 
							class="regular-text" 
							type="text" 
							data-required="true"
						>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="last_name">
							Last Name<br />
							<small class="required">(<?php esc_html_e( 'required', 'client-power-tools' ); ?>)</small>
						</label>
					</th>
					<td>
						<input 
							name="last_name" 
							id="last_name" 
							class="regular-text" 
							type="text" 
							data-required="true"
						>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="email">
							Email Address<br />
							<small class="required">(<?php esc_html_e( 'required', 'client-power-tools' ); ?>)</small>
						</label>
					</th>
					<td>
						<input 
							name="email" 
							id="email" 
							class="regular-text" 
							type="text" 
							data-required="true" 
							autocapitalize="none" 
							autocorrect="off"
						>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input 
				name="submit" 
				id="submit" 
				class="button button-primary" 
				type="submit" 
				value="Add Client Manager"
			>
		</p>
	</form>
	<?php
}

add_action( 'admin_post_cpt_new_client_manager_added', __NAMESPACE__ . '\cpt_process_new_client_manager' );
/**
 * Process a new client manager
 */
function cpt_process_new_client_manager() {
	if (
		! isset( $_POST['cpt_new_client_manager_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpt_new_client_manager_nonce'] ) ), 'cpt_new_client_manager_added' )
	) {
		exit( esc_html__( 'Invalid nonce.', 'client-power-tools' ) );
	}

	$existing_id = email_exists( sanitize_email( wp_unslash( $_POST['email'] ) ) );
	$user_data   = Common\cpt_get_sanitized_userdata_from_post();

	if ( ! $existing_id ) {
		$user_data          = array_merge(
			$user_data,
			array(
				'user_login'           => $user_data['user_email'],
				'user_pass'            => null,
				'role'                 => 'cpt-client-manager',
				'show_admin_bar_front' => 'false',
			)
		);
		$new_client_manager = wp_insert_user( $user_data );
	} else {
		$user_data['ID']    = $existing_id;
		$new_client_manager = wp_update_user( $userdata );
		$user               = new \WP_User( $new_client_manager );
		$user->add_role( 'cpt-client-manager' );
	}

	if ( is_wp_error( $new_client_manager ) ) {
		$result = sprintf(
			// Translators: %s is the error message.
			__( 'Client manager could not be added. Error message: %s', 'client-power-tools' ),
			$new_client_manager->get_error_message()
		);
	} else {
		if ( ! $existing_id ) {
			cpt_new_client_manager_email( $new_client_manager );
		}
		$result = __( 'Client manager added.', 'client-manager-added' );
	}

	set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
	wp_safe_redirect( wp_get_referer() );
	exit;
}

/**
 * Sends an email notification to new client manager.
 *
 * @param int $user_id Client manager's user ID.
 */
function cpt_new_client_manager_email( $user_id ) {
	if ( ! $user_id ) {
		return;
	}

	$user           = get_userdata( $user_id );
	$current_user   = wp_get_current_user();
	$from_name      = Common\cpt_get_display_name( $user->ID );
	$from_email     = $user->user_email;
	$headers[]      = 'Content-Type: text/html; charset=UTF-8';
	$headers[]      = 'From: ' . Common\cpt_get_display_name( $current_user->ID ) . ' <' . $current_user->user_email . '>';
	$to             = $user->user_email;
	$subject        = __( 'Your client manager account has been created. Please set your password.', 'client-power-tools' );
	$activation_key = get_password_reset_key( $user );
	$activation_url = home_url() . '?cpt_login=setpw&key=' . $activation_key . '&login=' . rawurlencode( $user->user_login );
	$card_content   = '<p>' . __( 'Your username is your email address:', 'client-power-tools' ) . '</p>';
	$card_content  .= '<p><strong>' . $user->user_email . '</strong></p>';
	$card_content  .= '<p>' . __( 'You will need to activate your account and set a password in order to access your clients.', 'client-power-tools' ) . '</p>';

	$message = Common\cpt_get_email_card( $subject, $card_content, __( 'Activate Your Account', 'client-power-tools' ), $activation_url );

	wp_mail( $to, $subject, $message, $headers );
}

/**
 * Outputs the list of client managers.
 */
function cpt_client_manager_list() {
	$client_manager_list = new Client_Manager_List_Table();
	$client_manager_list->prepare_items();
	?>
	<form id="client-manager-list" method="GET">
		<?php $client_manager_list->display(); ?>
	</form>
	<?php
}


/**
 * Get clients assigned to a client manager.
 *
 * @param int $user_id Client manager's user ID.
 * @return array|void Array of client user objects.
 */
function cpt_get_managers_clients( $user_id ) {
	if ( ! $user_id ) {
		return;
	}

	$args = array(
		'meta_key'   => 'cpt_client_manager',
		'meta_value' => $user_id,
		'role'       => 'cpt-client',
		'orderby'    => 'display_name',
		'order'      => 'ASC',
	);

	$client_query = new \WP_User_Query( $args );
	$clients      = $client_query->get_results();

	if ( $clients ) {
		foreach ( $clients as $client ) {
			$client_data[] = Common\cpt_get_client_data( $client->ID );
		}

		return $client_data;
	} else {
		return;
	}
}

/**
 * Removes the cpt-client-manager role from a user (without deleting the user).
 *
 * @param int $user_id Client manager's user ID.
 */
function cpt_remove_client_manager( $user_id ) {
	if ( ! $user_id ) {
		return;
	}

	$user = new \WP_User( $user_id );
	$user->remove_role( 'cpt-client-manager' );

	$result = __( 'Client manager removed.', 'client-power-tools' );

	set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
}
