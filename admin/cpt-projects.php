<?php

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

function cpt_projects() {
	if ( ! current_user_can( 'cpt_view_projects' ) ) {
		wp_die( '<p>' . esc_html__( 'Sorry, you are not allowed to access this page.' ) . '</p>', 403 );
	}
	$projects_label = Common\cpt_get_projects_label();
	?>
		<div 
			id="cpt-admin" 
			class="wrap"
		>
			<?php if ( isset( $_REQUEST['projects_post_id'] ) ) { ?>
				<p>
					<a href="<?php echo esc_url( remove_query_arg( 'projects_post_id' ) ); ?>">
						&lt; 
						<?php
							printf(
								// Translators: %s is the projects label.
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
					<?php if ( ! isset( $_REQUEST['projects_post_id'] ) ) { ?>
						<h1 id="cpt-page-title">
							<?php echo esc_html( $projects_label[1] ); ?>
						</h1>
						<p id="cpt-subtitle">Client Power Tools</p>
					<?php } else { ?>
						<?php
							$projects_post_id = intval( sanitize_key( $_REQUEST['projects_post_id'] ) );
							$project_data     = Common\cpt_get_project_data( $projects_post_id );
							$clients_user_id  = $project_data['clients_user_id'];
							$client_data      = $clients_user_id ? Common\cpt_get_client_data( $clients_user_id ) : false;
						?>
						<?php if ( $project_data['project_status'] ) { ?>
							<p id="cpt-project-status">
								<?php echo esc_html( $project_data['project_status'] ); ?>
							</p>
						<?php } ?>
						<h1 id="cpt-page-title">
							<?php
							if ( $projects_post_id ) {
								echo esc_html( $project_data['project_name'] );

							} else {
								printf(
									// Translators: %s is the projects label.
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
			if ( ! isset( $_REQUEST['projects_post_id'] ) ) {
				if ( current_user_can( 'cpt_manage_projects' ) ) {
					if ( $clients ) {
						?>
								<button class="button wp-element-button cpt-click-to-expand">
									<?php
										printf(
											// Translators: %s is the projects label.
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
													// Translators: %s is the projects label.
													esc_html__( 'Add a %s', 'client-power-tools' ),
													esc_html( Common\cpt_get_projects_label( 'singular' ) )
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
										// Translators: %s is the projects label.
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
				$projects_post_id = intval( sanitize_key( $_REQUEST['projects_post_id'] ) );
				Common\cpt_get_project_progress_bar( $projects_post_id );
				cpt_edit_project( $projects_post_id );
			}
			?>
		</div>
	<?php
}

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


function cpt_get_project_type_select( $field_name = 'project_type', $selected = null ) {
	$project_types = get_terms(
		array(
			'taxonomy'   => 'cpt-project-type',
			'hide_empty' => false,
			array(
				'orderby' => isset( $_REQUEST['orderby'] ) ? sanitize_key( $_REQUEST['orderby'] ) : 'name',
				'order'   => isset( $_REQUEST['order'] ) ? sanitize_key( $_REQUEST['order'] ) : 'ASC',
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