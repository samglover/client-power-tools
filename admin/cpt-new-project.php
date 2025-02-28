<?php
/**
 * New project
 *
 * @file       cpt-new-project.php
 * @package    Client_Power_Tools
 * @subpackage Core\Admin
 * @since      1.6.5
 */

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

add_action( 'admin_post_cpt_new_project_added', __NAMESPACE__ . '\cpt_process_new_project' );
/**
 * Processes a new project.
 */
function cpt_process_new_project() {
	if (
		! isset( $_POST['cpt_new_project_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpt_new_project_nonce'] ) ), 'cpt_new_project_added' )
	) {
		exit( 'Invalid nonce.' );
	}

	$new_project = wp_insert_post(
		array(
			'post_status' => 'publish',
			'post_title'  => sanitize_text_field( wp_unslash( $_POST['project_name'] ) ),
			'post_type'   => 'cpt_project',
			'meta_input'  => array(
				'cpt_project_id'     => sanitize_text_field( wp_unslash( $_POST['project_id'] ) ),
				'cpt_project_type'   => intval( wp_unslash( $_POST['project_type'] ) ),
				'cpt_project_stage'  => sanitize_text_field( wp_unslash( $_POST['project_stage'] ) ),
				'cpt_project_status' => sanitize_text_field( wp_unslash( $_POST['project_status'] ) ),
				'cpt_client_id'      => sanitize_text_field( wp_unslash( $_POST['client_id'] ) ),
			),
		)
	);

	if ( is_wp_error( $new_project ) ) {
		$result = esc_html(
			sprintf(
				'Project could not be created. Error message: %s',
				$new_client->get_error_message()
			)
		);
	} else {
		$project_label = Common\cpt_get_projects_label();
		$result        = esc_html(
			sprintf(
				// Translators: %1$s is the singular project label. %2$s and %4$s are HTML `<a>` tags that link to the project page. %3$s is the lower-case project label.
				__( '%1$s created. %2$sView %3$s%4$s.', 'client-power-tools' ),
				/* %1$s */ $project_label,
				/* %2$s */ '<a href="' . get_admin_url() . 'admin.php?page=cpt-projects&projects_post_id=' . $new_project . '">',
				/* %3$s */ strtolower( $project_label ),
				/* %4$s */ '</a>'
			)
		);
	}

	set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
	wp_safe_redirect( wp_get_referer() );
	exit;
}
