<?php
	namespace Client_Power_Tools\Core\Admin;

	use Client_Power_Tools\Core\Common;

	$user_id        = isset( $_REQUEST['user_id'] ) ? sanitize_key( intval( $_REQUEST['user_id'] ) ) : false;
	$client_ids     = Common\cpt_get_clients( array( 'fields' => 'ID' ) );
	$projects_label = Common\cpt_get_projects_label();
?>

<form 
	action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" 
	method="POST"
>
	<?php wp_nonce_field( 'cpt_new_project_added', 'cpt_new_project_nonce' ); ?>
	<input 
		name="action" 
		value="cpt_new_project_added" 
		type="hidden"
	>
	<div class="cpt-row">
		<div class="form-field span-4 form-required">
			<label for="client_id">Client <small class="required">(<?php esc_html_e( 'required', 'client-power-tools' ); ?>)</small></label>
			<?php if ( $client_ids ) { ?>
				<select 
					name="client_id" 
					id="client_id" 
					required
				>
					<option disabled selected value>
						<?php esc_html_e( 'Select client.', 'client-power-tools' ); ?>
					</option>
					<?php foreach ( $client_ids as $client_id ) { ?>
						<option 
							value="<?php echo esc_attr( $client_id ); ?>"
							<?php selected( $client_id, $user_id ); ?>
						>
							<?php echo esc_html( Common\cpt_get_client_name( $client_id ) ); ?>
						</option>
					<?php } ?>
				</select>
			<?php } ?>
		</div>
	</div>
	<div class="cpt-row">
		<div class="form-field">
			<label for="project_id">Project ID</label>
			<input 
				name="project_id" 
				id="project_id" 
				class="regular-text" 
				type="text" 
				autocapitalize="none" 
				autocorrect="off"
			>
		</div>
		<div class="form-field span-3">
			<label for="project_name">Project Name <small class="required">(<?php esc_html_e( 'required', 'client-power-tools' ); ?>)</small></label>
			<input 
				name="project_name" 
				id="project_name" 
				class="regular-text" 
				type="text" 
				required 
				aria-required="true"
			>
		</div>
	</div>
	<div class="cpt-row">
		<div class="form-field span-2">
			<label for="project_type">Project Type</label>
			<?php
			$project_type_select = cpt_get_project_type_select( 'project_type' );
			if ( $project_type_select ) {
				echo esc_html( $project_type_select );
			}
			?>
		</div>
		<div class="form-field span-2">
			<label for="project_stage">
				<?php
					// Translators: %s is the project label.
					printf( esc_html__( '%s Stage', 'client-power-tools' ), esc_html( $projects_label[0] ) );
				?>
			</label>
			<?php
			$project_stage_select = cpt_get_project_stage_select( '', 'project_stage' );
			if ( $project_stage_select ) {
				echo esc_html( $project_stage_select );
			}
			?>
		</div>
		<div class="form-field">
			<label for="project_status">
				<?php
					// Translators: %s is the project label.
					printf( esc_html__( '%s Status', 'client-power-tools' ), esc_html( $projects_label[0] ) );
				?>
			</label>
			<?php
			$project_status_select = cpt_get_status_select( 'cpt_project_statuses', 'project_status', 'cpt_default_project_status' );
			if ( $project_status_select ) {
				echo esc_html( $project_status_select );
			}
			?>
		</div>
	</div>
	<p class="submit">
		<input 
			name="submit" 
			id="submit" 
			class="button button-primary wp-element-button" 
			type="submit" 
			value="<?php echo esc_attr__( 'Add', 'client-power-tools' ) . ' ' . esc_attr( $projects_label[0] ); ?>"
		>
	</p>
</form>
