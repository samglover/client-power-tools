<?php
	namespace Client_Power_Tools\Core\Admin;

	use Client_Power_Tools\Core\Common;
?>

<form id="cpt-edit-rows" method="GET">
	<table>
		<tbody id="cpt-edit-row-home">
			<tr id="cpt-edit-row">
				<td colspan="3">
					<p class="cpt-section-header">
						<?php
						printf(
							// Translators: %s is the project type label.
							esc_html__( 'Edit %s Type', 'client-power-tools' ),
							esc_html( Common\cpt_get_projects_label( 'singular' ) )
						);
						?>
					</p>
					<input 
						name="edit_project_type_id" 
						id="edit_project_type_id" 
						type="hidden"
					>
					<div class="form-field form-required term-name-wrap">
						<label for="edit_project_type">
							<?php
							printf(
								// Translators: %s is the project type label.
								esc_html__( '%s Type', 'client-power-tools' ),
								esc_html( $projects_label[0] )
							);
							?>
							<small>(<?php esc_html_e( 'required', 'client-power-tools' ); ?>)</small>	
						</label>
						<input 
							name="edit_project_type" 
							id="edit_project_type" 
							class="regular-text" 
							type="text" 
							required aria-required="true"
						>
					</div>
					<div class="form-field">
						<label for="edit_project_type_stages">
							<?php
							printf(
								// Translators: %s is the project type label.
								esc_html__( '%s Type Stages', 'client-power-tools' ),
								esc_html( $projects_label[0] )
							);
							?>
						</label>
						<textarea 
							name="edit_project_type_stages" 
							id="edit_project_type_stages" 
							class="small-text" 
							rows="5" 
							placeholder="Stage One&#10;Stage Two&#10;Stage Three&#10;&hellip;"
						></textarea>
						<p class="description">
							<?php
							printf(
								// Translators: %s is the project type label.
								esc_html__( 'Enter one stage per line. These stages will only apply to this %s type.', 'client-power-tools' ),
								esc_html( strtolower( $projects_label[0] ) )
							);
							?>
						</p>
					</div>
					<div class="submit cpt-row gap-sm">
						<input name="submit" id="submit" class="button button-primary wp-element-button" type="submit" value="<?php esc_attr_e( 'Save', 'client-power-tools' ); ?>">
						<button 
							id="edit-cancel" 
							class="button button-secondary wp-element-button"
						>
							<?php esc_html_e( 'Cancel', 'client-power-tools' ); ?>
						</button>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</form>