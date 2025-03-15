<?php
/**
 * Processes login functions:
 * - Sending the login code for passwordless logins.
 * - Checking the login code.
 * - Checking the password for regular logins.
 *
 * @file       cpt-login.php
 * @package    Client_Power_Tools
 * @subpackage Core\Common
 * @since      1.4.11
 */

namespace Client_Power_Tools\Core\Common;

add_action( 'wp_ajax_nopriv_send_login_code', __NAMESPACE__ . '\send_login_code' );
/**
 * Sends an email with the login code.
 */
function send_login_code() {
	if (
		isset( $_POST['_ajax_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) ), 'cpt-login' )
	) {
		if ( isset( $_POST['email'] ) ) {
			$email = sanitize_text_field( wp_unslash( $_POST['email'] ) );
		} else {
			$email = false;
		}

		if ( ! $email || strlen( $email ) < 1 ) {
			wp_send_json_error( array( 'message' => __( 'Email address is missing.', 'client-power-tools' ) ) );
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'client-power-tools' ) ) );
		}

		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			wp_send_json_error( array( 'message' => __( 'No user with that email address.', 'client-power-tools' ) ) );
		}

		$to      = $email;
		$subject = '[' . get_bloginfo( 'title' ) . '] ' . __( 'Login Code', 'client-power-tools' );
		$code    = wp_generate_password( 8, false );

		set_transient( 'cpt_login_code_' . $user->ID, wp_hash_password( $code ), 600 );
		set_transient( 'cpt_login_code_tries_' . $user->ID, 0, 600 );

		$dashboard_url = add_query_arg(
			array(
				'cpt_login' => 'code',
				'user'      => rawurlencode( $email ),
			),
			cpt_get_client_dashboard_url()
		);

		ob_start();

		?>
		<p>
			<?php esc_html_e( 'Here is the login code you requested', 'client-power-tools' ); ?>:
		</p>
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
					// Translators: %s is the email address entered into the login form.
					__(
						'Login code sent to %s. If you don\'t see it in your inbox, remember to check your spam folder just in case! The code will remain valid for 10 minutes.',
						'client-power-tools'
					),
					$email
				),
			)
		);
	} else {
		wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'client-power-tools' ) ) );
	}
}


add_action( 'wp_ajax_nopriv_check_login_code', __NAMESPACE__ . '\check_login_code' );
/**
 * Checks the login code.
 */
function check_login_code() {
	if (
		isset( $_POST['_ajax_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) ), 'cpt-login' )
	) {
		if ( isset( $_POST['email'] ) ) {
			$email = sanitize_text_field( wp_unslash( $_POST['email'] ) );
		} else {
			$email = false;
		}

		if ( ! $email || strlen( $email ) < 1 ) {
			wp_send_json_error( array( 'message' => __( 'Email address is missing.', 'client-power-tools' ) ) );
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'client-power-tools' ) ) );
		}

		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			wp_send_json_error( array( 'message' => __( 'User does not exist.', 'client-power-tools' ) ) );
		}

		if ( isset( $_POST['code'] ) ) {
			$code = trim( sanitize_text_field( wp_unslash( $_POST['code'] ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Code is missing.', 'client-power-tools' ) ) );
		}

		$code_ok = wp_check_password( $code, get_transient( 'cpt_login_code_' . $user->ID ) );
		if ( ! $code_ok ) {
			$tries         = get_transient( 'cpt_login_code_tries_' . $user->ID );
			$tries_allowed = 3;

			if ( ! $tries ) {
				wp_send_json_error( array( 'message' => __( 'Missing transient.', 'client-power-tools' ) ) );
			}

			if ( $tries < $tries_allowed ) {
				++$tries;
				set_transient( 'cpt_login_code_tries_' . $user->ID, $tries );
				wp_send_json_error(
					array(
						'message' => sprintf(
							// Translators: %1$s is the number of tries. %2$s is the number of tries allowed.
							__( '%1$s of %2$s tries. Login failed.', 'client-power-tools' ),
							$tries,
							$tries_allowed
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
	} else {
		wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'client-power-tools' ) ) );
	}
}


add_action( 'wp_ajax_nopriv_check_password', __NAMESPACE__ . '\check_password' );
/**
 * Checks the password.
 */
function check_password() {
	if (
		! isset( $_POST['_ajax_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) ), 'cpt-login' )
	) {
		wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'client-power-tools' ) ) );
	}

	if ( isset( $_POST['email'] ) ) {
		$email = sanitize_text_field( wp_unslash( $_POST['email'] ) );
	} else {
		$email = false;
	}

	if (
		! $email
		|| strlen( $email ) < 1
	) {
		wp_send_json_error( array( 'message' => __( 'Email address is missing.', 'client-power-tools' ) ) );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'client-power-tools' ) ) );
	}

	if ( isset( $_POST['password'] ) ) {
		$password = sanitize_text_field( wp_unslash( $_POST['password'] ) );
	} else {
		$password = false;
	}

	if (
		! $password
		|| strlen( $password ) < 1
	) {
		wp_send_json_error( array( 'message' => __( 'Password is missing.', 'client-power-tools' ) ) );
	}

	$user = get_user_by( 'email', $email );
	if ( ! $user ) {
		wp_send_json_error( array( 'message' => __( 'User does not exist.', 'client-power-tools' ) ) );
	}

	$password_ok = wp_check_password( $password, $user->data->user_pass, $user->ID );
	if ( ! $password_ok ) {
		wp_send_json_error( array( 'message' => __( 'Login failed.', 'client-power-tools' ) ) );
	}

	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true );
	wp_send_json_success( array( 'message' => 'ECHO' . __( 'Logging you in …', 'client-power-tools' ) ) );
}