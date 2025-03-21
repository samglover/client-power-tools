<?php
/**
 * Admin functions
 *
 * @file       cpt-admin.php
 * @package    Client_Power_Tools
 * @subpackage Core\Admin
 * @since      1.0.0
 */

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

add_action( 'admin_notices', __NAMESPACE__ . '\cpt_security_warning', 1 );
/**
 * Security warning for websites that are not using SSL (HTTPS).
 */
function cpt_security_warning() {
	global $pagenow;
	if ( ! is_ssl() && cpt_is_cpt_admin_page() ) {
		?>
		<div class="cpt-notice notice notice-warning">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						// Translators: %1$s and %2$s are `<a>` tags for a link to the Client Power Tools website for additional information about this warning.
						__( 'It doesn\'t look like your website is using SSL (HTTPS). Before using Client Power Tools with your clients, it\'s a good idea to get an SSL certificate for your website and consider additional security measures. %1$sLearn more.%2$s', 'client-power-tools' ),
						'<a href="https://clientpowertools.com/security/?utm_source=cpt_user&utm_medium=cpt_ssl_warning" target="_blank">',
						'</a>'
					)
				);
				?>
			</p>
		</div>
		<?php
	}
}


add_action( 'admin_notices', __NAMESPACE__ . '\cpt_welcome_message' );
/**
 * Welcome message (dismissible)
 */
function cpt_welcome_message() {
	global $pagenow;
	if ( cpt_is_cpt_admin_page() && get_transient( 'cpt_show_welcome_message' ) ) {
		?>
			<div class="cpt-notice notice notice-info">
				<h2><?php esc_html_e( 'Welcome to Client Power Tools!', 'client-power-tools' ); ?></h2>
				<p style="font-size: 125%;">
					<?php
					echo wp_kses_post(
						sprintf(
							// Translators: %1$s and %2$s are `<a>` tags for a link to the main Client Power Tools admin page.
							__( 'You can view and manage your clients here, in the WordPress dashboard. You can add your first client on the %1$sClients page%2$s (if you are an admin).', 'client-power-tools' ),
							'<a href="' . esc_url( add_query_arg( 'page', 'cpt', admin_url( 'admin.php' ) ) ) . '" target="_blank">',
							'</a>'
						)
					);
					?>
				</p>
				<p style="font-size: 125%;">
					<?php
					echo wp_kses_post(
						sprintf(
							// Translators: %1$s and %2$s are `<a>` tags for a link to the frontend client dashboard.
							__( 'Your clients can access their dashboard by visiting %1$sthis page%2$s on the front end of your website (clients don\'t have access to the WordPress admin dashboard). You\'ll probably want to add that page to your navigation menu to make it easy for your clients to find.', 'client-power-tools' ),
							'<a href="' . esc_url( Common\cpt_get_client_dashboard_url() ) . '" target="_blank">',
							'</a>'
						)
					);
					?>
				</p>
				<p style="font-size: 125%;">
					<?php
					echo wp_kses_post(
						sprintf(
							// Translators: %1$s is a link to the Client Power Tools website. %2$s and %3$s are `<a>` tags for a link to the WordPress.org support forum.
							__( 'You can find options and customizations in the settings, and you can find additional documentation at %1$s. If you need help, please use the %2$sWordPress.org support forum%3$s.', 'client-power-tools' ),
							'<a href="https://clientpowertools.com/documentation/" target="_blank">clientpowertools.com</a>',
							'<a href="https://wordpress.org/support/plugin/client-power-tools/" target="_blank">',
							'</a>'
						)
					);
					?>
				</p>
				<p style="font-size: 125%;">
					<?php
					echo wp_kses_post(
						sprintf(
							// Translators: %1$s and %2$s are `<a>` tags for a link to the frontend client dashboard.
							__( 'Please %1$sleave a review on WordPress.org%2$s!', 'client-power-tools' ),
							'<a href="https://wordpress.org/plugins/client-power-tools/#reviews" target="_blank">',
							'</a>'
						)
					);
					?>
				</p>
				<p style="font-size: 125%;"><?php esc_html_e( '—Sam', 'client-power-tools' ); ?></p>
			</div>
		<?php
		delete_transient( 'cpt_show_welcome_message' );
	}
}


add_action( 'admin_menu', __NAMESPACE__ . '\cpt_menu_pages' );
/**
 * Adds the admin menu pages.
 */
function cpt_menu_pages() {
	add_menu_page(
		'Client Power Tools',
		'Clients',
		'cpt_view_clients',
		'cpt',
		__NAMESPACE__ . '\cpt_clients',
		CLIENT_POWER_TOOLS_DIR_URL . 'assets/images/cpt-icon.svg',
		'3',
	);

	add_submenu_page(
		'cpt',
		'Client Power Tools: Clients',
		'Clients',
		'cpt_view_clients',
		'cpt',
		__NAMESPACE__ . '\cpt_clients',
	);

	if ( get_option( 'cpt_module_messaging' ) ) {
		add_submenu_page(
			'cpt',
			'Client Power Tools: Messages',
			'Messages',
			'cpt_view_clients',
			'cpt-messages',
			__NAMESPACE__ . '\cpt_admin_messages',
		);
	}

	if ( get_option( 'cpt_module_projects' ) ) {
		$projects_label = Common\cpt_get_projects_label();
		add_submenu_page(
			'cpt',
			'Client Power Tools: ' . $projects_label[1],
			$projects_label[1],
			'cpt_view_projects',
			'cpt-projects',
			__NAMESPACE__ . '\cpt_projects',
		);

		add_submenu_page(
			'cpt',
			'Client Power Tools: ' . $projects_label[0] . ' Types',
			$projects_label[0] . ' Types',
			'cpt_view_projects',
			'cpt-project-types',
			__NAMESPACE__ . '\cpt_project_types',
		);
	}

	add_submenu_page(
		'cpt',
		'Client Power Tools: Client Managers',
		'Managers',
		'cpt_manage_team',
		'cpt-managers',
		__NAMESPACE__ . '\cpt_client_managers',
	);

	add_submenu_page(
		'cpt',
		'Client Power Tools: Settings',
		'Settings',
		'cpt_manage_settings',
		'cpt-settings',
		__NAMESPACE__ . '\cpt_settings',
	);
}


/**
 * Utility function for checking to see if the current page is a CPT page.
 */
function cpt_is_cpt_admin_page() {
	global $pagenow;
	if ( ! isset( $_GET['page'] ) ) {
		return false;
	}
	$page = sanitize_key( $_GET['page'] );
	if ( 'admin.php' === $pagenow && str_starts_with( $page, 'cpt' ) ) {
		return true;
	} else {
		return false;
	}
}


/**
 * Gets the client manager select drop-down.
 *
 * @param string $name Optional The field name property. Default is 'client_manager'.
 * @param int    $selected User ID of the client's manager.
 */
function cpt_get_client_manager_select( $name = 'client_manager', $selected = null ) {
	if ( ! $selected ) {
		$selected = get_option( 'cpt_default_client_manager' );
	}

	$client_manager_query = new \WP_USER_QUERY(
		array(
			'role__in' => array( 'cpt-client-manager' ),
			'orderby'  => 'display_name',
			'order'    => 'ASC',
		)
	);

	$client_managers = $client_manager_query->get_results();

	if ( $client_managers ) {
		?>
			<select 
				name="<?php echo esc_attr( $name ); ?>"
				id="<?php echo esc_attr( $name ); ?>"
			>
				<option value><?php esc_html_e( 'Unassigned', 'client-power-tools' ); ?></option>
				<?php foreach ( $client_managers as $client_manager ) { ?>
					<option
						value="<?php echo esc_attr( $client_manager->ID ); ?>"
						<?php selected( $client_manager->ID, $selected ); ?>
					>
						<?php echo esc_html( $client_manager->display_name ); ?>
					</option>
				<?php } ?>
			</select>
		<?php
	} else {
		?>
			<p class="description">
				<a href="<?php echo esc_url( add_query_arg( 'page', 'cpt-managers', admin_url( 'admin.php' ) ) ); ?>">
					<?php esc_html_e( 'Add a client manager.', 'client-power-tools' ); ?>
				</a>
			</p>
		<?php
	}
}

/**
 * Get status select drop-down
 *
 * @param string $option Option slug.
 * @param string $name Field name.
 * @param string $selected Optional. Selected option slug.
 */
function cpt_get_status_select( $option = null, $name = null, $selected = null ) {
	if ( ! $option || ! $name ) {
		return;
	}

	$statuses_array = explode( "\n", get_option( $option ) );
	if ( ! $selected ) {
		$selected = get_option( $name );
	}

	?>
		<select 
			name="<?php echo esc_attr( $name ); ?>"
			id="<?php echo esc_attr( $name ); ?>"
		>
			<?php foreach ( $statuses_array as $status ) { ?>
				<?php $status = trim( $status ); ?>
				<option 
					value="<?php echo esc_attr( $status ); ?>"
					<?php selected( $status, $selected ); ?>
				>
					<?php echo esc_html( $status ); ?>
				</option>
			<?php } ?>
		</select>
	<?php
}

add_action( 'wp_mail_failed', __NAMESPACE__ . '\cpt_show_wp_mail_errors', 10, 1 );
/**
 * Outputs email sending errors.
 *
 * @param object $wp_error A WP_Error object with the PHPMailerPHPMailerException message, and an array containing the mail recipient, subject, message, headers, and attachments.
 */
function cpt_show_wp_mail_errors( $wp_error ) {
	?>
		<pre>
			<?php print_r( $wp_error ); ?>
		</pre>
	<?php
}