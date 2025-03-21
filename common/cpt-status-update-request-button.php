<?php
/**
 * Status update request button functions.
 *
 * @file       cpt-status-update-request-button.php
 * @package    Client_Power_Tools
 * @subpackage Core\Common
 * @since      1.4.0
 */

namespace Client_Power_Tools\Core\Common;

/**
 * Outputs the status update request button.
 *
 * This is used by the `admin-post` action hook, so it must be loaded as a common function.
 *
 * @param int $user_id The client's user ID.
 */
function cpt_status_update_request_button( $user_id ) {
	if ( ! $user_id ) {
		return;
	}

	// Return (i.e. don't output the button) if the client has clicked the button more recently than the request frequency option allows.
	$request_frequency       = get_option( 'cpt_status_update_req_freq' );
	$days_since_last_request = cpt_get_days_since_last_request( $user_id );

	if (
		$days_since_last_request
		&& $days_since_last_request < $request_frequency
	) {
		$button_value = __( 'Status Update Requested', 'client-power-tools' );
		$disabled     = true;
	} else {
		$button_value = __( 'Request Status Update', 'client-power-tools' );
		$disabled     = false;
	}

	?>
	<div class="cpt-status-update-request">
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">
			<?php wp_nonce_field( 'cpt_status_update_requested', 'cpt_status_update_request_nonce' ); ?>
			<input 
				name="action" 
				value="cpt_status_update_requested" 
				type="hidden"
			>
			<input 
				name="clients_user_id" 
				value="<?php echo esc_attr( $user_id ); ?>" 
				type="hidden"
			>
			<p class="submit">
				<input 
					name="cpt-status-update-request-button"
					id="cpt-status-update-request-button"
					class="button button-primary wp-element-button"
					type="submit"
					value="<?php echo esc_attr( $button_value ); ?>"
					<?php if ( $disabled ) { ?>
						disabled="true"
					<?php } ?>
				>
			</p>
		</form>
	</div>
	<?php
}


/**
 * Calculates the number of days since the client last clicked the status update request button.
 *
 * @param int $user_id The client's user ID.
 * @return int|bool The number of days or false.
 */
function cpt_get_days_since_last_request( $user_id ) {
	if ( ! $user_id ) {
		return false;
	}

	$last_request_date      = null;
	$status_update_requests = new \WP_Query(
		array(
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'   => 'cpt_clients_user_id',
					'value' => $user_id,
				),
				array(
					'key'   => 'cpt_status_update_request',
					'value' => true,
				),
			),
			'order'          => 'DESC',
			'orderby'        => 'post_date',
			'post_type'      => 'cpt_message',
			'posts_per_page' => 1,
		)
	);

	if ( $status_update_requests->have_posts() ) :
		while ( $status_update_requests->have_posts() ) :
			$status_update_requests->the_post();
			$last_request_date = new \DateTime( get_the_date( 'Y-m-d' ) );
		endwhile;
	endif;

	$current_date = new \DateTime( strtotime( gmdate( get_option( 'Y-m-d' ) ) ) );

	if ( $last_request_date ) {
		$days_since_last_request = $last_request_date->diff( $current_date )->days;
	} else {
		$days_since_last_request = false;
	}

	return $days_since_last_request;
}

add_action( 'admin_post_cpt_status_update_requested', __NAMESPACE__ . '\cpt_process_status_update_request' );
/**
 * Creates a new cpt_message post for the status update request and triggers the notification function.
 *
 * @see cpt_status_update_request_notification()
 */
function cpt_process_status_update_request() {
	if (
		isset( $_POST['cpt_status_update_request_nonce'] )
		&& wp_verify_nonce( sanitize_key( wp_unslash( $_POST['cpt_status_update_request_nonce'] ) ), 'cpt_status_update_requested' )
		&& isset( $_POST['clients_user_id'] )
		&& isset( $_POST['_wp_http_referer'] )
	) {
		$clients_user_id = intval( $_POST['clients_user_id'] );
		$http_referrer   = sanitize_text_field( intval( $_POST['_wp_http_referer'] ) );

		$status_update_request = array(
			'post_title'   => __( 'STATUS UPDATE REQUESTED', 'client-power-tools' ),
			'post_content' => __( 'The client would like a status update.', 'client-power-tools' ),
			'post_name'    => md5( current_time( 'timestamp' ) . random_int( 0, PHP_INT_MAX ) ),
			'post_status'  => 'publish',
			'post_type'    => 'cpt_message',
			'meta_input'   => array(
				'cpt_clients_user_id'       => $clients_user_id,
				'cpt_status_update_request' => true,
			),
		);

		$post = wp_insert_post( $status_update_request, $wp_error );

		if ( is_wp_error( $post ) ) {
			$result = sprintf(
				// Translators: %s is the error message.
				__( 'Your status update request could not be sent. Error message: %s', 'client-power-tools' ),
				$post->get_error_message()
			);
		} else {
			cpt_status_update_request_notification( $post );
			$result = __( 'Status update requested!', 'client-power-tools' );
		}

		set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
		wp_safe_redirect( $http_referrer );
		exit;
	} else {
		die();
	}
}


/**
 * Sends an email notification to the client's manager.
 *
 * @param int $message_id The post ID of the status update request message.
 */
function cpt_status_update_request_notification( $message_id ) {
	if ( ! $message_id ) {
		return;
	}

	$msg_obj         = get_post( $message_id );
	$sender_id       = $msg_obj->post_author;
	$clients_user_id = get_post_meta( $message_id, 'cpt_clients_user_id', true );
	$client_data     = cpt_get_client_data( $clients_user_id );
	$from_name       = get_the_author_meta( 'display_name', $msg_obj->post_author );
	$from_email      = get_the_author_meta( 'user_email', $msg_obj->post_author );
	$headers[]       = 'Content-Type: text/html; charset=UTF-8';
	$headers[]       = 'From: ' . $from_name . ' <' . $from_email . '>';
	$to              = $client_data['manager_email'];

	if ( get_option( 'cpt_status_update_req_notice_email' ) ) {
		$cc        = get_option( 'cpt_status_update_req_notice_email' );
		$headers[] = 'Cc: ' . $cc;
	}
	$subject = sprintf(
		// Translators: %1$s is the message subject ("Status update requested"). %2$s is the sender's name.
		__( '%1$s by %2$s', 'client-power-tools' ),
		$msg_obj->post_title,
		$from_name
	);

	$subject_html = sprintf(
		// Translators: %1$s is the message subject. %2$s is an HTML line break. %3$s is the sender's name.
		__( '%1$s%2$s by %3$s', 'client-power-tools' ),
		$msg_obj->post_title,
		'&nbsp;<br />',
		$from_name
	);

	$message = '<p>' . __( 'Please give them an update.', 'client-power-tools' ) . '</p>';

	if ( get_option( 'cpt_module_messaging' ) ) {
		$button_txt = sprintf(
			// Translators: %s is the sender's name.
			__( 'Go to %s', 'client-power-tools' ),
			$from_name
		);
		$profile_url = cpt_get_client_profile_url( $sender_id );
	} else {
		$button_txt  = null;
		$profile_url = null;
	}

	$message = cpt_get_email_card( $subject_html, $message, $button_txt, $profile_url );
	wp_mail( $to, $subject, $message, $headers );
}
