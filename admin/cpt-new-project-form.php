<?php
  namespace Client_Power_Tools\Core\Admin;
  use Client_Power_Tools\Core\Common;

  $user_id = isset($_REQUEST['user_id']) ? sanitize_key(intval($_REQUEST['user_id'])) : false;
  $client_ids = Common\cpt_get_clients(['fields' => 'ID']);
?>

<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
  <?php wp_nonce_field('cpt_new_project_added', 'cpt_new_project_nonce'); ?>
  <input name="action" value="cpt_new_project_added" type="hidden">
  <div class="cpt-row">
    <div class="form-field span-2 form-required">
      <label for="client_id">Client <small>(required)</small></label>
      <?php if ($client_ids) { ?>
        <select name="client_id" id="client_id" required>
          <option disabled selected value><?php _e('Select client.', 'client-power-tools'); ?></option>
          <?php
            foreach ($client_ids as $client_id) {
              echo '<option value="' . $client_id . '"' . selected($client_id, $user_id) . '>' . Common\cpt_get_name($client_id) . '</option>';
            }
          ?>
        </select>
      <?php } ?>
    </div>
  </div>
  <div class="cpt-row">
    <div class="form-field">
      <label for="project_id">Project ID</label>
      <input name="project_id" id="project_id" class="regular-text" type="text" autocapitalize="none" autocorrect="off">
    </div>
    <div class="form-field span-3">
      <label for="project_name">Project Name <small>(required)</small></label>
      <input name="project_name" id="project_name" class="regular-text" type="text" required aria-required="true">
    </div>
  </div>
  <div class="cpt-row">
    <div class="form-field span-2">
      <label for="project_type">Project Type</label>
      <?php echo cpt_get_project_type_select('project_type') ?>
    </div>
    <div class="form-field span-2">
      <label for="project_stage"><?php printf(__('%s Stage', 'client-power-tools'), $projects_label[0]); ?></label>
      <?php 
        echo cpt_get_project_stage_select('', 'project_stage');
      ?>
    </div>
  </div>
  <?php $custom_fields = Common\cpt_custom_client_fields(); ?>
  <?php if ($custom_fields) { ?>
    <div class="cpt-row">
      <?php foreach($custom_fields as $field) { ?>
        <div class="form-field">
          <label for="<?php echo $field['id']; ?>"><?php echo $field['label']; ?><br /><small>(<?php echo $field['required'] ? 'required' : 'optional'; ?>)</small></label>
          <input name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" class="regular-text" type="<?php echo $field['type']; ?>" data-required="<?php echo $field['required'] ? 'true' : 'false'; ?>">
      </div>
      <?php } ?>
    </div>
  <?php } ?>
  <p class="submit">
    <input name="submit" id="submit" class="button button-primary" type="submit" value="<?php echo __('Add', 'client-power-tools') . ' ' . Common\cpt_get_projects_label('singular'); ?>">
  </p>
</form>
