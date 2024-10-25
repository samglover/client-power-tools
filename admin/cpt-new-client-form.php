<?php
	namespace Client_Power_Tools\Core\Admin;

	use Client_Power_Tools\Core\Common;
?>

<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" autocapitalize="none" autocorrect="off" autocomplete="off">
	<?php wp_nonce_field( 'cpt_new_client_added', 'cpt_new_client_nonce' ); ?>
	<input name="action" value="cpt_new_client_added" type="hidden">
	<div class="cpt-row">
	<div class="form-field">
		<label for="client_id"><?php esc_html_e( 'Client ID', 'client-power-tools' ); ?></label>
		<input name="client_id" id="client_id" class="regular-text" type="text">
	</div>
		<div class="form-field span-3">
		<label for="client_name"><?php esc_html_e( 'Client Name', 'client-power-tools' ); ?></label>
		<input name="client_name" id="client_name" class="regular-text" type="text">
		</div>
	</div>
	<div class="cpt-row">
	<p class="cpt-section-header span-3"><?php esc_html_e( 'Primary Contact', 'client-power-tools' ); ?></p>
	</div>
	<div class="cpt-row">
		<div class="form-field span-2">
			<label for="first_name">
				<?php esc_html_e( 'First Name', 'client-power-tools' ); ?>
				<small class="required">(<?php esc_html_e( 'required', 'client-power-tools' ); ?>)</small>
			</label>
			<input 
				name="first_name" 
				id="first_name" 
				class="regular-text" 
				type="text" 
				required aria-required="true"
			>
		</div>
		<div class="form-field span-2">
			<label for="last_name">
				<?php esc_html_e( 'Last Name', 'client-power-tools' ); ?>
				<small class="required">(<?php esc_html_e( 'required', 'client-power-tools' ); ?>)</small>
			</label>
			<input 
				name="last_name" 
				id="last_name" 
				class="regular-text" 
				type="text" 
				required aria-required="true"
			>
		</div>
	</div>
	<div class="cpt-row">
		<div class="form-field span-3">
			<label for="email">
				<?php esc_html_e( 'Email Address', 'client-power-tools' ); ?>
				<small class="required">(<?php esc_html_e( 'required', 'client-power-tools' ); ?>)</small>
			</label>
			<input 
				name="email" 
				id="email" 
				class="regular-text" 
				type="text" 
				required aria-required="true"
			>
		</div>
	</div>
	<div class="cpt-row">
	<p class="cpt-section-header span-3"><?php esc_html_e( 'Additional Contacts', 'client-power-tools' ); ?></p>
	</div>
	<div class="cpt-row">
	<div class="form-field span-3">
		<label for="email_ccs"><?php esc_html_e( 'Email Addresses to CC When Sending Messages', 'client-power-tools' ); ?></label>
		<textarea name="email_ccs" id="email_ccs" class="regular-text" rows="3" type="text"></textarea>
		<p class="description"><?php esc_html_e( 'Enter one email address per line.', 'client-power-tools' ); ?></p>
	</div>
	</div>
	<div class="cpt-row">
	<p class="cpt-section-header span-3"><?php esc_html_e( 'Client Details', 'client-power-tools' ); ?></p>
	</div>
	<div class="cpt-row">
	<div class="form-field span-2">
		<label for="client_manager"><?php esc_html_e( 'Client Manager', 'client-power-tools' ); ?></label>
		<?php
		$client_manager_select = cpt_get_client_manager_select();
		if ( $client_manager_select ) {
			echo wp_kses_post( $client_manager_select );
		}
		?>
	</div>
	<div class="form-field">
		<label for="client_status"><?php esc_html_e( 'Client Status', 'client-power-tools' ); ?></label>
		<?php
		$cpt_status_select = cpt_get_status_select( 'cpt_client_statuses', 'client_status', 'cpt_default_client_status' );
		if ( $cpt_status_select ) {
			echo wp_kses_post( $cpt_status_select );
		}
		?>
	</div>
	</div>
	<?php $custom_fields = Common\cpt_custom_client_fields(); ?>
	<?php if ( $custom_fields ) { ?>
	<div class="cpt-row">
		<?php foreach ( $custom_fields as $field ) { ?>
		<div class="form-field span-2">
			<label for="<?php echo esc_attr( $field['id'] ); ?>">
			<?php echo esc_html( $field['label'] ); ?>
				<?php if ( $field['required'] ) { ?>
					<small class="cpt-required">(<?php esc_html_e( 'required', 'client-power-tools' ); ?>)</small>
				<?php } ?>
			</label>
			<input 
			name="<?php echo esc_attr( $field['id'] ); ?>" 
			id="<?php echo esc_attr( $field['id'] ); ?>" 
			class="regular-text" 
			type="<?php echo esc_attr( $field['type'] ); ?>" 
			data-required="<?php echo $field['required'] ? esc_attr( 'true' ) : esc_attr( 'false' ); ?>"
			>
		</div>
		<?php } ?>
	</div>
	<?php } ?>
	<p class="submit">
	<input 
		name="submit" 
		id="submit" 
		class="button button-primary wp-element-button" 
		type="submit" 
		value="<?php esc_attr_e( 'Add Client', 'client-power-tools' ); ?>"
	>
	</p>
</form>
