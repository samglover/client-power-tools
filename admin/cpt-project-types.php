<?php

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

function cpt_project_types() {
	if ( ! current_user_can( 'cpt_view_projects' ) ) {
		wp_die( '<p>' . esc_html__( 'Sorry, you are not allowed to access this page.' ) . '</p>', 403 );
	}
	$projects_label = Common\cpt_get_projects_label();
	?>
		<div id="cpt-admin" class="wrap">
			<div id="cpt-admin-header">
			<img class="cpt-logo" src="<?php echo esc_url( CLIENT_POWER_TOOLS_DIR_URL ); ?>assets/images/cpt-logo.png" alt="Client Power Tools" />
				<div id="cpt-admin-page-title">
					<h1 id="cpt-page-title">
						<?php
							printf(
								// translators: %s is the projects label.
								esc_html__( '%s Types', 'client-power-tools' ),
								esc_html( $projects_label[0] )
							);
						?>
					</h1>
					<p id="cpt-subtitle">Client Power Tools</p>
				</div>
			</div>
			<hr class="wp-header-end">
			<div id="col-container">
				<div id="col-left">
					<div class="col-wrap">
						<div class="form-wrap">
							<h2>
								<?php
									printf(
										// translators: %s is the projects label.
										esc_html__( 'Add New %s Type', 'client-power-tools' ),
										esc_html( $projects_label[0] )
									);
								?>
							</h2>
							<?php
								include CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-new-project-type-form.php';
							?>
						</div>
					</div>
				</div>
				<div id="col-right">
					<div class="col-wrap">
						<?php
							$project_types_list = new Project_Types_List_Table();
							$project_types_list->prepare_items();
						?>
						<form 
							id="project-types-list" 
							action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST"
						>
							<input 
								name="action" 
								value="cpt_project_type_edited" 
								type="hidden"
							>
							<?php
								wp_nonce_field( 'cpt_project_type_edited', 'cpt_edit_project_type_nonce' );
								$project_types_list->display();
							?>
						</form>
						<?php include CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-edit-project-type-form.php'; ?>
					</div>
				</div>
			</div>
		</div>
	<?php
}


add_action( 'admin_post_cpt_new_project_type_added', __NAMESPACE__ . '\cpt_process_new_project_type' );
function cpt_process_new_project_type() {
	if ( ! isset( $_POST['cpt_new_project_type_nonce'] ) || ! wp_verify_nonce( $_POST['cpt_new_project_type_nonce'], 'cpt_new_project_type_added' ) ) {
		exit( esc_html__( 'Invalid nonce.', 'client-power-tools' ) );
	}

	$new_project_type = wp_insert_term(
		sanitize_text_field( $_POST['project_type'] ),
		'cpt-project-type'
	);

	if ( is_wp_error( $new_project_type ) ) {
		$result = sprintf( __( '%1$s type could not be created. Error message: %2$s', 'client-power-tools' ), Common\cpt_get_projects_label( 'singular' ), $new_project_type->get_error_message() );
	} else {
		$result = sprintf(
			// translators: %s is the projects label.
			esc_html__( '%s type created.', 'client-power-tools' ),
			esc_html( Common\cpt_get_projects_label( 'singular' ) )
		);
		$new_project_type_stages = add_term_meta(
			$new_project_type['term_id'],
			'cpt_project_type_stages',
			sanitize_textarea_field( $_POST['project_type_stages'] ),
			true
		);

		if ( is_wp_error( $new_project_type_stages ) ) {
			$result .= sprintf(
				// translators: %s is the error message.
				esc_html__( 'Stages could not be added. Error message: %s', 'client-power-tools' ),
				esc_html( $new_project_type_stages->get_error_message() )
			);
		}
	}

	set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
	wp_redirect( $_POST['_wp_http_referer'] );
	exit;
}


add_action( 'admin_post_cpt_project_type_edited', __NAMESPACE__ . '\cpt_process_edit_project_type' );
function cpt_process_edit_project_type() {
	if ( ! isset( $_POST['cpt_edit_project_type_nonce'] ) || ! wp_verify_nonce( $_POST['cpt_edit_project_type_nonce'], 'cpt_project_type_edited' ) ) {
		exit( esc_html__( 'Invalid nonce.', 'client-power-tools' ) );
	}

	$project_type_id     = intval( $_POST['edit_project_type_id'] );
	$project_type        = $_POST['edit_project_type'];
	$project_type_stages = $_POST['edit_project_type_stages'];

	$update_project_type = wp_update_term(
		$project_type_id,
		'cpt-project-type',
		array(
			'name' => sanitize_text_field( $_POST['edit_project_type'] ),
		)
	);

	if ( is_wp_error( $update_project_type ) ) {
		$result = sprintf( __( '%1$s type could not be updated. Error message: %2$s', 'client-power-tools' ), Common\cpt_get_projects_label( 'singular' ), $new_project_type->get_error_message() );
	} else {
		$result              = sprintf( __( '%s type updated.', 'client-power-tools' ), Common\cpt_get_projects_label( 'singular' ) );
		$project_type_stages = update_term_meta(
			$project_type_id,
			'cpt_project_type_stages',
			sanitize_textarea_field( $_POST['edit_project_type_stages'] )
		);

		if ( is_wp_error( $project_type_stages ) ) {
			$result .= sprintf(
				// translators: %s is the error message.
				esc_html__( 'Stages could not be updated. Error message: %s', 'client-power-tools' ),
				esc_html( $new_project_type_stages->get_error_message() )
			);
		}
	}

	set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
	wp_redirect( $_POST['_wp_http_referer'] );
	exit;
}


function cpt_process_project_type_actions( $action ) {
	if ( ! $action ) {
		return;
	}
	$redirect_url = remove_query_arg(
		array(
			'_wpnonce',
			'action',
			'project_type_term_id',
			'error',
			'message',
		),
		wp_get_referer()
	);
	switch ( $action ) {
		case 'delete':
			$term_id      = intval( $_REQUEST['project_type_term_id'] );
			$term_deleted = wp_delete_term( $term_id, 'cpt-project-type' );
			if ( is_wp_error( $term_deleted ) ) {
				$result .= sprintf(
					// translators: 1 is the projects label, 2 is the error message.
					esc_html__( '%1$s type could not be deleted. Error message: %2$s', 'client-power-tools' ),
					esc_html( Common\cpt_get_projects_label( 'singular' ) ),
					esc_html( $term_deleted->get_error_message() )
				);
			} else {
				$result = sprintf(
					// translators: %s is the projects label.
					esc_html__( '%s type deleted.', 'client-power-tools' ),
					esc_html( Common\cpt_get_projects_label( 'singular' ) )
				);
			}
			set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
			break;
	}
	$redirect = wp_redirect( $redirect_url );
	exit;
}