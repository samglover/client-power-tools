<?php

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

add_action( 'admin_post_cpt_client_updated', __NAMESPACE__ . '\cpt_process_client_update' );
function cpt_process_client_update() {
	if ( isset( $_POST['cpt_client_updated_nonce'] ) && wp_verify_nonce( $_POST['cpt_client_updated_nonce'], 'cpt_client_updated' ) ) {
		$clients_user_id = sanitize_key( intval( $_POST['clients_user_id'] ) );

		$userdata = array(
			'ID'           => $clients_user_id,
			'client_name'  => sanitize_text_field( $_POST['client_name'] ),
			'first_name'   => sanitize_text_field( $_POST['first_name'] ),
			'last_name'    => sanitize_text_field( $_POST['last_name'] ),
			'display_name' => sanitize_text_field( $_POST['first_name'] ) . ' ' . sanitize_text_field( $_POST['last_name'] ),
			'user_email'   => sanitize_email( $_POST['email'] ),
		);

		$clients_user_id = wp_update_user( $userdata );

		if ( is_wp_error( $clients_user_id ) ) {
			$result = 'Client could not be updated. Error message: ' . $clients_user_id->get_error_message();
		} else {
			$client_id      = sanitize_text_field( $_POST['client_id'] );
			$client_name    = sanitize_text_field( $_POST['client_name'] );
			$email_ccs      = sanitize_textarea_field( $_POST['email_ccs'] );
			$client_manager = sanitize_text_field( $_POST['client_manager'] );
			$client_status  = sanitize_text_field( $_POST['client_status'] );

			update_user_meta( $clients_user_id, 'cpt_client_id', $client_id );
			update_user_meta( $clients_user_id, 'cpt_client_name', $client_name );
			update_user_meta( $clients_user_id, 'cpt_email_ccs', $email_ccs );
			update_user_meta( $clients_user_id, 'cpt_client_manager', $client_manager );
			update_user_meta( $clients_user_id, 'cpt_client_status', $client_status );

			$custom_fields = Common\cpt_custom_client_fields();
			if ( $custom_fields ) {
				foreach ( $custom_fields as $field ) {
					update_user_meta( $clients_user_id, $field['id'], sanitize_text_field( $_POST[ $field['id'] ] ) );
				}
			}

			$result = 'Client updated.';
		}

		set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
		wp_redirect( $_POST['_wp_http_referer'] );
		exit;
	} else {
		die();
	}
}


function cpt_delete_client_modal( $clients_user_id ) {
	if ( ! $clients_user_id ) {
		return;
	}
	?>
		<div id="cpt-delete-client-modal" class="cpt-admin-modal" style="display: none;">
			<div class="cpt-admin-modal-card">
				<h2 style="color: red;"><?php esc_html_e( 'WARNING' ); ?></h2>
				<p><?php echo wp_kses_post( '<strong>Deleting a client is permanent.</strong> There is no undo. Make sure you have a backup!' ); ?></p>
				<p><?php echo wp_kses_post( 'Deleting a client will also remove the associated user account, client messages, projects, and other client information.' ); ?></p>
				<?php cpt_delete_client_button( $clients_user_id ); ?>
				<button class="button wp-element-button cpt-cancel-delete-client">
					<?php esc_html_e( 'Cancel' ); ?>
				</button>
			</div>
		</div>
		<div class="cpt-admin-modal-screen" style="display: none;"></div>
	<?php
}


function cpt_delete_client_button( $clients_user_id ) {
	if ( ! $clients_user_id ) {
		return;
	}

	$client_name = Common\cpt_get_client_name( $clients_user_id );
	$button_txt  = esc_html__( 'Delete' ) . ' ' . $client_name;

	?>
		<form id="cpt_delete_client_button" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">
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
function cpt_process_delete_client() {
	if ( isset( $_POST['cpt_client_deleted_nonce'] ) && wp_verify_nonce( $_POST['cpt_client_deleted_nonce'], 'cpt_client_deleted' ) ) {
		$clients_user_id = sanitize_key( intval( $_POST['clients_user_id'] ) );
		$client_name     = Common\cpt_get_client_name( $clients_user_id );

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

		if ( $client_deleted == true ) {
			$result = $client_name . __( ' deleted.' );
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
		wp_redirect( remove_query_arg( 'user_id', $_POST['_wp_http_referer'] ) );
		exit;
	} else {
		die();
	}
}
