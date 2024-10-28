<?php

namespace Client_Power_Tools\Core\Frontend;

use Client_Power_Tools\Core\Common;

// Noindexes the client dashboard because it's none of Google's business.
add_action( 'wp_head', __NAMESPACE__ . '\cpt_noindex_client_dashboard' );
function cpt_noindex_client_dashboard() {
	if ( Common\cpt_is_client_dashboard() ) {
		echo '<meta name="robots" content="noindex" />';
	}
}

add_filter( 'the_content', __NAMESPACE__ . '\cpt_client_dashboard' );
function cpt_client_dashboard( $content ) {
	if (
		! Common\cpt_is_client_dashboard() ||
		! is_main_query() ||
		! in_the_loop() ||
		has_shortcode( $content, 'client-dashboard' )
	) {
		return $content;
	}

	$dashboard = cpt_get_client_dashboard();

	if (
		Common\cpt_is_client_dashboard( 'messages' ) ||
		Common\cpt_is_client_dashboard( 'projects' ) ||
		Common\cpt_is_project()
	) {
		$content = $dashboard;
	} else {
		$content = $dashboard . $content;
	}
	return $content;
}


function cpt_get_client_dashboard( $user_id = null ) {
	if (
		! Common\cpt_is_client_dashboard()
	) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return '<p>' . sprintf(
			wp_kses_post(
				// translators: %1$s and %2$s are <a> tags for the login link.
				__( 'Please %1$slog in%2$s to view the client dashboard.', 'client-power-tools' )
			),
			/* %1$s */ '<a class="cpt-login-link" href="#">',
			/* %2$s */ '</a>'
		) . '</p>';
	}

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if (
		! 0 === $user_id ||
		! Common\cpt_is_client( $user_id )
	) {
		return '<p>' . __( 'Sorry, you don\'t have permission to view this page because your user account is missing the "Client" role.', 'client-power-tools' ) . '</p>';
	}

	// Logs the user's visit/last activity.
	update_user_meta( $user_id, 'cpt_last_activity', time() );

	$client_data = Common\cpt_get_client_data( $user_id );

	ob_start();

	cpt_nav();
	cpt_breadcrumbs();
	cpt_the_title();
	Common\cpt_get_notices();

	// Outputs the welcome message and the status update request button.
	if ( Common\cpt_is_client_dashboard( 'dashboard' ) ) {
		cpt_welcome_message( $client_data['first_name'] );

		$dashboard_page_id = intval( get_option( 'cpt_client_dashboard_page_selection' ) );
		$dashboard_content = get_the_content( null, false, $dashboard_page_id );
		if (
			get_option( 'cpt_module_status_update_req_button' ) &&
			! has_shortcode( $dashboard_content, 'status-update-request-button' )
		) {
			Common\cpt_status_update_request_button( $user_id );
		}
	}

	// Outputs the Messages page.
	if (
		get_option( 'cpt_module_messaging' ) &&
		Common\cpt_is_client_dashboard( 'messages' )
	) {
		Common\cpt_messages( $user_id );
		?>
			<div class="form-wrap cpt-new-message-form">
				<h3><?php esc_html_e( 'New Message', 'client-power-tools' ); ?></h3>
				<?php Common\cpt_new_message_form( $user_id ); ?>
			</div>
		<?php
	}

	// Outputs the Projects page.
	if (
		get_option( 'cpt_module_projects' ) &&
		Common\cpt_is_client_dashboard( 'projects' )
	) {
		// Outputs an individual project if a project post ID is specified.
		// Otherwise, outputs the list of projects.
		$projects_post_id = isset( $_REQUEST['projects_post_id'] ) ? intval( sanitize_key( $_REQUEST['projects_post_id'] ) ) : false;
		if (
			$projects_post_id &&
			Common\cpt_is_project( $projects_post_id )
		) {
			Common\cpt_get_project( $projects_post_id );
		} else {
			Common\cpt_get_projects_list();
		}
	}

	return ob_get_clean();
}


function cpt_nav() {
	remove_filter( 'the_title', 'Client_Power_Tools\Core\Frontend\cpt_client_dashboard_page_titles', 10 );
	?>
		<nav 
			id="cpt-nav" 
			class="wp-block-group has-global-padding is-layout-constrained alignfull"
		>
			<ul class="cpt-tabs menu">
				<li class="cpt-tab menu-item
					<?php
					if ( Common\cpt_is_client_dashboard( 'dashboard' ) && ! isset( $_REQUEST['tab'] ) ) {
						echo ' current-menu-item';
					}
					?>
				">
					<a href="<?php echo esc_url( Common\cpt_get_client_dashboard_url() ); ?>">
						<?php esc_html_e( 'Home', 'client-power-tools' ); ?>
					</a>
				</li>
				<?php if ( get_option( 'cpt_module_messaging' ) ) { ?>
					<li class="cpt-tab menu-item
						<?php
						if ( Common\cpt_is_client_dashboard( 'messages' ) ) {
							echo ' current-menu-item';
						}
						?>
					">
						<a href="<?php echo esc_url( add_query_arg( 'tab', 'messages', Common\cpt_get_client_dashboard_url() ) ); ?>">
							<?php esc_html_e( 'Messages', 'client-power-tools' ); ?>
						</a>
					</li>
				<?php } ?>
				<?php if ( get_option( 'cpt_module_projects' ) ) { ?>
					<li class="cpt-tab menu-item
						<?php
						if ( Common\cpt_is_client_dashboard( 'projects' ) ) {
							echo ' current-menu-item';
						}
						?>
					">
						<a href="<?php echo esc_url( add_query_arg( 'tab', 'projects', Common\cpt_get_client_dashboard_url() ) ); ?>">
							<?php echo esc_html( Common\cpt_get_projects_label( 'plural' ) ); ?>
						</a>
					</li>
				<?php } ?>
				<?php if ( get_option( 'cpt_module_knowledge_base' ) ) { ?>
					<?php
					$kb_id           = get_option( 'cpt_knowledge_base_page_selection' );
					$kb_children_ids = cpt_get_child_pages( $kb_id );

					$kb_classes = 'cpt-tab menu-item';
					if ( $kb_children_ids ) {
						$kb_classes .= ' menu-item-has-children';
					}
					if ( Common\cpt_is_knowledge_base() ) {
						$kb_classes .= ' current-menu-item';
					}
					?>
					<li class="<?php echo esc_attr( $kb_classes ); ?>">
						<a href="<?php echo esc_url( Common\cpt_get_knowledge_base_url() ); ?>">
							<?php echo esc_html( get_the_title( $kb_id ) ); ?>
						</a>
						<?php
						if ( $kb_children_ids ) {
							cpt_submenu( $kb_id );
						}
						?>
					</li>
				<?php } ?>
				<?php
				$addl_pages_ids = get_option( 'cpt_client_dashboard_addl_pages' ) ? explode( ',', get_option( 'cpt_client_dashboard_addl_pages' ) ) : false;
				if ( $addl_pages_ids ) {
					foreach ( $addl_pages_ids as $addl_page_id ) {
						$addl_page_id           = intval( trim( $addl_page_id ) );
						$addl_page_children_ids = cpt_get_child_pages( $addl_page_id );
						$addl_page_classes      = 'cpt-tab menu-item';
						if ( get_option( 'cpt_client_dashboard_addl_pages_children' ) && $addl_page_children_ids ) {
							$addl_page_classes .= ' menu-item-has-children';
						}
						if ( $addl_page_id == get_the_ID() || in_array( $addl_page_id, get_post_ancestors( get_the_ID() ) ) ) {
							$addl_page_classes .= ' current-menu-item';
						}
						?>
						<li class="<?php echo esc_attr( $addl_page_classes ); ?>">
							<a href="<?php echo esc_url( get_permalink( $addl_page_id ) ); ?>">
								<?php echo esc_html( get_the_title( $addl_page_id ) ); ?>
							</a>
							<?php
							if ( get_option( 'cpt_client_dashboard_addl_pages_children' ) && $addl_page_children_ids ) {
								cpt_submenu( $addl_page_id );
							}
							?>
						</li>
						<?php
					}
				}
				?>
			</ul>
		</nav>
	<?php
}


/**
 * Nav Submenus
 */
function cpt_get_child_pages( $page_id ) {
	if ( ! $page_id ) {
		return;
	}

	$child_pages = get_posts(
		array(
			'fields'         => 'ids',
			'order'          => 'ASC',
			'orderby'        => 'menu_order',
			'post_parent'    => $page_id,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'post_type'      => 'page',
		)
	);

	if ( $child_pages ) {
		return $child_pages;
	} else {
		return false;
	}
}

function cpt_submenu( $page_id ) {
	if ( ! $page_id ) {
		return;
	}

	$child_pages = cpt_get_child_pages( $page_id );
	if ( ! $child_pages ) {
		return;
	}

	?>
		<ul class="sub-menu">
			<?php foreach ( $child_pages as $id ) { ?>
				<?php
				$children = cpt_get_child_pages( $id );
				$classes  = array( 'menu-item' );
				if ( get_the_ID() === $id ) {
					$classes[] = 'current-menu-item';
				}
				if ( $children ) {
					$classes[] = 'menu-item-has-children';
				}
				?>
				<li class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
					<a href="<?php echo esc_url( get_permalink( $id ) ); ?>">
						<?php echo esc_html( get_the_title( $id ) ); ?>
					</a>
					<?php
					if ( $children ) {
						cpt_submenu( $id );
					}
					?>
				</li>
			<?php } ?>
		</ul>
	<?php
}

function cpt_welcome_message( $clients_first_name ) {
	?>
	<p>
		<strong>
			<?php
				printf(
					// translators: %s is the client's first name.
					esc_html__( 'Welcome back, %s!', 'client-power-tools' ),
					esc_html( $clients_first_name )
				);
			?>
		</strong>
	</p>
	<?php
}

/**
 * Knowledge Base Breadcrumbs
 */
function cpt_breadcrumbs() {
	if ( ! get_option( 'cpt_show_knowledge_base_breadcrumbs' ) ) {
		return;
	}

	if ( ! Common\cpt_is_knowledge_base() && ! Common\cpt_is_additional_page() ) {
		return;
	}

	$this_page_id  = get_the_ID();
	$parent_id     = wp_get_post_parent_id( $this_page_id );
	$breadcrumbs[] = '<span class="breadcrumb last-breadcrumb"><strong>' . get_the_title( $this_page_id ) . '</strong></span>';
	while ( $parent_id ) {
		$parent_url    = get_the_permalink( $parent_id );
		$parent_title  = get_the_title( $parent_id );
		$breadcrumbs[] = '<span class="breadcrumb"><a href="' . $parent_url . '">' . $parent_title . '</a></span>';
		$parent_id     = wp_get_post_parent_id( $parent_id );
	}
	$breadcrumbs = array_reverse( $breadcrumbs );

	?>
		<div id="cpt-breadcrumbs">
			<?php echo wp_kses_post( implode( ' / ', $breadcrumbs ) ); ?>
		</div>
	<?php
}
