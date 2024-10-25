<?php

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

function cpt_client_managers() {
	if ( ! current_user_can( 'cpt_manage_team' ) ) {
		wp_die(
			'<p>' . esc_html__( 'Sorry, you are not allowed to access this page.' ) . '</p>',
			403
		);
	}

	if ( isset( $_REQUEST['cpt_action'] ) && isset( $_REQUEST['user_id'] ) ) {
		$action_user_id = sanitize_key( intval( $_REQUEST['user_id'] ) );
		$action         = sanitize_key( $_REQUEST['cpt_action'] );
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
		<table 
			class="form-table" 
			role="presentation"
		>
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
						<input name="last_name" id="last_name" class="regular-text" type="text" data-required="true">
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
						<input name="email" id="email" class="regular-text" type="text" data-required="true" autocapitalize="none" autocorrect="off">
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


function cpt_process_new_client_manager() {
	if ( isset( $_POST['cpt_new_client_manager_nonce'] ) && wp_verify_nonce( $_POST['cpt_new_client_manager_nonce'], 'cpt_new_client_manager_added' ) ) {
		$client_manager_email       = sanitize_email( $_POST['email'] );
		$existing_client_manager_id = email_exists( $client_manager_email );

		if ( ! $existing_client_manager_id ) {
			$userdata           = array(
				'first_name'           => sanitize_text_field( $_POST['first_name'] ),
				'last_name'            => sanitize_text_field( $_POST['last_name'] ),
				'display_name'         => sanitize_text_field( $_POST['first_name'] ) . ' ' . sanitize_text_field( $_POST['last_name'] ),
				'user_email'           => $client_manager_email,
				'user_login'           => sanitize_user( $_POST['email'] ),
				'user_pass'            => null,
				'role'                 => 'cpt-client-manager',
				'show_admin_bar_front' => 'false',
			);
			$new_client_manager = wp_insert_user( $userdata );
		} else {
			$userdata = array(
				'ID'           => $existing_client_manager_id,
				'first_name'   => sanitize_text_field( $_POST['first_name'] ),
				'last_name'    => sanitize_text_field( $_POST['last_name'] ),
				'display_name' => sanitize_text_field( $_POST['first_name'] ) . ' ' . sanitize_text_field( $_POST['last_name'] ),
			);

			$new_client_manager = wp_update_user( $userdata );
			$user               = new \WP_User( $new_client_manager );
			$user->add_role( 'cpt-client-manager' );
		}

		if ( is_wp_error( $new_client_manager ) ) {
			$result = 'Client manager ' . $existing_client_manager_id . ' could not be added. Error message: ' . $new_client_manager->get_error_message();
		} else {
			if ( ! $existing_client_manager_id ) {
				cpt_new_client_manager_email( $new_client_manager );
			}
			$result = 'Client manager added.';
		}

		set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
		wp_redirect( $_POST['_wp_http_referer'] );
		exit;
	} else {
		die();
	}
}

add_action( 'admin_post_cpt_new_client_manager_added', __NAMESPACE__ . '\cpt_process_new_client_manager' );


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
	$subject        = 'Your client manager account has been created. Please set your password.';
	$activation_key = get_password_reset_key( $user );
	$activation_url = home_url() . '?cpt_login=setpw&key=' . $activation_key . '&login=' . urlencode( $user->user_login );

	ob_start();
	?>
		<p>Your username is your email address:</p>
		<p>
			<strong>
				<?php echo esc_html( $user->user_email ); ?>
			</strong>
		</p>
		<p>You will need to activate your account and set a password in order to access your clients.</p>
	<?php
	$card_content = ob_get_clean();

	$message = Common\cpt_get_email_card( $subject, $card_content, 'Activate Your Account', $activation_url );

	wp_mail( $to, $subject, $message, $headers );
}


function cpt_client_manager_list() {
	$client_manager_list = new Client_Manager_List_Table();
	$client_manager_list->prepare_items();
	?>
	<form id="client-manager-list" method="GET">
		<?php $client_manager_list->display(); ?>
	</form>
	<?php
}


// Returns an array of client user objects.
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


function cpt_remove_client_manager( $user_id ) {
	if ( ! $user_id ) {
		return;
	}

	$user = new \WP_User( $user_id );
	$user->remove_role( 'cpt-client-manager' );

	$result = 'Client manager removed.';

	set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
}
