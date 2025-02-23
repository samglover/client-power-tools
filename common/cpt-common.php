<?php
/**
 * Common functions
 *
 * @file       cpt-common.php
 * @package    Client_Power_Tools
 * @subpackage Core\Common
 * @since      1.0.0
 */

namespace Client_Power_Tools\Core\Common;

add_action( 'init', __NAMESPACE__ . '\cpt_add_roles' );
/**
 * Adds the Client and Client Manager user roles and capabilities and assigns all CPT capabilities to admins.
 */
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


/**
 * Checks to see whether the current page/page is part of the client dashboard.
 *
 * @param string|bool $page_slug Optional. Dashboard page to check. Possible values:
 * - 'home'
 * - 'messages'
 * - 'projects'
 * - 'knowledge-base'
 * - 'additional-pages'
 * Default is false.
 * @since 1.10.4 Refactored
 * @return string|bool If $page_slug is specified, returns true or false. Otherwise returns the dashboard page slug (same options as $page_slug, above) if true.
 */
function cpt_is_client_dashboard( $page_slug = false ) {
	global $wp_query;
	if ( ! isset( $wp_query->post->ID ) ) {
		return false;
	}

	$this_page_id      = $wp_query->post->ID;
	$dashboard_page_id = intval( get_option( 'cpt_client_dashboard_page_selection' ) );

	if ( $this_page_id === $dashboard_page_id ) {
		if ( isset( $_REQUEST['tab'] ) ) {
			$dashboard_page = sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) );
		} else {
			$dashboard_page = 'home';
		}
	} elseif ( cpt_is_knowledge_base() ) {
		$dashboard_page = 'knowlege-base';
	} elseif ( cpt_is_additional_page() ) {
		$dashboard_page = 'additional-pages';
	}

	if (
		$page_slug
		&& $page_slug === $dashboard_page
	) {
		return true;
	} elseif ( ! $page_slug ) {
		return $dashboard_page;
	}

	return false;
}

/**
 * Checks to see whether the current post is a project.
 *
 * @param int $post_id Optional. ID of the post to check. Default is the current post ID.
 * @return bool
 */
function cpt_is_project( $post_id = false ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return false;
		}
	}

	if ( 'cpt_project' === get_post_type( $post_id ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Checks to see whether the current post is a knowledge base page or descendant.
 *
 * @return bool
 */
function cpt_is_knowledge_base() {
	global $wp_query;
	if ( ! isset( $wp_query->post->ID ) ) {
		return false;
	}

	$this_page_id        = $wp_query->post->ID;
	$knowledge_base_id   = intval( get_option( 'cpt_knowledge_base_page_selection' ) );
	$this_page_ancestors = get_post_ancestors( $this_page_id );

	if (
		$this_page_id === $knowledge_base_id
		|| (
			$this_page_ancestors
			&& in_array( $knowledge_base_id, $this_page_ancestors, true )
		)
	) {
		return true;
	} else {
		return false;
	}
}


/**
 * Checks to see whether the current post is an additional page or descendant.
 *
 * @return bool
 */
function cpt_is_additional_page() {
	global $wp_query;
	if ( ! isset( $wp_query->post->ID ) ) {
		return false;
	}

	$this_page_id     = $wp_query->post->ID;
	$addl_pages_array = get_option( 'cpt_client_dashboard_addl_pages' );

	if ( ! $addl_pages_array ) {
		return false;
	}

	$addl_pages_array = explode( ',', $addl_pages_array );
	foreach ( $addl_pages_array as $page_id ) {
		$page_id = intval( trim( $page_id ) );

		if (
			$page_id === $this_page_id
			|| in_array( $page_id, get_post_ancestors( $this_page_id ), true )
		) {
			return true;
		}
	}
}


/**
 * Checks to see whether the current user is a client.
 *
 * @param int|bool $user_id Optional. ID of the user to check. Default is false.
 * @return bool
 */
function cpt_is_client( $user_id = false ) {
	if ( ! $user_id && ! is_user_logged_in() ) {
		return false;
	}
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}
	}

	$user = get_userdata( $user_id );

	if (
		$user &&
		$user->roles &&
		in_array( 'cpt-client', $user->roles, true )
	) {
		return true;
	} else {
		return false;
	}
}


/**
 * Gets clients
 *
 * @see get_users()
 * @param array $args Optional. Arguments to retrieve users.
 * @return array List of clients.
 */
function cpt_get_clients( $args = array() ) {
	if ( isset( $_REQUEST['orderby'] ) ) {
		$orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
	} else {
		$orderby = 'display_name';
	}

	if ( isset( $_REQUEST['order'] ) ) {
		$order = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
	} else {
		$order = 'ASC';
	}

	$client_query_args = array(
		'role'    => 'cpt-client',
		'orderby' => $orderby,
		'order'   => $order,
	);
	$client_query_args = array_merge( $client_query_args, $args );
	$clients           = get_users( $client_query_args );
	return $clients;
}


/**
 * Gets a client's profile URL.
 *
 * @param int $clients_user_id User ID of the client to check.
 * @return url|void
 */
function cpt_get_client_profile_url( $clients_user_id ) {
	if ( ! $clients_user_id ) {
		return;
	}
	return add_query_arg( 'user_id', $clients_user_id, admin_url( 'admin.php?page=cpt' ) );
}


/**
 * Gets the client user data keys for use with wp_insert_user() and wp_update_user().
 *
 * @since 1.10.4
 * @return array User data keys.
 */
function cpt_get_client_userdata_keys() {
	return array(
		'ID',
		'client_name',
		'first_name',
		'last_name',
		'display_name',
		'user_email',
	);
}

/**
 * Sanitizes user data from $_POST.
 *
 * @since 1.10.4
 * @return array Sanitized user data.
 */
function cpt_get_sanitized_userdata_from_post() {
	$data_keys          = cpt_get_client_userdata_keys();
	$sanitized_userdata = array();

	foreach ( $data_keys as $data_key ) {
		if (
			( 'user_email' !== $data_key && ! isset( $_POST[ $data_key ] ) )
			|| ( 'user_email' === $data_key && ! isset( $_POST['email'] ) )
		) {
			continue;
		}

		switch ( $data_key ) {
			case 'ID':
				$sanitized_value = intval( wp_unslash( $_POST[ $data_key ] ) );
				break;
			case 'user_email':
				// CPT uses 'email' in form fields.
				$sanitized_value = sanitize_email( wp_unslash( $_POST['email'] ) );
				break;
			default:
				$sanitized_value = sanitize_text_field( wp_unslash( $_POST[ $data_key ] ) );
		}

		$sanitized_userdata[ $data_key ] = $sanitized_value;
	}

	return $sanitized_userdata;
}


/**
 * Gets the client user meta keys for use with update_user_meta().
 *
 * @since 1.10.4
 * @return array User meta keys.
 */
function cpt_get_client_usermeta_keys() {
	return array(
		'client_id',
		'client_name',
		'email_ccs',
		'client_manager',
		'client_status',
	);
}

/**
 * Sanitizes user meta from $_POST.
 *
 * @since 1.10.4
 * @return array Sanitized user meta.
 */
function cpt_get_sanitized_usermeta_from_post() {
	$meta_keys          = cpt_get_client_usermeta_keys();
	$sanitized_usermeta = array();

	foreach ( $meta_keys as $meta_key ) {
		if ( ! isset( $_POST[ $meta_key ] ) ) {
			continue;
		}

		switch ( $meta_key ) {
			case 'client_id':
			case 'client_manager':
				$sanitized_value = intval( wp_unslash( $_POST[ $meta_key ] ) );
				break;
			case 'email_ccs':
				$sanitized_textarea = sanitize_textarea_field( wp_unslash( $_POST[ $meta_key ] ) );
				$array_of_emails    = cpt_cleanse_array_of_emails( explode( PHP_EOL, $sanitized_textarea ) );
				$sanitized_value    = implode( PHP_EOL, $array_of_emails );
				break;
			default:
				$sanitized_value = sanitize_text_field( wp_unslash( $_POST[ $meta_key ] ) );
		}

		$sanitized_usermeta[ 'cpt_' . $meta_key ] = $sanitized_value;
	}

	return $sanitized_usermeta;
}


/**
 * Gets the URL of the client dashboard page.
 *
 * @return url
 */
function cpt_get_client_dashboard_url() {
	$page_id = get_option( 'cpt_client_dashboard_page_selection' );
	return get_permalink( $page_id );
}


/**
 * Gets the URL of the knowledge base page.
 *
 * @return url
 */
function cpt_get_knowledge_base_url() {
	$page_id = get_option( 'cpt_knowledge_base_page_selection' );
	return get_permalink( $page_id );
}


/**
 * Gets a client's name. Uses the cpt_client_name field if set. Otherwise, gets the name from cpt_get_display_name().
 *
 * @param int $user_id Client's user ID.
 * @return string Client's full name.
 */
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


/**
 * Gets a user's display name, which is either first name + last name or the user's display name.
 *
 * @param int $user_id Client's user ID.
 * @return string Client's display name.
 */
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


/**
 * Filter hook for custom client fields.
 */
function cpt_custom_client_fields() {
	return apply_filters( 'cpt_custom_fields', array() );
}


/**
 * Sanitizes custom fields from $_POST.
 *
 * @since 1.10.4
 * @return array Sanitized fields.
 */
function cpt_get_sanitized_custom_fields_from_post() {
	$custom_fields = cpt_custom_client_fields();

	if ( ! $custom_fields ) {
		return false;
	}

	foreach ( $custom_fields as $field ) {
		if ( ! isset( $_POST[ $field['id'] ] ) ) {
			continue;
		}

		switch ( $field['type'] ) {
			case 'email':
				$sanitized_value = sanitize_email( wp_unslash( $_POST[ $field['id'] ] ) );
				break;
			case 'url':
				$sanitized_value = sanitize_url( wp_unslash( $_POST[ $field['id'] ] ) );
				break;
			default:
				$sanitized_value = sanitize_text_field( wp_unslash( $_POST[ $field['id'] ] ) );
		}

		$sanitized_custom_fields[ $field['id'] ] = $sanitized_value;
	}

	return $sanitized_custom_fields;
}


/**
 * Gets a client's data.
 *
 * @param int $clients_user_id The client's user ID.
 * @return array Client details.
 */
function cpt_get_client_data( $clients_user_id ) {
	if (
		! $clients_user_id
		|| ! cpt_is_client( $clients_user_id )
	) {
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


/**
 * Gets a client manager ID from a client's ID.
 *
 * @param int $clients_user_id Client's user ID.
 * @return int|bool Client manager's ID on success. False on failure.
 */
function cpt_get_client_manager_id( $clients_user_id ) {
	if ( ! $clients_user_id ) {
		return;
	}

	$client_manager = get_user_meta( $clients_user_id, 'cpt_client_manager', true );

	return $client_manager ? $client_manager : false;
}


/**
 * Gets a client manager's email from a client's ID.
 *
 * @param int $clients_user_id Client's user ID.
 * @return stromg|bool Client manager's email on success. False on failure.
 */
function cpt_get_client_manager_email( $clients_user_id ) {
	if ( ! $clients_user_id ) {
		return;
	}

	$userdata = get_userdata( get_user_meta( $clients_user_id, 'cpt_client_manager', true ) );

	return isset( $userdata->user_email ) ? $userdata->user_email : false;
}


/**
 * Gets the email "card" HTML.
 *
 * @param string $title The card title.
 * @param string $content The card content HTML.
 * @param string $button_txt Button text label.
 * @param string $button_url Button URL.
 * @return string Email card HTML.
 */
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
	<div 
		class="cpt-card" 
		align="left" 
		style="<?php echo esc_attr( $card_style ); ?>"
	>
		<?php if ( ! empty( $title ) ) { ?>
			<h2 style="<?php echo esc_attr( $h2_style ); ?>">
				<?php echo esc_html( $title ); ?>
			</h2>
		<?php } ?>
		<?php
		if ( ! empty( $content ) ) {
			echo wp_kses_post( $content );
		}
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


add_action( 'admin_notices', __NAMESPACE__ . '\cpt_get_notices' );
/**
 * Checks for a transient with the results of an action. If one exists, outputs a notice. In the admin the output is a standard WordPress admin notice. On the front end, the output is a modal.
 */
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


/**
 * Sanitizes an array of emails.
 *
 * @param array $array_of_emails The array to be cleansed.
 * @return array Cleansed array of emails.
 */
function cpt_cleanse_array_of_emails( $array_of_emails ) {
	if ( ! $array_of_emails || ! is_array( $array_of_emails ) ) {
		return;
	}
	foreach ( $array_of_emails as $key => $val ) {
		if ( empty( trim( $val ) ) ) {
			unset( $array_of_emails[ $key ] );
		} else {
			$array_of_emails[ $key ] = sanitize_email( trim( $val ) );
		}
	}
	return $array_of_emails;
}


/**
 * Sanitizes an array of strings.
 *
 * @param array $array_of_strings The array to be cleansed.
 * @return array Cleansed array of strings.
 */
function cpt_cleanse_array_of_strings( $array_of_strings ) {
	if ( ! $array_of_strings || ! is_array( $array_of_strings ) ) {
		return;
	}
	foreach ( $array_of_strings as $key => $val ) {
		if ( empty( trim( $val ) ) ) {
			unset( $array_of_strings[ $key ] );
		} else {
			$array_of_strings[ $key ] = sanitize_text_field( trim( $val ) );
		}
	}
	return $array_of_strings;
}


/**
 * Converts an array to a comma-separated inline list. Uses the serial comma.
 *
 * @param array $array_of_items The array to be converted.
 * @return string Comma-separated list.
 */
function cpt_array_to_strlist( $array_of_items ) {
	if ( ! $array_of_items || ! is_array( $array_of_items ) ) {
		return;
	}

	$list = '';
	switch ( count( $array_of_items ) ) {
		case 0:
			return false;
		case 1:
			return trim( $array_of_items[0] );
		case 2:
			return trim( $array_of_items[0] ) . ' ' . __( 'and' ) . ' ' . $array_of_items[1];
		case ( count( $array_of_items ) >= 3 ):
			foreach ( $array_of_items as $key => $val ) {
				$list .= trim( $val );
				if ( $key < count( $array_of_items ) - 2 ) {
					$list .= ', ';
				} elseif ( $key < count( $array_of_items ) - 1 ) {
					$list .= ', and ';
				}
			}
	}

	return $array_of_items;
}
