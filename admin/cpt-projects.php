<?php
/**
 * Admin projects
 *
 * @file       cpt-projects.php
 * @package    Client_Power_Tools
 * @subpackage Core\Admin
 * @since      1.6.5
 */

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

/**
 * Outputs projects in the admin.
 */
function cpt_projects() {
	if ( ! current_user_can( 'cpt_view_projects' ) ) {
		wp_die( '<p>' . esc_html__( 'Sorry, you are not allowed to access this page.' ) . '</p>', 403 );
	}

	$projects_label = Common\cpt_get_projects_label();

	if ( isset( $_REQUEST['projects_post_id'] ) ) {
		$project_id = intval( wp_unslash( $_REQUEST['projects_post_id'] ) );
	} else {
		$project_id = false;
	}

	?>
	<div id="cpt-admin" class="wrap">
		<?php if ( $project_id ) { ?>
			<p>
				<a href="<?php echo esc_url( remove_query_arg( 'projects_post_id' ) ); ?>">
					<?php
						printf(
							// Translators: %s is the plural projects label.
							esc_html__( 'Back to %s', 'client-power-tools' ),
							esc_html( $projects_label[1] )
						);
					?>
				</a>
			</p>
		<?php } ?>
		<div id="cpt-admin-header">
		<img class="cpt-logo" src="<?php echo esc_url( CLIENT_POWER_TOOLS_DIR_URL ); ?>assets/images/cpt-logo.png" alt="Client Power Tools" />
			<div id="cpt-admin-page-title">
				<?php if ( ! $project_id ) { ?>
					<h1 id="cpt-page-title">
						<?php echo esc_html( $projects_label[1] ); ?>
					</h1>
					<p id="cpt-subtitle">Client Power Tools</p>
				<?php } else { ?>
					<?php
						$project_data    = Common\cpt_get_project_data( $project_id );
						$clients_user_id = $project_data['clients_user_id'];
						$client_data     = $clients_user_id ? Common\cpt_get_client_data( $clients_user_id ) : false;
					?>
					<?php if ( $project_data['project_status'] ) { ?>
						<p id="cpt-project-status">
							<?php echo esc_html( $project_data['project_status'] ); ?>
						</p>
					<?php } ?>
					<h1 id="cpt-page-title">
						<?php
						if ( $project_id ) {
							echo esc_html( $project_data['project_name'] );
						} else {
							printf(
								// Translators: %s is the plural projects label.
								esc_html__( 'Error: No such %s.', 'client-power-tools' ),
								esc_html( strtolower( $projects_label[0] ) )
							);
						}
						?>
						<?php if ( $project_data['project_id'] ) { ?>
							<span style="color:silver">
								(<?php echo esc_html( $project_data['project_id'] ); ?>)
							</span>
						<?php } ?>
					</h1>
					<?php if ( $clients_user_id ) { ?>
						<p id="cpt-project-client">
							<?php
							if ( get_current_user_id() === $client_data['manager_id'] ) {
								echo esc_html__( 'Your Client', 'client-power-tools' ) . ' ';
							}
								echo esc_html( Common\cpt_get_client_name( $client_data['user_id'] ) ) . '\'s ' . esc_html( $projects_label[0] );
							?>
						</p>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
		<hr class="wp-header-end">
		<?php
		$clients = Common\cpt_get_clients( array( 'fields' => 'ID' ) );
		if ( ! $project_id ) {
			if ( current_user_can( 'cpt_manage_projects' ) ) {
				if ( $clients ) {
					?>
					<button class="button wp-element-button cpt-click-to-expand">
						<?php
							printf(
								// Translators: %s is the singular project label.
								esc_html__( 'Add a %s', 'client-power-tools' ),
								esc_html( $projects_label[0] )
							);
						?>
					</button>
					<div class="cpt-this-expands">
						<div class="form-wrap form-wrap-new_project">
							<h2>
								<?php
									printf(
										// Translators: %s is the singular project label.
										esc_html__( 'Add a %s', 'client-power-tools' ),
										esc_html( $projects_label[0] )
									);
								?>
							</h2>
							<?php include CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-new-project-form.php'; ?>
						</div>
					</div>
					<?php
				} else {
					?>
					<p>
						<?php
							sprintf(
								// Translators: %s is the singular project label.
								esc_html__( 'In order to create a %s you must add a client.', 'client-power-tools' ),
								esc_html( strtolower( $projects_label[0] ) )
							)
						?>
					</p>
					<?php
				}
			}
			cpt_project_list();
		} else {
			Common\cpt_get_project_progress_bar( $project_id );
			cpt_edit_project( $project_id );
		}
		?>
	</div>
	<?php
}

/**
 * Outputs the list of projects.
 */
function cpt_project_list() {
	$project_list = new Project_List_Table();
	$project_list->prepare_items();
	?>
		<form id="project-list" method="GET">
			<?php $project_list->views(); ?>
			<?php $project_list->display(); ?>
		</form>
	<?php
}

/**
 * Outputs the project type select drop-down.
 *
 * @param string   $field_name Populates the `name` and `id` properties of the `select` tag.
 * @param int|null $selected Optional. The selected project type. Default null.
 */
function cpt_get_project_type_select( $field_name = 'project_type', $selected = null ) {
	if ( isset( $_REQUEST['orderby'] ) ) {
		$orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
	} else {
		$orderby = 'name';
	}

	if ( isset( $_REQUEST['order'] ) ) {
		$order = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
	} else {
		$order = 'ASC';
	}

	$project_types = get_terms(
		array(
			'taxonomy'   => 'cpt-project-type',
			'hide_empty' => false,
			array(
				'orderby' => $orderby,
				'order'   => $order,
			),
		)
	);

	if ( $project_types ) {
		?>
			<select 
				name="<?php echo esc_attr( $field_name ); ?>" 
				id="<?php echo esc_attr( $field_name ); ?>"
			>
				<?php foreach ( $project_types as $project_type ) { ?>
					<option 
						value="<?php echo esc_attr( $project_type->term_id ); ?>"
						<?php selected( $project_type->term_id, $selected ); ?>
					>
						<?php echo esc_html( $project_type->name ); ?>
					</option>
				<?php } ?>
			</select>
		<?php
	}
}

/**
 * Outputs the project stage select drop-down.
 *
 * @param int|null $projects_post_id Optional. A project post ID. Default null.
 * @param string   $field_name Populates the `name` and `id` properties of the `select` tag.
 * @param int|null $selected Optional. The selected project type. Default null.
 */
function cpt_get_project_stage_select( $projects_post_id = null, $field_name = 'cpt_project_stage', $selected = null ) {
	if ( $projects_post_id ) {
		$project_type  = get_post_meta( $projects_post_id, 'cpt_project_type', true );
		$current_stage = get_post_meta( $projects_post_id, 'cpt_project_stage', true );
	} else {
		$default_type = get_taxonomy( 'cpt-project-type' )->default_term;
		$project_type = get_term_by( 'slug', $default_type['slug'], 'cpt-project-type' )->term_id;
	}

	$stages_array = explode( "\n", get_term_meta( $project_type, 'cpt_project_type_stages', true ) );
	foreach ( $stages_array as $key => $val ) {
		$stages_array[ $key ] = trim( $val );
		if ( empty( $stages_array[ $key ] ) ) {
			unset( $stages_array[ $key ] );
		}
	}

	?>
		<select 
			name="<?php echo esc_attr( $field_name ); ?>" 
			id="<?php echo esc_attr( $field_name ); ?>"
		>
			<?php if ( ! empty( $stages_array ) ) { ?>
				<?php foreach ( $stages_array as $stage ) { ?>
					<?php $stage = trim( $stage ); ?>
					<option 
						value="<?php echo esc_attr( $stage ); ?>"
						<?php selected( $stage, $selected ); ?>
					>
						<?php echo esc_html( $stage ); ?>
					</option>
				<?php } ?>
			<?php } else { ?>
				<option 
					disabled 
					selected 
					value
				>
					<?php esc_html_e( 'Select another project type.', 'client-power-tools' ); ?>
				</option>
			<?php } ?>
		</select>
	<?php
}