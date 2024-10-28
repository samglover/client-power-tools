<?php

namespace Client_Power_Tools\Core\Common;

/**
 * Adds the Client and Client Manager user roles and capabilities, and assigns
 * all CPT capabilities to admins.
 */
add_action( 'init', __NAMESPACE__ . '\cpt_add_roles' );
function cpt_add_roles() {
	add_role(
		'cpt-client',
		'Client'
	);

	add_role(
		'cpt-client-manager',
		'Client Manager',
		array(
			'cpt_view_clients'  => true,
			'cpt_view_projects' => true,
			'read'              => true,
		)
	);

	$admin = get_role( 'administrator' );
	$admin->add_cap( 'cpt_view_clients' );
	$admin->add_cap( 'cpt_manage_clients' );
	$admin->add_cap( 'cpt_manage_team' );
	$admin->add_cap( 'cpt_view_projects' );
	$admin->add_cap( 'cpt_manage_projects' );
	$admin->add_cap( 'cpt_manage_settings' );
}


function cpt_is_client_dashboard( $tab_slug = false ) {
	global $wp_query;
	if (
		! isset( $wp_query->post->ID )
	) {
		return false;
	}

	$this_page_id = $wp_query->post->ID;
	if ( ! is_page( $this_page_id ) ) {
		return false;
	}

	$dashboard_page_id = intval( get_option( 'cpt_client_dashboard_page_selection' ) );

	if ( $tab_slug ) {
		$request_tab      = isset( $_REQUEST['tab'] ) ? sanitize_key( $_REQUEST['tab'] ) : false;
		$projects_post_id = isset( $_REQUEST['projects_post_id'] ) ? intval( sanitize_key( $_REQUEST['projects_post_id'] ) ) : false;
		switch ( $tab_slug ) {
			case 'dashboard':
				if (
					! $request_tab &&
					$this_page_id === $dashboard_page_id
				) {
					return 'dashboard';
				}
				break;
			case 'projects':
				if (
					$request_tab &&
					'projects' === $request_tab
				) {
					return 'projects';
				}
				break;
			case 'messages':
				if (
					$request_tab &&
					'messages' === $request_tab
				) {
					return 'messages';
				}
				break;
			case 'knowledge base':
				if ( cpt_is_knowledge_base() ) {
					return 'knowledge-base';
				}
				break;
			case 'additional page' || 'additional pages':
				if ( cpt_is_additional_page() ) {
					return 'additional-pages';
				}
				break;
		}
	} elseif ( $this_page_id === $dashboard_page_id ) {
		return 'dashboard';
	} elseif ( cpt_is_knowledge_base() ) {
		return 'knowledge-base';
	} elseif ( cpt_is_additional_page() ) {
		return 'additional-pages';
	}
	return false;
}


function cpt_is_project( $post_id = false ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id ) {
		return false;
	}

	if ( 'cpt_project' === get_post_type( $post_id ) ) {
		return true;
	} else {
		return false;
	}
}


function cpt_is_knowledge_base() {
	global $wp_query;
	if ( ! isset( $wp_query->post->ID ) ) {
		return false;
	}

	$this_page_id        = $wp_query->post->ID;
	$knowledge_base_id   = intval( get_option( 'cpt_knowledge_base_page_selection' ) );
	$this_page_ancestors = get_post_ancestors( $this_page_id );

	if (
		$this_page_id === $knowledge_base_id ||
		(
			$this_page_ancestors &&
			in_array( $knowledge_base_id, $this_page_ancestors, true )
		)
	) {
		return true;
	}
	return false;
}


function cpt_is_additional_page() {
	global $wp_query;
	if ( ! isset( $wp_query->post->ID ) ) {
		return false;
	}

	$this_page_id     = $wp_query->post->ID;
	$addl_pages_array = get_option( 'cpt_client_dashboard_addl_pages' ) ? get_option( 'cpt_client_dashboard_addl_pages' ) : false;

	if ( $addl_pages_array ) {
		$is_addl_page     = false;
		$addl_pages_array = explode( ',', $addl_pages_array );
		foreach ( $addl_pages_array as $key => $page_id ) {
			$page_id = intval( trim( $page_id ) );
			if (
				$page_id === $this_page_id ||
				in_array( $page_id, get_post_ancestors( $this_page_id ), true )
			) {
				$is_addl_page = true;
			}
		}
		if ( $is_addl_page ) {
			return true;
		}
	}
	return false;
}


/**
 * Checks to see whether the current user is a client. Returns true if the current
 * user has the cpt-client role, false if not.
 *
 * If no user ID is provided, checks to see whether the current user is logged-in with the
 * cpt-client role.
 */
function cpt_is_client( $user_id = null ) {
	if ( ! $user_id && ! is_user_logged_in() ) {
		return false;
	}
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	$user = get_userdata( $user_id );

	if (
		$user &&
		$user->roles &&
		in_array( 'cpt-client', $user->roles )
	) {
		return true;
	} else {
		return false;
	}
}


function cpt_get_clients( $args = array() ) {
	$client_query_args = array(
		'role'    => 'cpt-client',
		'orderby' => isset( $_REQUEST['orderby'] ) ? sanitize_key( $_REQUEST['orderby'] ) : 'display_name',
		'order'   => isset( $_REQUEST['order'] ) ? sanitize_key( $_REQUEST['order'] ) : 'ASC',
	);
	$client_query_args = array_merge( $client_query_args, $args );
	$clients           = get_users( $client_query_args );
	return $clients;
}


function cpt_get_client_profile_url( $clients_user_id ) {
	if ( ! $clients_user_id ) {
		return;
	}
	return add_query_arg( 'user_id', $clients_user_id, admin_url( 'admin.php?page=cpt' ) );
}


function cpt_get_client_dashboard_url() {
	$page_id = get_option( 'cpt_client_dashboard_page_selection' );
	return get_permalink( $page_id );
}


function cpt_get_knowledge_base_url() {
	$page_id = get_option( 'cpt_knowledge_base_page_selection' );
	return get_permalink( $page_id );
}


// Requires the primary contact's user ID because the client object IS the primary contact.
function cpt_get_client_name( $user_id ) {
	if ( ! $user_id ) {
		return;
	}
	$client_name = get_user_meta( $user_id, 'cpt_client_name', true );
	if ( ! $client_name ) {
		$client_name = cpt_get_display_name( $user_id );
	}
	return $client_name;
}


function cpt_get_display_name( $user_id ) {
	if ( ! $user_id ) {
		return;
	}
	$userdata = get_userdata( $user_id );
	if ( ! $userdata ) {
		return;
	}
	if ( isset( $userdata->first_name ) && isset( $userdata->last_name ) ) {
		$name = $userdata->first_name . ' ' . $userdata->last_name;
	} else {
		$name = $userdata->display_name;
	}
	return $name;
}


function cpt_custom_client_fields() {
	return apply_filters( 'cpt_custom_fields', array() );
}


// Returns an array with the user's details.
function cpt_get_client_data( $clients_user_id ) {
	if ( ! $clients_user_id ) {
		return;
	}
	if ( ! cpt_is_client( $clients_user_id ) ) {
		return;
	}
	$userdata    = get_userdata( $clients_user_id );
	$client_data = array(
		'user_id'       => $clients_user_id,
		'client_name'   => cpt_get_client_name( $clients_user_id ),
		'first_name'    => get_user_meta( $clients_user_id, 'first_name', true ),
		'last_name'     => get_user_meta( $clients_user_id, 'last_name', true ),
		'email'         => $userdata->user_email,
		'email_ccs'     => get_user_meta( $clients_user_id, 'cpt_email_ccs', true ),
		'client_id'     => get_user_meta( $clients_user_id, 'cpt_client_id', true ),
		'manager_id'    => cpt_get_client_manager_id( $clients_user_id ),
		'manager_email' => cpt_get_client_manager_email( $clients_user_id ),
		'status'        => get_user_meta( $clients_user_id, 'cpt_client_status', true ),
	);

	$custom_fields = cpt_custom_client_fields();
	if ( $custom_fields ) {
		foreach ( $custom_fields as $field ) {
			$client_data[ $field['id'] ] = get_user_meta( $clients_user_id, $field['id'], true );
		}
	}
	return $client_data;
}


function cpt_get_client_manager_id( $clients_user_id ) {
	if ( ! $clients_user_id ) {
		return;
	}
	$client_manager = get_user_meta( $clients_user_id, 'cpt_client_manager', true );
	return $client_manager ? $client_manager : false;
}


function cpt_get_client_manager_email( $clients_user_id ) {
	if ( ! $clients_user_id ) {
		return;
	}
	$userdata = get_userdata( get_user_meta( $clients_user_id, 'cpt_client_manager', true ) );
	return isset( $userdata->user_email ) ? $userdata->user_email : false;
}


function cpt_get_email_card(
	$title = null,
	$content = null,
	$button_txt = 'Go',
	$button_url = null
) {
	$card_style   = 'border: 1px solid #ddd; box-sizing: border-box; font-family: Jost, Helvetica, Arial, sans-serif; margin: 30px 3px 30px 0; padding: 30px; max-width: 500px;';
	$h2_style     = 'margin-top: 0;';
	$button_style = 'background-color: #eee; border: 1px solid #ddd; box-sizing: border-box; display: block; margin: 0; padding: 1em; width: 100%; text-align: center;';

	ob_start();
	?>
			<div class="cpt-card" align="left" style="<?php echo esc_attr( $card_style ); ?>">
				<?php if ( ! empty( $title ) ) { ?>
					<h2 style="<?php echo esc_attr( $h2_style ); ?>"><?php echo esc_html( $title ); ?></h2>
				<?php } ?>
				<?php
				if ( ! empty( $content ) ) {
					echo wp_kses_post( $content );}
				?>
				<?php if ( ! empty( $button_url ) ) { ?>
					<a 
						class="button" 
						href="<?php echo esc_url( $button_url ); ?>" 
						style="<?php echo esc_attr( $button_style ); ?>"
					>
						<?php echo esc_html( $button_txt ); ?>
					</a>
				<?php } ?>
			</div>
		<?php
		return ob_get_clean();
}


/**
 * Checks for a transient with the results of an action, and if one exists,
 * outputs a notice. In the admin, this is a standard WordPress admin notice. On
 * the front end, this is a modal.
 */
add_action( 'admin_notices', __NAMESPACE__ . '\cpt_get_notices' );
function cpt_get_notices() {
	$transient = 'cpt_notice_for_user_' . get_current_user_id();
	$notice    = get_transient( $transient );
	if ( ! $notice ) {
		return;
	}

	$classes = array(
		'cpt-notice',
	);
	if ( is_admin() ) {
		$classes[] = 'notice';
		$classes[] = 'is-dismissible';
	}
	if ( ! is_admin() ) {
		$classes[] = 'card';
		$classes[] = 'visible';
	}
	$classes[] = is_wp_error( $notice ) ? 'notice-error' : 'notice-success';
	?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php if ( ! is_admin() ) { ?>
				<button class="cpt-dismiss-button cpt-notice-dismiss"></button>
			<?php } ?>
			<p class="cpt-notice-message"><?php echo wp_kses_post( $notice ); ?></p>
		</div>
	<?php
	delete_transient( $transient );
}


function cpt_cleanse_array_of_emails( $array ) {
	if ( ! $array || ! is_array( $array ) ) {
		return;
	}
	foreach ( $array as $key => $val ) {
		if ( empty( trim( $val ) ) ) {
			unset( $array[ $key ] );
		} else {
			$array[ $key ] = sanitize_email( trim( $val ) );
		}
	}
	return $array;
}


function cpt_cleanse_array_of_strings( $array ) {
	if ( ! $array || ! is_array( $array ) ) {
		return;
	}
	foreach ( $array as $key => $val ) {
		if ( empty( trim( $val ) ) ) {
			unset( $array[ $key ] );
		} else {
			$array[ $key ] = sanitize_text_field( trim( $val ) );
		}
	}
	return $array;
}


function cpt_array_to_strlist( $array ) {
	if ( ! $array || ! is_array( $array ) ) {
		return;
	}
	$list = '';
	switch ( count( $array ) ) {
		case 0:
			return false;
		case 1:
			return trim( $array[0] );
		case 2:
			return trim( $array[0] ) . ' ' . __( 'and' ) . ' ' . $array[1];
		case ( count( $array ) >= 3 ):
			foreach ( $array as $key => $val ) {
				$list .= trim( $val );
				if ( $key < count( $array ) - 2 ) {
					$list .= ', ';
				} elseif ( $key < count( $array ) - 1 ) {
					$list .= ', and ';
				}
			}
	}
	return $list;
}
