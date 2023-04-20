<?php
  namespace Client_Power_Tools\Core\Admin;
  use Client_Power_Tools\Core\Common;

  $client_ids = Common\cpt_get_clients(['fields' => 'ID']);
?>

<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
  <?php wp_nonce_field('cpt_project_updated', 'cpt_project_updated_nonce'); ?>
  <input name="action" value="cpt_project_updated" type="hidden">
  <input name="projects_post_id" value="<?php echo $projects_post_id; ?>" type="hidden">
  <div class="form-field form-required">
    <label for="client_id"><?php _e('Client', 'client-power-tools'); ?> <small>(required)</small></label>
    <?php if ($client_ids) { ?>
      <select name="client_id" id="client_id" required>
        <?php foreach ($client_ids as $client_id) { ?>
          <option value="<?php echo $client_id; ?>"<?php selected(get_post_meta($projects_post_id, 'cpt_client_id', true), $client_id); ?>><?php echo Common\cpt_get_name($client_id); ?></option>
        <?php } ?>
      </select>
    <?php } ?>
  </div>
  <div class="cpt-row">
    <div class="form-field">
      <label for="project_id">Project ID</label>
      <input name="project_id" id="project_id" type="text" autocapitalize="none" autocorrect="off" value="<?php echo $project_data['project_id']; ?>">
    </div>
    <div class="form-field form-required">
      <label for="project_name">Project Name <small>(required)</small></label>
      <input name="project_name" id="project_name" class="regular-text" type="text" required aria-required="true" value="<?php echo $project_data['project_name']; ?>">
    </div>
  </div>
  <div class="cpt-row">
    <div class="form-field">
      <label for="project_type">Project Type</label>
      <?php 
        $project_type = get_post_meta($projects_post_id, 'cpt_project_type', true);
        echo cpt_get_project_type_select('project_type', $project_type);
      ?>
    </div>
    <div class="form-field">
      <label for="project_stage">Project Stage</label>
      <?php 
        $current_project_stage = get_post_meta($projects_post_id, 'cpt_project_stage', true);
        echo cpt_get_project_stage_select($projects_post_id, 'project_stage', $current_project_stage);
      ?>
    </div>
    <div class="form-field">
      <label for="project_status">Project Status</label>
      <?php echo cpt_get_status_select('cpt_project_statuses', 'project_status', 'cpt_default_project_status') ?>
    </div>
  </div>
  <?php 
    $custom_fields = Common\cpt_custom_client_fields();
    if ($custom_fields) { 
      foreach($custom_fields as $field) {
        ?>
          <div class="form-field>
            <label for="<?php echo $field['id']; ?>"><?php echo $field['label']; ?><br /><small>(<?php echo $field['required'] ? 'required' : 'optional'; ?>)</small></label>
            <input name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" class="regular-text" type="<?php echo $field['type']; ?>" data-required="<?php echo $field['required'] ? 'true' : 'false'; ?>">
          </div>
        <?php
      }
    } 
  ?>
  <p class="submit">
    <input name="submit" id="submit" class="button button-primary" type="submit" value="Update Project">
  </p>
</form>
