<?php
	namespace Client_Power_Tools\Core\Admin;

	use Client_Power_Tools\Core\Common;

	$client_ids     = Common\cpt_get_clients( array( 'fields' => 'ID' ) );
	$projects_label = Common\cpt_get_projects_label();
?>

<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">
	<?php wp_nonce_field( 'cpt_project_updated', 'cpt_project_updated_nonce' ); ?>
	<input 
		name="action" 
		value="cpt_project_updated" 
		type="hidden"
	>
	<input 
		name="projects_post_id" 
		value="<?php echo esc_attr( $projects_post_id ); ?>" 
		type="hidden"
	>
	<div class="cpt-row">
		<div class="form-field span-4 form-required">
			<label for="client_id">
				<?php esc_html_e( 'Client', 'client-power-tools' ); ?>
				<small class="cpt-required">(<?php esc_html_e( 'required', 'client-power-tools' ); ?>)</small>
			</label>
			<?php if ( $client_ids ) { ?>
				<select 
					name="client_id" 
					id="client_id" 
					required
				>
					<?php foreach ( $client_ids as $client_id ) { ?>
						<option 
							value="<?php echo esc_attr( $client_id ); ?>"
							<?php selected( get_post_meta( $projects_post_id, 'cpt_client_id', true ), $client_id ); ?>
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
			<label for="project_id">
				<?php
					printf(
						// translators: %s is the projects label.
						esc_html__( '%s ID', 'client-power-tools' ),
						esc_html( $projects_label[0] )
					);
					?>
			</label>
			<input 
				name="project_id" 
				id="project_id" 
				type="text" 
				autocapitalize="none" 
				autocorrect="off" 
				value="<?php echo esc_attr( $project_data['project_id'] ); ?>"
			>
		</div>
		<div class="form-field span-3 form-required">
			<label for="project_name">
				<?php
					printf(
						// translators: %s is the projects label.
						esc_html__( '%s Name', 'client-power-tools' ),
						esc_html( $projects_label[0] )
					);
					?>
				<small class="required">(<?php esc_html_e( 'required', 'client-power-tools' ); ?>)</small>
			</label>
			<input 
				name="project_name" 
				id="project_name" 
				class="regular-text" 
				type="text" 
				required aria-required="true" 
				value="<?php echo esc_attr( $project_data['project_name'] ); ?>"
			>
		</div>
	</div>
	<div class="cpt-row">
		<div class="form-field span-2">
			<label for="project_type">
				<?php
					printf(
						// translators: %s is the projects label.
						esc_html__( '%s Type', 'client-power-tools' ),
						esc_html( $projects_label[0] )
					);
					?>
			</label>
			<?php
			$project_type        = get_post_meta( $projects_post_id, 'cpt_project_type', true );
			$project_type_select = cpt_get_project_type_select( 'project_type', $project_type );
			if ( $project_type_select ) {
				echo esc_html( $project_type_select );
			}
			?>
		</div>
		<div class="form-field span-2">
			<label for="project_stage">
				<?php
					printf(
						// translators: %s is the projects label.
						esc_html__( '%s Stage', 'client-power-tools' ),
						esc_html( $projects_label[0] )
					);
					?>
			</label>
			<?php
			$current_project_stage = get_post_meta( $projects_post_id, 'cpt_project_stage', true );
			$project_stage_select  = cpt_get_project_stage_select( $projects_post_id, 'project_stage', $current_project_stage );
			if ( $project_stage_select ) {
				echo esc_html( $project_stage_select );
			}
			?>
		</div>
		<div class="form-field">
			<label for="project_status">
				<?php
					printf(
						// translators: %s is the projects label.
						esc_html__( '%s Status', 'client-power-tools' ),
						esc_html( $projects_label[0] )
					);
					?>
			</label>
			<?php echo esc_html( cpt_get_status_select( 'cpt_project_statuses', 'project_status', 'cpt_default_project_status' ) ); ?>
		</div>
	</div>
	<p class="submit">
		<input 
			name="submit" 
			id="submit" 
			class="button button-primary wp-element-button" 
			type="submit" 
			value="
				<?php
					printf(
						// translators: %s is the projects label.
						esc_html__( 'Update %s', 'client-power-tools' ),
						esc_html( $projects_label[0] )
					);
					?>
			"
		>
	</p>
</form>
