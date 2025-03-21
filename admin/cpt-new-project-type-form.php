<?php
/**
 * New project type form
 *
 * @file       cpt-new-project-type-form.php
 * @package    Client_Power_Tools
 * @subpackage Core\Admin
 * @since      1.7.0
 */

namespace Client_Power_Tools\Core\Admin;

?>

<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">
	<?php wp_nonce_field( 'cpt_new_project_type_added', 'cpt_new_project_type_nonce' ); ?>
	<input 
		name="action" 
		value="cpt_new_project_type_added" 
		type="hidden"
	>
	<div class="form-field form-required term-name-wrap">
		<label for="project_type">
			<?php
			echo esc_html(
				sprintf(
					// Translators: %s is the singular project label.
					__( '%s Type', 'client-power-tools' ),
					$projects_label[0]
				)
			);
			?>
			<small class="required">(<?php esc_html_e( 'required', 'client-power-tools' ); ?>)</small>
		</label>
		<input 
			name="project_type" 
			id="project_type" 
			class="regular-text" 
			type="text" 
			required aria-required="true"
		>
	</div>
	<div class="form-field">
		<label for="project_type_stages">
			<?php
			echo esc_html(
				sprintf(
					// Translators: %s is the singular project label.
					__( '%s Type Stages', 'client-power-tools' ),
					$projects_label[0]
				)
			);
			?>
		</label>
		<textarea name="project_type_stages" class="small-text" rows="5" placeholder="Stage One&#10;Stage Two&#10;Stage Three&#10;&hellip;"></textarea>
		<p class="description">
			<?php
			echo esc_html(
				sprintf(
					// Translators: %s is the singular project label.
					__( 'Enter one stage per line. These stages will only apply to this %s type.', 'client-power-tools' ),
					strtolower( $projects_label[0] )
				)
			);
			?>
		</p>
	</div>
	<p class="submit">
		<input 
			name="submit" 
			id="submit" 
			class="button button-primary wp-element-button" 
			type="submit" 
			value="
				<?php
				echo esc_html(
					sprintf(
						// Translators: %s is the singular project label.
						__( 'Add %s Type', 'client-power-tools' ),
						esc_html( $projects_label[0] )
					)
				);
				?>
			"
		>
	</p>
</form>