<?php

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

function cpt_edit_project( $projects_post_id ) {
	if ( ! $projects_post_id || ! is_user_logged_in() ) {
		return;
	}
	$project_data = Common\cpt_get_project_data( $projects_post_id );

	if ( is_admin() && current_user_can( 'cpt_manage_projects' ) ) {
		if ( get_post_status( $projects_post_id ) !== 'trash' ) {
			?>
				<button class="button wp-element-button cpt-click-to-expand">
					<?php
						printf(
							// translators: %s is the projects label.
							esc_html__( 'Edit %s', 'client-power-tools' ),
							esc_html( Common\cpt_get_projects_label( 'singular' ) )
						);
					?>
				</button>
				<div class="cpt-this-expands">
					<div class="form-wrap form-wrap-edit_project">
						<h2>
							<?php
								printf(
									// translators: %s is the projects label.
									esc_html__( 'Edit This %s', 'client-power-tools' ),
									esc_html( Common\cpt_get_projects_label( 'singular' ) )
								);
							?>
						</h2>
						<?php include CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-edit-project-form.php'; ?>
						<?php cpt_delete_project_button( $projects_post_id ); ?>
					</div>
				</div>
			<?php
		} else {
			?>
				<div class="cpt-row gap-sm">
					<?php
						echo esc_html( cpt_undelete_project_button( $projects_post_id ) );
						echo esc_html( cpt_permadelete_project_button( $projects_post_id ) );
					?>
				</div>
			<?php
		}
	}
}

add_action( 'admin_post_cpt_project_updated', __NAMESPACE__ . '\cpt_process_project_update' );
function cpt_process_project_update() {
	if ( isset( $_POST['cpt_project_updated_nonce'] ) && wp_verify_nonce( $_POST['cpt_project_updated_nonce'], 'cpt_project_updated' ) ) {
		$projects_post_id = sanitize_key( intval( $_POST['projects_post_id'] ) );
		$project_data     = array(
			'ID'         => $projects_post_id,
			'post_title' => sanitize_text_field( $_POST['project_name'] ),
			'meta_input' => array(
				'cpt_project_id'     => sanitize_text_field( $_POST['project_id'] ),
				'cpt_project_status' => sanitize_text_field( $_POST['project_status'] ),
				'cpt_project_type'   => intval( $_POST['project_type'] ),
				'cpt_project_stage'  => sanitize_text_field( $_POST['project_stage'] ),
				'cpt_client_id'      => sanitize_text_field( $_POST['client_id'] ),
			),
		);

		$update_project = wp_update_post( $project_data );

		$result = 'Project updated.';
		if ( is_wp_error( $update_project ) ) {
			$result = 'Project could not be updated. Error message: ' . $update_project->get_error_message();
		}
		set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );

		wp_redirect( $_POST['_wp_http_referer'] );
		exit;
	} else {
		die();
	}
}


function cpt_delete_project_button( $projects_post_id ) {
	if ( ! $projects_post_id ) {
		return;
	}
	?>
		<form 
			id="cpt_delete_project_button" 
			action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" 
			method="POST"
		>
			<?php wp_nonce_field( 'cpt_project_deleted', 'cpt_project_deleted_nonce' ); ?>
			<input 
				name="action" 
				value="cpt_project_deleted" 
				type="hidden"
			>
			<input 
				name="projects_post_id" 
				value="<?php echo esc_attr( $projects_post_id ); ?>" 
				type="hidden"
			>
			<input 
				name="submit" 
				id="submit" 
				type="submit" 
				value="<?php echo esc_attr( sprintf( __( 'Delete this %s', 'client-power-tools' ), Common\cpt_get_projects_label( 'singular' ) ) ); ?>"
			>
		</form>
	<?php
}

add_action( 'admin_post_cpt_project_deleted', __NAMESPACE__ . '\cpt_process_delete_project' );
function cpt_process_delete_project() {
	if ( isset( $_POST['cpt_project_deleted_nonce'] ) && wp_verify_nonce( $_POST['cpt_project_deleted_nonce'], 'cpt_project_deleted' ) ) {
		$projects_post_id = sanitize_key( intval( $_POST['projects_post_id'] ) );
		$project_deleted  = wp_trash_post( $projects_post_id );

		if ( $project_deleted == true ) {
			$result = sprintf(
				// translators: %s is the projects label.
				esc_html__( '%s moved to the trash.', 'client-power-tools' ),
				esc_html( Common\cpt_get_projects_label( 'singular' ) )
			) . cpt_undelete_project_button( $projects_post_id );
		} else {
			$result = sprintf(
				// translators: %s is the projects label.
				esc_html__( '%s could not be moved to the trash.', 'client-power-tools' ),
				esc_html( Common\cpt_get_projects_label( 'singular' ) )
			);
		}

		set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
		wp_redirect( remove_query_arg( 'projects_post_id', $_POST['_wp_http_referer'] ) );
		exit;
	} else {
		die();
	}
}


function cpt_undelete_project_button( $projects_post_id ) {
	if ( ! $projects_post_id ) {
		return;
	}
	ob_start();
	?>
		<form 
			id="cpt_undelete_project_button" 
			action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" 
			method="POST"
		>
			<?php wp_nonce_field( 'cpt_project_undeleted', 'cpt_project_undeleted_nonce' ); ?>
			<input 
				name="action" 
				value="cpt_project_undeleted" 
				type="hidden"
			>
			<input 
				name="projects_post_id" 
				value="<?php echo esc_attr( $projects_post_id ); ?>" 
				type="hidden"
			>
			<input 
				name="submit" 
				id="submit" 
				type="submit" 
				value="<?php esc_attr_e( 'Restore', 'client-power-tools' ); ?>"
			>
		</form>
	<?php
	return ob_get_clean();
}

add_action( 'admin_post_cpt_project_undeleted', __NAMESPACE__ . '\cpt_process_undelete_project' );
function cpt_process_undelete_project() {
	if ( isset( $_POST['cpt_project_undeleted_nonce'] ) && wp_verify_nonce( $_POST['cpt_project_undeleted_nonce'], 'cpt_project_undeleted' ) ) {
		$projects_post_id  = sanitize_key( intval( $_POST['projects_post_id'] ) );
		$project_undeleted = wp_update_post(
			array(
				'ID'          => $projects_post_id,
				'post_status' => 'publish',
			)
		);

		if ( true === $project_undeleted ) {
			$result = sprintf(
				// translators: %s is the projects label.
				esc_html__( '%s restored.', 'client-power-tools' ),
				esc_html( Common\cpt_get_projects_label( 'singular' ) )
			);
		} else {
			$result = sprintf(
				// translators: %s is the projects label.
				esc_html__( '%s could not be restored.', 'client-power-tools' ),
				esc_html( Common\cpt_get_projects_label( 'singular' ) )
			);
		}

		set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
		wp_redirect( add_query_arg( 'projects_post_id', $projects_post_id, get_admin_url() . 'admin.php?page=cpt-projects&projects_post_id=' ) );
		exit;
	} else {
		die();
	}
}


function cpt_permadelete_project_button( $projects_post_id ) {
	if ( ! $projects_post_id ) {
		return;
	}
	ob_start();
	?>
		<form 
			id="cpt_permadelete_project_button" 
			action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" 
			method="POST"
		>
			<?php wp_nonce_field( 'cpt_project_permadeleted', 'cpt_project_permadeleted_nonce' ); ?>
			<input 
				name="action" 
				value="cpt_project_permadeleted" 
				type="hidden"
			>
			<input 
				name="projects_post_id" 
				value="<?php echo esc_attr( $projects_post_id ); ?>" 
				type="hidden"
			>
			<input 
				name="submit" 
				id="submit" 
				type="submit" 
				value="<?php esc_attr_e( 'Delete Permanently', 'client-power-tools' ); ?>"
			>
		</form>
	<?php
	return ob_get_clean();
}

add_action( 'admin_post_cpt_project_permadeleted', __NAMESPACE__ . '\cpt_process_permadelete_project' );
function cpt_process_permadelete_project() {
	if ( isset( $_POST['cpt_project_permadeleted_nonce'] ) && wp_verify_nonce( $_POST['cpt_project_permadeleted_nonce'], 'cpt_project_permadeleted' ) ) {
		$projects_post_id = sanitize_key( intval( $_POST['projects_post_id'] ) );
		$project_deleted  = wp_delete_post( $projects_post_id, true );

		if ( $project_deleted == true ) {
			$result = Common\cpt_get_projects_label( 'singular' ) . ' ' . __( 'deleted.', 'client-power-tools' );
		} else {
			$result = Common\cpt_get_projects_label( 'singular' ) . ' ' . __( 'could not be deleted.', 'client-power-tools' );
		}

		set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
		wp_redirect( remove_query_arg( 'projects_post_id', $_POST['_wp_http_referer'] ) );
		exit;
	} else {
		die();
	}
}
