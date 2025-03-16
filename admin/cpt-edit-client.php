<?php
/**
 * New client
 *
 * @file       cpt-edit-client.php
 * @package    Client_Power_Tools
 * @subpackage Core\Admin
 * @since      1.0.0
 */

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

add_action( 'admin_post_cpt_client_updated', __NAMESPACE__ . '\cpt_process_client_update' );
/**
 * Process client update
 */
function cpt_process_client_update() {
	if (
		! isset( $_POST['cpt_client_updated_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpt_client_updated_nonce'] ) ), 'cpt_client_updated' )
	) {
		exit( esc_html__( 'Invalid nonce.', 'client-power-tools' ) );
	}

	if ( ! isset( $_POST['clients_user_id'] ) ) {
		exit( esc_html__( 'Missing client ID', 'client-power-tools' ) );
	}

	$user_data = array_merge(
		Common\cpt_get_sanitized_userdata_from_post(),
		array(
			'ID' => intval( wp_unslash( $_POST['clients_user_id'] ) ),
		)
	);

	$update_client = wp_update_user( $user_data );

	if ( is_wp_error( $update_client ) ) {
		$result = 'Client could not be updated. Error message: ' . $update_client->get_error_message();
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

		$result = __( 'Client updated.', 'client-power-tools' );
	}

	set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
	wp_safe_redirect( wp_get_referer() );
	exit;
}

/**
 * Delete client modal
 *
 * @param int $clients_user_id Client's user ID.
 */
function cpt_delete_client_modal( $clients_user_id ) {
	if ( ! $clients_user_id ) {
		return;
	}
	?>
		<div id="cpt-delete-client-modal" class="cpt-admin-modal" style="display: none;">
			<div class="cpt-admin-modal-card">
				<h2 style="color: red;"><?php esc_html_e( 'WARNING' ); ?></h2>
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							// Translators: %1$s and %2$s are `<strong>` tags.
							__( '%1$sDeleting a client is permanent.%2$s There is no undo. Make sure you have a backup!', 'client-power-tools' ),
							'<strong>',
							'</strong>'
						)
					);
					?>
				</p>
				<p>
					<?php esc_html_e( 'Deleting a client will also remove the associated user account, client messages, projects, and other client information.', 'client-power-tools' ); ?>
				</p>
				<?php cpt_delete_client_button( $clients_user_id ); ?>
				<button class="button wp-element-button cpt-cancel-delete-client">
					<?php esc_html_e( 'Cancel', 'client-power-tools' ); ?>
				</button>
			</div>
		</div>
		<div class="cpt-admin-modal-screen" style="display: none;"></div>
	<?php
}

/**
 * Delete client button
 *
 * @param int $clients_user_id Client's user ID.
 */
function cpt_delete_client_button( $clients_user_id ) {
	if ( ! $clients_user_id ) {
		return;
	}

	$client_name = Common\cpt_get_client_name( $clients_user_id );
	$button_txt  = esc_html__( 'Delete', 'client-power-tools' ) . ' ' . $client_name;

	?>
		<form 
			id="cpt_delete_client_button" 
			action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" 
			method="POST"
		>
			<?php wp_nonce_field( 'cpt_client_deleted', 'cpt_client_deleted_nonce' ); ?>
			<input 
				name="action" 
				value="cpt_client_deleted" 
				type="hidden"
			>
			<input 
				name="clients_user_id" 
				value="<?php echo esc_attr( $clients_user_id ); ?>" 
				type="hidden"
			>
			<input 
				name="submit" 
				id="submit" 
				class="button button-primary wp-element-button" 
				type="submit" 
				value="<?php echo esc_attr( $button_txt ); ?>"
			>
		</form>
	<?php
}


add_action( 'admin_post_cpt_client_deleted', __NAMESPACE__ . '\cpt_process_delete_client' );
/**
 * Process client deletion
 */
function cpt_process_delete_client() {
	if (
		! isset( $_POST['cpt_client_deleted_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpt_client_deleted_nonce'] ) ), 'cpt_client_deleted' )
	) {
		exit( esc_html__( 'Invalid nonce.', 'client-power-tools' ) );
	}

	if ( ! isset( $_POST['clients_user_id'] ) ) {
		exit( esc_html__( 'Missing client ID.', 'client-power-tools' ) );
	}

	$clients_user_id  = intval( wp_unslash( $_POST['clients_user_id'] ) );
	$client_name      = Common\cpt_get_client_name( $clients_user_id );
	$cpt_messages     = get_posts(
		array(
			'fields'         => 'ids',
			'meta_key'       => 'cpt_clients_user_id',
			'meta_value'     => $clients_user_id,
			'post_type'      => 'cpt_message',
			'posts_per_page' => -1,
		)
	);
	$message_count    = $cpt_messages ? count( $cpt_messages ) : 0;
	$msg_delete_count = 0;

	foreach ( $cpt_messages as $post_id ) {
		$post_deleted = wp_delete_post( $post_id, true );
		if ( $post_deleted ) {
			++$msg_delete_count;
		}
	}

	$cpt_projects      = get_posts(
		array(
			'fields'         => 'ids',
			'meta_key'       => 'cpt_client_id',
			'meta_value'     => $clients_user_id,
			'post_type'      => 'cpt_project',
			'posts_per_page' => -1,
		)
	);
	$project_count     = $cpt_projects ? count( $cpt_projects ) : 0;
	$proj_delete_count = 0;

	foreach ( $cpt_projects as $post_id ) {
		$post_deleted = wp_delete_post( $post_id, true );
		if ( $post_deleted ) {
			++$proj_delete_count;
		}
	}

	$client_deleted = wp_delete_user( $clients_user_id );

	if ( true === $client_deleted ) {
		$result = sprintf(
			// Translators: %s is the client's name.
			__( '%s deleted.', 'client-power-tools' ),
			$client_name
		);
	} else {
		$result = __( 'Client could not be deleted.', 'client-power-tools' );
	}

	if ( $message_count > 0 ) {
		$result .= ' ' . $msg_delete_count . '/' . $message_count . __( ' messages deleted.' );
		if ( $msg_delete_count < $messager_count ) {
			$result .= ' <em>' . __( 'Not all messages could be deleted.', 'client-power-tools' ) . '</em>';
		}
	}

	if ( $project_count > 0 ) {
		$result .= ' ' . $proj_delete_count . '/' . $project_count . ' ' . strtolower( Common\cpt_get_projects_label( 'plural' ) ) . __( ' deleted.' );
		if ( $proj_delete_count < $project_count ) {
			$result .= ' <em>' . __( 'Not all projects could be deleted.', 'client-power-tools' ) . '</em>';
		}
	}

	set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
	wp_safe_redirect( remove_query_arg( 'user_id', wp_get_referer() ) );
	exit;
}
