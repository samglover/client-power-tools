<?php
  namespace Client_Power_Tools\Core\Admin;
  use Client_Power_Tools\Core\Common;
?>

<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
  <?php wp_nonce_field('cpt_new_client_added', 'cpt_new_client_nonce'); ?>
  <input name="action" value="cpt_new_client_added" type="hidden">
  <div class="cpt-row">
    <div class="form-field span-2">
      <label for="first_name"><?php printf(__('First Name %s(required)%s', 'client-power-tools'), '<small>', '</small>'); ?></label>
      <input name="first_name" id="first_name" class="regular-text" type="text" required aria-required="true">
    </div>
    <div class="form-field span-2">
      <label for="last_name"><?php printf(__('Last Name %s(required)%s', 'client-power-tools'), '<small>', '</small>'); ?></label>
      <input name="last_name" id="last_name" class="regular-text" type="text" required aria-required="true">
    </div>
  </div>
  <div class="cpt-row">
    <div class="form-field span-3">
      <label for="email"><?php printf(__('Email Address %s(required)%s', 'client-power-tools'), '<small>', '</small>'); ?></label>
      <input name="email" id="email" class="regular-text" type="text" required aria-required="true" autocapitalize="none" autocorrect="off">
    </div>
  </div>
  <div class="cpt-row">
    <div class="form-field">
      <label for="client_id"><?php _e('Client ID', 'client-power-tools'); ?></label>
      <input name="client_id" id="client_id" class="regular-text" type="text" autocapitalize="none" autocorrect="off">
    </div>
    <div class="form-field">
      <label for="client_manager"><?php _e('Client Manager', 'client-power-tools'); ?></label>
      <?php echo cpt_get_client_manager_select() ?>
    </div>
    <div class="form-field">
      <label for="client_status"><?php _e('Client Status', 'client-power-tools'); ?></label>
      <?php echo cpt_get_status_select('cpt_client_statuses', 'client_status', 'cpt_default_client_status') ?>
    </div>
  </div>
  <?php $custom_fields = Common\cpt_custom_client_fields(); ?>
  <?php if ($custom_fields) { ?>
    <div class="cpt-row">
      <?php foreach($custom_fields as $field) { ?>
        <div class="form-field">
          <label for="<?php echo $field['id']; ?>"><?php echo $field['label']; echo $field['required'] ? ' <small>(required)</small>' : ''; ?></label>
          <input name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" class="regular-text" type="<?php echo $field['type']; ?>" data-required="<?php echo $field['required'] ? 'true' : 'false'; ?>">
        </div>
      <?php } ?>
    </div>
  <?php } ?>
  <p class="submit">
    <input name="submit" id="submit" class="button button-primary" type="submit" value="<?php _e('Add Client', 'client-power-tools'); ?>">
  </p>
</form>
