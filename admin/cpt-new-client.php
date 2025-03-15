<?php
/**
 * New client
 *
 * @file       cpt-new-client.php
 * @package    Client_Power_Tools
 * @subpackage Core\Admin
 * @since      1.0.0
 */

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

add_action( 'admin_post_cpt_new_client_added', __NAMESPACE__ . '\cpt_process_new_client' );
/**
 * Process new client
 */
function cpt_process_new_client() {
	if (
		! isset( $_POST['cpt_new_client_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpt_new_client_nonce'] ) ), 'cpt_new_client_added' )
	) {
		exit( esc_html__( 'Invalid nonce.', 'client-power-tools' ) );
	}

	if (
		! isset( $_POST['first_name'] )
		|| ! isset( $_POST['last_name'] )
		|| ! isset( $_POST['email'] )
	) {
		exit( esc_html__( 'Missing required fields', 'client-power-tools' ) );
	}

	$existing_id = email_exists( sanitize_email( wp_unslash( $_POST['email'] ) ) );
	$user_data   = Common\cpt_get_sanitized_userdata_from_post();

	if ( ! $existing_id ) {
		$user_data  = array_merge(
			$user_data,
			array(
				'user_login'           => $user_data['user_email'],
				'user_pass'            => wp_hash_password( wp_generate_password( 32, true ) ),
				'role'                 => 'cpt-client',
				'show_admin_bar_front' => 'false',
			)
		);
		$new_client = wp_insert_user( $user_data );
	} else {
		$user_data['ID'] = $existing_id;
		$new_client      = wp_update_user( $user_data );
		$user            = new \WP_User( $new_client );
		$user->add_role( 'cpt-client' );
	}

	if ( is_wp_error( $new_client ) ) {
		$result = sprintf(
			// Translators: %s is the error message.
			__( 'Client could not be created. Error message: %s', 'client-power-tools' ),
			$new_client->get_error_message()
		);
	} else {
		$user_meta = Common\cpt_get_sanitized_usermeta_from_post();
		foreach ( $user_meta as $meta_key => $meta_value ) {
			update_user_meta( $new_client, $meta_key, $meta_value );
		}

		$custom_fields = Common\cpt_get_sanitized_custom_fields_from_post();
		if ( $custom_fields ) {
			foreach ( $custom_fields as $field_key => $field_value ) {
				update_user_meta( $new_client, $field_key, $field_value );
			}
		}

		if ( ! $existing_id ) {
			cpt_new_client_email( $new_client );
		}
		$client_profile_url = Common\cpt_get_client_profile_url( $new_client );
		$result             = sprintf(
			// Translators: %1$s is an `<a>` tag that links to the client's profile URL. %2$s is the client's name. %3$s is the closing `</a>` tag.
			__( 'Client created. %1$sView %2$s\'s profile%3$s.', 'client-power-tools' ),
			'<a href="' . esc_url( $client_profile_url ) . '">',
			Common\cpt_get_client_name( $new_client ),
			'</a>'
		);
	}

	set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
	wp_safe_redirect( wp_get_referer() );
	exit;
}

/**
 * Assembles the new client notification email.
 *
 * @param int $clients_user_id Client's user ID.
 */
function cpt_new_client_email( $clients_user_id ) {
	if ( ! $clients_user_id ) {
		exit( esc_html__( 'Missing client\'s user ID.', 'client-power-tools' ) );
	}

	$user        = get_userdata( $clients_user_id );
	$client_data = Common\cpt_get_client_data( $clients_user_id );
	$from_name   = Common\cpt_get_display_name( $client_data['manager_id'] );
	$from_email  = $client_data['manager_email'];
	$headers[]   = 'Content-Type: text/html; charset=UTF-8';
	$headers[]   = $from_name ? 'From: ' . $from_name . ' <' . $from_email . '>' : 'From: ' . $from_email;
	$to          = $user->user_email;
	$subject     = get_option( 'cpt_new_client_email_subject_line' );

	$title        = __( 'Your Client Account Has Been Created', 'client-power-tools' );
	$card_content = '<p>' . __( 'Use your email address to access your client dashboard:', 'client-power-tools' ) . ' <strong>' . $user->user_email . '</strong></p>';
	$button_txt   = __( 'Visit Your Client Dashboard', 'client-power-tools' );
	$button_url   = Common\cpt_get_client_dashboard_url();

	$message = get_option( 'cpt_new_client_email_message_body' ) . Common\cpt_get_email_card( $title, $card_content, $button_txt, $button_url );

	wp_mail( $to, $subject, $message, $headers );
}
