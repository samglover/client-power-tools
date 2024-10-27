<?php

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

add_action( 'admin_init', __NAMESPACE__ . '\cpt_admin_actions' );
function cpt_admin_actions() {
	if (
		! isset( $_REQUEST['action'] ) ||
		! isset( $_REQUEST['page'] )
	) {
		return;
	}

	if ( ! $_REQUEST['_wpnonce'] ) {
		exit( esc_html__( 'Missing nonce.', 'client-power-tools' ) );
	}

	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ) ) {
		exit( esc_html__( 'Invalid nonce.', 'client-power-tools' ) );
	}

	$page = sanitize_key( $_REQUEST['page'] );
	if ( ! str_starts_with( $page, 'cpt-' ) ) {
		return;
	}

	switch ( $page ) {
		case 'cpt-project-types':
			$action = sanitize_key( $_REQUEST['action'] );
			cpt_process_project_type_actions( $action );
			break;
		default:
			exit( esc_html__( 'Unknown page.', 'client-power-tools' ) );
	}
}


add_action( 'admin_notices', __NAMESPACE__ . '\cpt_security_warning', 1 );
function cpt_security_warning() {
	global $pagenow;
	if ( ! is_ssl() && cpt_is_cpt_admin_page() ) {
		?>
		<div class="cpt-notice notice notice-warning">
			<p>
				<?php
					printf(
						wp_kses_post(
							// Translators: %1$s and %2$s are HTML <a> tags for a link to the Client Power Tools website for additional information about this warning.
							__( 'It doesn\'t look like your website is using SSL (HTTPS). Before using Client Power Tools with your clients, it\'s a good idea to get an SSL certificate for your website and consider additional security measures. %1$sLearn more.%2$s', 'client-power-tools' )
						),
						/* %1$s */ '<a href="https://clientpowertools.com/security/?utm_source=cpt_user&utm_medium=cpt_ssl_warning" target="_blank">',
						/* %2$s */ '</a>'
					);
				?>
			</p>
		</div>
		<?php
	}
}


add_action( 'admin_notices', __NAMESPACE__ . '\cpt_welcome_message' );
function cpt_welcome_message() {
	global $pagenow;
	if ( cpt_is_cpt_admin_page() && get_transient( 'cpt_show_welcome_message' ) ) {
		?>
			<div class="cpt-notice notice notice-info">
				<h2><?php esc_html_e( 'Welcome to Client Power Tools!', 'client-power-tools' ); ?></h2>
				<p style="font-size: 125%;">
					<?php
						printf(
							wp_kses_post(
								// Translators: %1$s and %2$s are HTML <a> tags for a link to the main Client Power Tools admin page.
								__( 'You can view and manage your clients here, in the WordPress dashboard. You can add your first client on the %1$sClients page%2$s (if you are an admin).', 'client-power-tools' )
							),
							/* %1$s */ '<a href="' . esc_url( add_query_arg( 'page', 'cpt', admin_url( 'admin.php' ) ) ) . '" target="_blank">',
							/* %2$s */ '</a>'
						);
					?>
				</p>
				<p style="font-size: 125%;">
					<?php
						printf(
							wp_kses_post(
								// Translators: %1$s and %2$s are HTML <a> tags for a link to the frontend client dashboard.
								__( 'Your clients can access their dashboard by visiting %1$sthis page%2$s on the front end of your website (clients don\'t have access to the WordPress admin dashboard). You\'ll probably want to add that page to your navigation menu to make it easy for your clients to find.', 'client-power-tools' )
							),
							/* %1$s */ '<a href="' . esc_url( Common\cpt_get_client_dashboard_url() ) . '" target="_blank">',
							/* %2$s */ '</a>'
						);
					?>
				</p>
				<p style="font-size: 125%;">
					<?php
						printf(
							wp_kses_post(
								// Translators: %1$s is an HTML link to the Client Power Tools website. %2$s and %3$s are HTML <a> tags for a link to the WordPress.org support forum.
								__( 'You can find options and customizations in the settings, and you can find additional documentation at %1$s. If you need help, please use the %2$sWordPress.org support forum%3$s.', 'client-power-tools' )
							),
							/* %1$s */ '<a href="https://clientpowertools.com/documentation/" target="_blank">clientpowertools.com</a>',
							/* %2$s */ '<a href="https://wordpress.org/support/plugin/client-power-tools/" target="_blank">',
							/* %3$s */ '</a>'
						);
					?>
				</p>
				<p style="font-size: 125%;">
					<?php
						printf(
							wp_kses_post(
								// Translators: %1$s and %2$s are HTML <a> tags for a link to the frontend client dashboard.
								__( 'Please %1$sleave a review on WordPress.org%2$s!', 'client-power-tools' )
							),
							/* %1$s */ '<a href="https://wordpress.org/plugins/client-power-tools/#reviews" target="_blank">',
							/* %2$s */ '</a>'
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


function cpt_get_client_manager_select( $name = null, $selected = null ) {
	if ( ! $name ) {
		$name = 'client_manager';
	}
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
function cpt_show_wp_mail_errors( $wp_error ) {
	?>
		<pre>
			<?php print_r( $wp_error ); ?>
		</pre>
	<?php
}