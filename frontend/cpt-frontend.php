<?php
/**
 * Frontend functions.
 *
 * @file       cpt-frontend.php
 * @package    Client_Power_Tools
 * @subpackage Core\Frontend
 * @since      1.0.0
 */

namespace Client_Power_Tools\Core\Frontend;

use Client_Power_Tools\Core\Common;

add_filter( 'body_class', __NAMESPACE__ . '\cpt_body_classes' );
/**
 * Adds body classes to CPT pages, including .customize-cpt for overriding default CPT styles.
 *
 * @param array $classes An array of body classes.
 * @return array A modified array of body classes.
 */
function cpt_body_classes( $classes ) {
	global $wp_query;
	if (
		! isset( $wp_query->post->ID )
	) {
		return $classes;
	}

	if ( ! Common\cpt_is_client_dashboard() ) {
		return $classes;
	}

	$post_id      = $wp_query->post->ID;
	$body_classes = array(
		'customize-cpt',
		'client-dashboard',
	);

	if ( isset( $_REQUEST['projects_post_id'] ) ) {
		$body_classes[] = 'is-singular-project';
	}

	$body_classes[] = 'client-dashboard-' . Common\cpt_is_client_dashboard();

	return array_merge( $classes, $body_classes );
}


add_action( 'wp_footer', __NAMESPACE__ . '\cpt_login' );
/**
 * Loads the login modal in the footer.
 */
function cpt_login() {
	?>
		<div 
			id="cpt-login" 
			class="cpt-modal card"
		>
			<button class="cpt-dismiss-button cpt-login-modal-dismiss">
				<span class="screen-reader-text">Close</span>
			</button>
			<?php if ( ! is_user_logged_in() ) : ?>
				<h2><?php esc_html_e( 'Client Login', 'client-power-tools' ); ?></h2>
				<div 
					id="cpt-login-notices" 
					class="notice cpt-notice"
				></div>
				<form 
					id="cpt-login-form" 
					name="cpt-login-form" 
					action="<?php echo esc_url( get_permalink() ); ?>" 
					method="post"
				>
					<?php wp_nonce_field( 'cpt-login', 'cpt-login-nonce' ); ?>
					<p id="cpt-login-email">
						<label for="cpt-login-email-field">Email Address</label>
						<input 
							id="cpt-login-email-field" 
							class="input" 
							name="cpt-login-email-field" 
							type="text" 
							autocomplete="username" 
							value="" 
							size="20"
						>
					</p>
					<p 
						id="cpt-login-code" 
						data-button-text="<?php esc_attr_e( 'Send Code', 'client-power-tools' ); ?>"
					>
						<label for="cpt-login-code-field">Login Code</label>
						<input 
							id="cpt-login-code-field" 
							class="input" 
							name="cpt-login-code-field" 
							type="password" 
							autocomplete="one-time-code" 
							value="" 
							size="20"
						>
					</p>
					<p 
						id="cpt-login-password" 
						data-button-text="<?php esc_attr_e( 'Log In', 'client-power-tools' ); ?>"
					>
						<label for="cpt-login-password-field">Password</label>
						<input 
							id="cpt-login-password-field" 
							class="input" 
							name="cpt-login-password-field" 
							type="password" 
							autocomplete="current-password" 
							value="" 
							size="20"
						>
					</p>
					<p id="cpt-login-type-links">
						<a 
							id="cpt-login-code-link" 
							href="#"
						>
							<?php esc_html_e( 'Get a login code by email.', 'client-power-tools' ); ?>
						</a>
						<a 
							id="cpt-password-link" 
							href="#"
						>
							<?php esc_html_e( 'Use a password instead.', 'client-power-tools' ); ?>
						</a>
					</p>
					<p id="cpt-login-submit">
						<input 
							id="cpt-login-submit-button" 
							class="button button-primary wp-element-button" 
							name="cpt-login-submit-button" 
							type="submit" 
							value="<?php esc_html_e( 'Send Code', 'client-power-tools' ); ?>"
						>
					</p>
				</form>
			<?php else : ?>
				<h2><?php esc_html_e( 'Log Out?', 'client-power-tools' ); ?></h2>
				<p>
					<a 
						id="cpt-logout" 
						class="button wp-element-button" 
						href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" 
						rel="nofollow"
					>
						<?php esc_html_e( 'Log Out', 'client-power-tools' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
		<div id="cpt-modal-screen"></div>
	<?php
}


add_filter( 'the_title', __NAMESPACE__ . '\cpt_client_dashboard_page_titles', 10, 2 );
/**
 * Replaces client dashboard page titles with the title of the client dashboard page. Only works within the loop so it only replaces the title above the CPT nav menu.
 *
 * @see cpt_the_title()
 * @see cpt_get_the_title()
 * @param string $title The post title.
 * @return string The client dashboard page title.
 */
function cpt_client_dashboard_page_titles( $title ) {
	if (
		! is_main_query()
		|| ! in_the_loop()
		|| ! Common\cpt_is_client_dashboard()
	) {
		return $title;
	}

	$client_dashboard_page_title = get_post( get_option( 'cpt_client_dashboard_page_selection' ) )->post_title;
	return $client_dashboard_page_title;
}

/**
 * Outputs the title of the client dashboard page beneath the CPT nav menu.
 *
 * @see cpt_client_dashboard_page_titles()
 * @see cpt_get_the_title()
 */
function cpt_the_title() {
	$classes = array(
		'entry-title',
		'wp-block-post-title',
		'cpt-entry-title',
	);
	?>
		<h2 class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php echo esc_html( cpt_get_the_title() ); ?>
		</h2>
	<?php
}

/**
 * Modifies the title of the home, messages, and projects tabs/pages.
 *
 * @see cpt_client_dashboard_page_titles()
 * @see cpt_the_title()
 *
 * @return string The modified title.
 */
function cpt_get_the_title() {
	remove_filter( 'the_title', __NAMESPACE__ . '\cpt_client_dashboard_page_titles', 10, 2 );
	if ( Common\cpt_is_client_dashboard( 'home' ) ) {
		return __( 'Your Home', 'client-power-tools' );
	}
	if ( Common\cpt_is_client_dashboard( 'messages' ) ) {
		return __( 'Your Messages', 'client-power-tools' );
	}
	if ( Common\cpt_is_client_dashboard( 'projects' ) ) {
		return __( 'Your', 'client-power-tools' ) . ' ' . Common\cpt_get_projects_label( 'plural' );
	}
	if ( Common\cpt_is_project() ) {
		return;
	}
	return get_the_title();
}


/**
 * Tests to determine whether the client dashboard is being displayed.
 *
 * @deprecated
 */
function cpt_is_cpt() {
	trigger_error( 'Function ' . __FUNCTION__ . ' is deprecated as of 1.7.6 and will be removed soon.', E_USER_DEPRECATED );
	return Common\cpt_is_client_dashboard();
}
