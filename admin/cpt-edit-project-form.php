<?php
  namespace Client_Power_Tools\Core\Admin;
  use Client_Power_Tools\Core\Common;

  $client_ids = Common\cpt_get_clients(['fields' => 'ID']);
  $projects_label = Common\cpt_get_projects_label();
?>

<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
  <?php wp_nonce_field('cpt_project_updated', 'cpt_project_updated_nonce'); ?>
  <input name="action" value="cpt_project_updated" type="hidden">
  <input name="projects_post_id" value="<?php echo $projects_post_id; ?>" type="hidden">
  <div class="cpt-row">
    <div class="form-field span-2 form-required">
      <label for="client_id"><?php printf(__('Client %s(required)%s', 'client-power-tools'), '<small>', '</small>'); ?></label>
      <?php if ($client_ids) { ?>
        <select name="client_id" id="client_id" required>
          <?php foreach ($client_ids as $client_id) { ?>
            <option value="<?php echo $client_id; ?>"<?php selected(get_post_meta($projects_post_id, 'cpt_client_id', true), $client_id); ?>><?php echo Common\cpt_get_name($client_id); ?></option>
          <?php } ?>
        </select>
      <?php } ?>
    </div>
  </div>
  <div class="cpt-row">
    <div class="form-field">
      <label for="project_id"><?php printf(__('%s ID', 'client-power-tools'), $projects_label[0]); ?></label>
      <input name="project_id" id="project_id" type="text" autocapitalize="none" autocorrect="off" value="<?php echo $project_data['project_id']; ?>">
    </div>
    <div class="form-field span-3 form-required">
      <label for="project_name"><?php printf(__('%s Name %s(required)%s', 'client-power-tools'), $projects_label[0], '<small>', '</small>'); ?></label>
      <input name="project_name" id="project_name" class="regular-text" type="text" required aria-required="true" value="<?php echo $project_data['project_name']; ?>">
    </div>
  </div>
  <div class="cpt-row">
    <div class="form-field">
      <label for="project_type"><?php printf(__('%s Type', 'client-power-tools'), $projects_label[0]); ?></label>
      <?php 
        $project_type = get_post_meta($projects_post_id, 'cpt_project_type', true);
        echo cpt_get_project_type_select('project_type', $project_type);
      ?>
    </div>
    <div class="form-field">
      <label for="project_stage"><?php printf(__('%s Stage', 'client-power-tools'), $projects_label[0]); ?></label>
      <?php 
        $current_project_stage = get_post_meta($projects_post_id, 'cpt_project_stage', true);
        echo cpt_get_project_stage_select($projects_post_id, 'project_stage', $current_project_stage);
      ?>
    </div>
    <div class="form-field">
      <label for="project_status"><?php printf(__('%s Status', 'client-power-tools'), $projects_label[0]); ?></label>
      <?php echo cpt_get_status_select('cpt_project_statuses', 'project_status', 'cpt_default_project_status') ?>
    </div>
  </div>
  <?php 
    $custom_fields = Common\cpt_custom_client_fields();
    if ($custom_fields) { 
      ?>
        <div class="cpt-row">
          <?php foreach($custom_fields as $field) { ?>
            <div class="form-field>
              <label for="<?php echo $field['id']; ?>"><?php echo $field['label']; echo $field['required'] ? ' <small>(required)</small>' : ''; ?></label>
              <input name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" class="regular-text" type="<?php echo $field['type']; ?>" data-required="<?php echo $field['required'] ? 'true' : 'false'; ?>">
            </div>
          <?php } ?>
        </div>
      <?php
    } 
  ?>
  <p class="submit">
    <input name="submit" id="submit" class="button button-primary" type="submit" value="<?php printf(__('Update %s', 'client-power-tools'), $projects_label[0]); ?>">
  </p>
</form>
