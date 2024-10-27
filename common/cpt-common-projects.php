<?php

namespace Client_Power_Tools\Core\Common;

function cpt_get_projects_label( $n = null ) {
	$projects_label = get_option( 'cpt_projects_label' );
	foreach ( $projects_label as $key => $label ) {
		$projects_label[ $key ] = ucwords( $label );
	}
	switch ( $n ) {
		case ( 'singular' ):
			return $projects_label[0];
		case ( 'plural' ):
			return $projects_label[1];
		default:
			return $projects_label;
	}
}


function cpt_get_project_data( $projects_post_id ) {
	if ( ! $projects_post_id ) {
		return;
	}
	$term_obj     = get_term( get_post_meta( $projects_post_id, 'cpt_project_type', true ) );
	$project_data = array(
		'projects_post_id' => $projects_post_id,
		'project_id'       => get_post_meta( $projects_post_id, 'cpt_project_id', true ),
		'project_name'     => get_the_title( $projects_post_id ),
		'project_type'     => $term_obj ? $term_obj->name : '',
		'project_status'   => get_post_meta( $projects_post_id, 'cpt_project_status', true ),
		'project_stage'    => get_post_meta( $projects_post_id, 'cpt_project_stage', true ),
		'clients_user_id'  => get_post_meta( $projects_post_id, 'cpt_client_id', true ),
		'managers_user_id' => cpt_get_client_manager_id( get_post_meta( $projects_post_id, 'cpt_client_id', true ) ),
	);
	return $project_data;
}


function cpt_get_projects_list( $clients_user_id = null ) {
	if ( ! $clients_user_id ) {
		$clients_user_id = get_current_user_id();
	}

	if ( ! cpt_is_client( $clients_user_id ) ) {
		return;
	}

	$projects_label = cpt_get_projects_label();
	$projects       = new \WP_Query(
		array(
			'meta_key'       => 'cpt_client_id',
			'meta_value'     => $clients_user_id,
			'orderby'        => isset( $_REQUEST['orderby'] ) ? sanitize_key( $_REQUEST['orderby'] ) : 'project',
			'order'          => isset( $_REQUEST['order'] ) ? sanitize_key( $_REQUEST['order'] ) : 'ASC',
			'post_type'      => 'cpt_project',
			'posts_per_page' => -1,
		)
	);
	if ( $projects->have_posts() ) :
		?>
			<section class="cpt-projects-list">
				<?php
				while ( $projects->have_posts() ) :
					$projects->the_post();
					$projects_post_id = get_the_ID();
					?>
					<div class="cpt-project card">
						<div class="cpt-project-content">
							<h3 class="cpt-project-title">
								<?php
								if ( is_admin() ) {
									$project_url = get_admin_url() . 'admin.php?page=cpt-projects&projects_post_id=' . $projects_post_id;
								} else {
									$project_url = cpt_get_client_dashboard_url() . '?tab=projects&projects_post_id=' . $projects_post_id;
								}
								?>
								<a href="<?php echo esc_url( $project_url ); ?>"><?php the_title(); ?></a>
							</h3>
							<?php cpt_get_project_progress_bar( $projects_post_id ); ?>
						</div>
						<?php cpt_get_project_meta( $projects_post_id ); ?>
					</div>
					<?php
				endwhile;
				?>
			</section>
		<?php
	else :
		?>
			<p>
				<?php
					printf(
						// translators: %s is the projects label.
						esc_html__( 'No %s found.', 'client-power-tools' ),
						esc_html( strtolower( $projects_label[1] ) )
					);
				?>
			</p>
		<?php
		wp_reset_postdata();
	endif;
}

function cpt_get_project( $projects_post_id ) {
	$projects_label   = cpt_get_projects_label();
	$projects_post_id = sanitize_key( intval( $_REQUEST['projects_post_id'] ) );
	$project_data     = cpt_get_project_data( $projects_post_id );
	?>
	<p>
		<a href="<?php echo esc_url( remove_query_arg( 'projects_post_id' ) ); ?>">
			&lt;
			<?php
				printf(
					// translators: %s is the projects label.
					esc_html__( 'Back to %s', 'client-power-tools' ),
					esc_html( $projects_label[1] )
				);
			?>
		</a>
	</p>
	<?php cpt_get_project_meta( $projects_post_id ); ?>
	<h2 class="cpt-project-title">
		<?php
		echo esc_html( get_the_title( $projects_post_id ) );
		if ( $project_data['project_id'] ) {
			?>
				<span style="color:silver">
					(<?php echo esc_html( $project_data['project_id'] ); ?>)
				</span>
			<?php
		}
		?>
	</h2>
	<?php
	cpt_get_project_progress_bar( $projects_post_id );
}

function cpt_get_project_progress_bar( $projects_post_id ) {
	if ( ! $projects_post_id ) {
		$projects_post_id = get_the_ID();
	}
	if ( ! $projects_post_id ) {
		return;
	}

	$project_type   = get_term( get_post_meta( $projects_post_id, 'cpt_project_type', true ) );
	$project_stages = isset( $project_type->term_id ) ? array_map( 'trim', explode( "\n", get_term_meta( $project_type->term_id, 'cpt_project_type_stages', true ) ) ) : false;

	if ( ! $project_type || ! $project_stages ) {
		return;
	}

	$current_stage     = sanitize_text_field( get_post_meta( $projects_post_id, 'cpt_project_stage', true ) );
	$current_stage_key = array_search( $current_stage, $project_stages, true );
	$stage_width       = 100 / count( $project_stages );

	if ( 0 === $current_stage_key && count( $project_stages ) > 1 ) {
		$indicator_width = false;
	} elseif ( count( $project_stages ) === $current_stage_key + 1 ) {
		$indicator_width = 100;
	} else {
		$indicator_width = $stage_width * $current_stage_key + ( $stage_width / 2 );
	}

	if ( $current_stage ) {
		?>
			<div class="cpt-project-stage-progress">
				<p class="cpt-section-header"><?php esc_html_e( 'Progress', 'client-power-tools' ); ?></p>
				<div class="cpt-stages-container">
					<div class="cpt-stage-progress-container">
						<div 
							class="cpt-stage-progress-indicator"
							<?php if ( $indicator_width ) { ?>
								style="<?php echo esc_attr( 'width: ' . $indicator_width . '%' ); ?>;"
							<?php } ?>
						></div>
					</div>
					<div class="cpt-stage-labels">
					<?php
					foreach ( $project_stages as $key => $stage ) {
						$stage_classes = 'cpt-stage-label';
						if ( $key < $current_stage_key ) {
							$stage_classes .= ' completed';
						}
						if ( $stage == $current_stage ) {
							$stage_classes .= ' current';
						}
						if ( $key > $current_stage_key ) {
							$stage_classes .= ' not-started';
						}
						?>
							<div 
								class="<?php echo esc_attr( $stage_classes ); ?>"
								style="<?php echo esc_attr( 'width: ' . $stage_width . '%' ); ?>;"
							>
								<?php echo esc_html( $stage ); ?>
							</div>
						<?php
					}
					?>
					</div>
				</div>
			</div>
		<?php
	}
}

function cpt_get_project_meta( $projects_post_id ) {
	if ( ! $projects_post_id ) {
		$projects_post_id = get_the_ID();
	}

	if ( ! $projects_post_id || ! cpt_is_project( $projects_post_id ) ) {
		return;
	}

	$project_data = cpt_get_project_data( $projects_post_id );
	?>
		<div class="cpt-project-meta cpt-row">
			<?php
			if (
				! cpt_is_project() &&
				$project_data['project_id']
			) {
				?>
						<div class="cpt-col cpt-project-id">
							<span class="cpt-project-meta-label"><?php esc_html_e( 'ID', 'client-power-tools' ); ?></span>
							<span class="cpt-project-meta-value"><?php echo esc_html( $project_data['project_id'] ); ?></span>
						</div>
					<?php
			}
			if ( $project_data['project_type'] ) {
				?>
						<div class="cpt-col cpt-project-type">
							<span class="cpt-project-meta-label"><?php esc_html_e( 'Type', 'client-power-tools' ); ?></span>
							<span class="cpt-project-meta-value"><?php echo esc_html( $project_data['project_type'] ); ?></span>
						</div>
					<?php
			}
			if ( $project_data['project_status'] ) {
				?>
						<div class="cpt-col cpt-project-status">
							<span class="cpt-project-meta-label"><?php esc_html_e( 'Status', 'client-power-tools' ); ?></span>
							<span class="cpt-project-meta-value"><?php echo esc_html( $project_data['project_status'] ); ?></span>
						</div>
					<?php
			}
			?>
		</div>
	<?php
}

add_action( 'wp_ajax_cpt_update_stage_select', __NAMESPACE__ . '\cpt_update_stage_select' );
add_action( 'wp_ajax_nopriv_cpt_update_stage_select', __NAMESPACE__ . '\cpt_update_stage_select' );
function cpt_update_stage_select() {
	if ( ! isset( $_POST['_ajax_nonce'] ) || ! wp_verify_nonce( $_POST['_ajax_nonce'], 'update-stages-nonce' ) ) {
		exit( 'Invalid nonce.' );
	}
	$project_type = $_POST['project_type'];
	$stages_array = explode( "\n", sanitize_textarea_field( get_term_meta( $project_type, 'cpt_project_type_stages', true ) ) );
	foreach ( $stages_array as $key => $val ) {
		$stages_array[ $key ] = trim( $val );
		if ( empty( $stages_array[ $key ] ) ) {
			unset( $stages_array[ $key ] );
		}
	}
	wp_send_json(
		array(
			'type'   => $project_type,
			'stages' => $stages_array,
		)
	);
	wp_die();
}