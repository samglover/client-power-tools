<?php
/**
 * Edit project
 *
 * @file       cpt-edit-project.php
 * @package    Client_Power_Tools
 * @subpackage Core\Admin
 * @since      1.6.5
 */

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

/**
 * Edit This Project panel
 *
 * @param int $projects_post_id Project's post ID.
 */
function cpt_edit_project( $projects_post_id ) {
	if ( ! $projects_post_id || ! is_user_logged_in() ) {
		return;
	}
	$project_data   = Common\cpt_get_project_data( $projects_post_id );
	$projects_label = Common\cpt_get_projects_label( 'singular' );

	if ( is_admin() && current_user_can( 'cpt_manage_projects' ) ) {
		if ( 'trash' !== get_post_status( $projects_post_id ) ) {
			?>
			<button class="button wp-element-button cpt-click-to-expand">
				<?php
				echo esc_html(
					sprintf(
						// Translators: %s is the singular project label.
						__( 'Edit %s', 'client-power-tools' ),
						$projects_label
					)
				);
				?>
			</button>
			<div class="cpt-this-expands">
				<div class="form-wrap form-wrap-edit_project">
					<h2>
						<?php
						echo esc_html(
							sprintf(
								// Translators: %s is the singular project label.
								__( 'Edit This %s', 'client-power-tools' ),
								$projects_label
							)
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
/**
 * Process project updates.
 */
function cpt_process_project_update() {
	if (
		! isset( $_POST['cpt_project_updated_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpt_project_updated_nonce'] ) ), 'cpt_project_updated' )
	) {
		exit( esc_html__( 'Invalid nonce.', 'client-power-tools' ) );
	}

	$projects_post_id = isset( $_POST['projects_post_id'] ) ? intval( wp_unslash( $_POST['projects_post_id'] ) ) : null;
	$project_data     = array(
		'ID'         => $projects_post_id,
		'post_title' => isset( $_POST['project_name'] ) ? sanitize_text_field( wp_unslash( $_POST['project_name'] ) ) : null,
		'meta_input' => array(
			'cpt_project_id'     => isset( $_POST['project_id'] ) ? sanitize_text_field( wp_unslash( $_POST['project_id'] ) ) : null,
			'cpt_project_status' => isset( $_POST['project_status'] ) ? sanitize_text_field( wp_unslash( $_POST['project_status'] ) ) : null,
			'cpt_project_type'   => isset( $_POST['project_type'] ) ? intval( wp_unslash( $_POST['project_type'] ) ) : null,
			'cpt_project_stage'  => isset( $_POST['project_stage'] ) ? sanitize_text_field( wp_unslash( $_POST['project_stage'] ) ) : null,
			'cpt_client_id'      => isset( $_POST['client_id'] ) ? sanitize_text_field( wp_unslash( $_POST['client_id'] ) ) : null,
		),
	);

	$update_project = wp_update_post( $project_data );

	$result = 'Project updated.';
	if ( is_wp_error( $update_project ) ) {
		$result = sprintf(
			// Translators: %s is the error message.
			'Project could not be updated. Error message: %s',
			$update_project->get_error_message()
		);
	}
	set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );

	wp_safe_redirect( wp_get_referer() );
	exit;
}

/**
 * Delete project button
 *
 * @param int $projects_post_id Project's post ID.
 */
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
			value="
			<?php
			echo esc_attr(
				sprintf(
					// Translators: %s is the singular project label.
					__( 'Delete this %s', 'client-power-tools' ),
					Common\cpt_get_projects_label( 'singular' )
				)
			);
			?>
			"
		>
	</form>
	<?php
}

add_action( 'admin_post_cpt_project_deleted', __NAMESPACE__ . '\cpt_process_delete_project' );
/**
 * Processes project deletion.
 */
function cpt_process_delete_project() {
	if (
		! isset( $_POST['cpt_project_deleted_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpt_project_deleted_nonce'] ) ), 'cpt_project_deleted' )
	) {
		exit( esc_html__( 'Invalid nonce.', 'client-power-tools' ) );
	}

	if ( ! isset( $_POST['projects_post_id'] ) ) {
		exit( esc_html__( 'Missing project ID', 'client-power-tools' ) );
	}

	$projects_post_id = intval( wp_unslash( $_POST['projects_post_id'] ) );
	$project_deleted  = wp_trash_post( $projects_post_id );
	$projects_label   = Common\cpt_get_projects_label( 'singular' );

	if ( $project_deleted ) {
		$result = sprintf(
			// Translators: %s is the singular project label.
			__( '%s moved to the trash.', 'client-power-tools' ),
			$projects_label
		) . cpt_undelete_project_button( $projects_post_id );
	} else {
		$result = sprintf(
			// Translators: %s is the singular project label.
			__( '%s could not be moved to the trash.', 'client-power-tools' ),
			$projects_label
		);
	}

	set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
	wp_safe_redirect( remove_query_arg( 'projects_post_id', wp_get_referer() ) );
	exit;
}

/**
 * Undelete project button
 *
 * @param int $projects_post_id Project's post ID.
 * @return string Button HTML.
 */
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
/**
 * Process project undelete.
 */
function cpt_process_undelete_project() {
	if (
		! isset( $_POST['cpt_project_undeleted_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpt_project_undeleted_nonce'] ) ), 'cpt_project_undeleted' )
	) {
		exit( esc_html__( 'Invalid nonce.', 'client-power-tools' ) );
	}

	if ( ! isset( $_POST['projects_post_id'] ) ) {
		exit( esc_html__( 'Missing project ID', 'client-power-tools' ) );
	}

	$projects_post_id  = intval( wp_unslash( $_POST['projects_post_id'] ) );
	$project_undeleted = wp_update_post(
		array(
			'ID'          => $projects_post_id,
			'post_status' => 'publish',
		)
	);
	$projects_label    = Common\cpt_get_projects_label( 'singular' );

	if ( ! is_wp_error( $project_undeleted ) ) {
		$result = sprintf(
			// Translators: %s is the singular project label.
			__( '%s restored.', 'client-power-tools' ),
			$projects_label
		);
	} else {
		$result = sprintf(
			// Translators: %s is the singular project label.
			__( '%s could not be restored.', 'client-power-tools' ),
			$projects_label
		);
	}

	set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
	wp_safe_redirect( add_query_arg( 'projects_post_id', $projects_post_id, get_admin_url() . 'admin.php?page=cpt-projects&projects_post_id=' ) );
	exit;
}

/**
 * Delete project confirmation button
 *
 * @param int $projects_post_id Project's post ID.
 * @return string Button HTML.
 */
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
/**
 * Processes delete confirmation.
 */
function cpt_process_permadelete_project() {
	if (
		! isset( $_POST['cpt_project_permadeleted_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpt_project_permadeleted_nonce'] ) ), 'cpt_project_permadeleted' )
	) {
		exit( esc_html__( 'Invalid nonce.', 'client-power-tools' ) );
	}

	if ( ! isset( $_POST['projects_post_id'] ) ) {
		exit( esc_html__( 'Missing project ID.', 'client-power-tools' ) );
	}

	$projects_post_id = intval( wp_unslash( $_POST['projects_post_id'] ) );
	$project_deleted  = wp_delete_post( $projects_post_id, true );
	$projects_label   = Common\cpt_get_projects_label( 'singular' );

	if ( true === $project_deleted ) {
		$result = sprintf(
			// Translators: %s is the singular project label.
			__( '%s deleted.', 'client-power-tools' ),
			$projects_label
		);
	} else {
		$result = sprintf(
			// Translators: %s is the singular project label.
			__( '%s could not be deleted.', 'client-power-tools' ),
			$projects_label
		);
	}

	set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
	wp_safe_redirect( remove_query_arg( 'projects_post_id', wp_get_referer() ) );
	exit;
}