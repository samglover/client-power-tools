<?php

namespace Client_Power_Tools\Core\Common;

add_action( 'wp_ajax_nopriv_send_login_code', __NAMESPACE__ . '\send_login_code' );
function send_login_code() {
	if ( ! isset( $_POST['_ajax_nonce'] ) || ! wp_verify_nonce( $_POST['_ajax_nonce'], 'cpt-login' ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'client-power-tools' ) ) );
	}
	if ( ! isset( $_POST['email'] ) || strlen( $_POST['email'] ) < 1 ) {
		wp_send_json_error( array( 'message' => __( 'Email address is missing.', 'client-power-tools' ) ) );
	}
	if ( ! is_email( sanitize_email( $_POST['email'] ) ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'client-power-tools' ) ) );
	}

	$email = sanitize_email( $_POST['email'] );
	$user  = get_user_by( 'email', $email );
	if ( ! $user ) {
		wp_send_json_error( array( 'message' => __( 'Unable to find a user with that email address.', 'client-power-tools' ) ) );
	}

	$to      = $email;
	$subject = '[' . get_bloginfo( 'title' ) . '] ' . __( 'Login Code', 'client-power-tools' );

	$code = wp_generate_password( 8, false );
	set_transient( 'cpt_login_code_' . $user->ID, wp_hash_password( $code ), 600 );
	set_transient( 'cpt_login_code_tries_' . $user->ID, 0, 600 );

	$dashboard_url = add_query_arg(
		array(
			'cpt_login' => 'code',
			'user'      => urlencode( $email ),
		),
		cpt_get_client_dashboard_url()
	);

	ob_start();
	?>
			<p><?php esc_html_e( 'Here is the login code you requested', 'client-power-tools' ); ?>: </p>
			<p style="text-align: center;">
				<strong style="font-size: 125%">
					<?php echo esc_html( $code ); ?>
				</strong>
			</p>
		<?php
		$card_content = ob_get_clean();

		$message   = cpt_get_email_card( '', $card_content, __( 'Go to Login', 'client-power-tools' ), $dashboard_url );
		$headers[] = 'Content-Type: text/html; charset=UTF-8';

		$result = wp_mail( $to, $subject, $message, $headers );
		wp_send_json_success(
			array(
				'message' => sprintf(
					__( 'Login code sent to %s. If you don\'t see it in your inbox, remember to check your spam folder just in case! The code will remain valid for 10 minutes.', 'client-power-tools' ),
					$email
				),
			)
		);
}


add_action( 'wp_ajax_nopriv_check_login_code', __NAMESPACE__ . '\check_login_code' );
function check_login_code() {
	if ( ! isset( $_POST['_ajax_nonce'] ) || ! wp_verify_nonce( $_POST['_ajax_nonce'], 'cpt-login' ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'client-power-tools' ) ) );
	}
	if ( ! isset( $_POST['email'] ) || strlen( $_POST['email'] ) < 1 ) {
		wp_send_json_error( array( 'message' => __( 'Email address is missing.', 'client-power-tools' ) ) );
	}

	$user = get_user_by( 'email', sanitize_email( $_POST['email'] ) );
	if ( ! $user ) {
		wp_send_json_error( array( 'message' => __( 'User missing.', 'client-power-tools' ) ) );
	}

	$code = isset( $_POST['code'] ) ? wp_check_password( trim( $_POST['code'] ), get_transient( 'cpt_login_code_' . $user->ID ) ) : false;
	if ( ! $code ) {
		$tries = get_transient( 'cpt_login_code_tries_' . $user->ID );
		if ( $tries < 3 ) {
			++$tries;
			set_transient( 'cpt_login_code_tries_' . $user->ID, $tries );
			wp_send_json_error(
				array(
					'message' => sprintf(
						__( '%s of 3 tries. Login failed.', 'client-power-tools' ),
						$tries
					),
					'tries'   => $tries,
				)
			);
		} else {
			delete_transient( 'cpt_login_code_' . $user->ID );
			delete_transient( 'cpt_login_code_tries_' . $user->ID );
			wp_send_json_error( array( 'message' => __( 'Too many tries. Login failed.', 'client-power-tools' ) ) );
		}
	}

	delete_transient( 'cpt_login_code_' . $user->ID );
	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true );
	wp_send_json_success( array( 'message' => __( 'Logging you in …', 'client-power-tools' ) ) );
}


add_action( 'wp_ajax_nopriv_check_password', __NAMESPACE__ . '\check_password' );
function check_password() {
	if ( ! isset( $_POST['_ajax_nonce'] ) || ! wp_verify_nonce( $_POST['_ajax_nonce'], 'cpt-login' ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'client-power-tools' ) ) );
	}
	if ( ! isset( $_POST['email'] ) || strlen( $_POST['email'] ) < 1 ) {
		wp_send_json_error( array( 'message' => __( 'Email address is missing.', 'client-power-tools' ) ) );
	}
	if ( ! isset( $_POST['password'] ) || strlen( $_POST['password'] ) < 1 ) {
		wp_send_json_error( array( 'message' => __( 'Password is missing.', 'client-power-tools' ) ) );
	}

	$user     = is_email( sanitize_email( $_POST['email'] ) ) ? get_user_by( 'email', sanitize_email( $_POST['email'] ) ) : false;
	$password = wp_check_password( $_POST['password'], $user->data->user_pass, $user->ID );
	if ( ! $user || ! $password ) {
		wp_send_json_error( array( 'message' => __( 'Login failed.', 'client-power-tools' ) ) );
	}

	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true );
	wp_send_json_success( array( 'message' => __( 'Logging you in …', 'client-power-tools' ) ) );
}