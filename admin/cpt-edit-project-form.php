<?php
  namespace Client_Power_Tools\Core\Admin;
  use Client_Power_Tools\Core\Common;

  $client_ids = Common\cpt_get_clients(['fields' => 'ID']);
?>

<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
  <?php wp_nonce_field('cpt_project_updated', 'cpt_project_updated_nonce'); ?>
  <input name="action" value="cpt_project_updated" type="hidden">
  <input name="projects_post_id" value="<?php echo $projects_post_id; ?>" type="hidden">
  <table class="form-table" role="presentation">
    <tbody>
      <tr>
        <th scope="row">
          <label for="client_id">Client<br /><small>(required)</small></label>
        </th>
        <td>
          <?php if ($client_ids) { ?>
            <select name="client_id" id="client_id" required>
              <?php foreach ($client_ids as $client_id) { ?>
                <option value="<?php echo $client_id; ?>"<?php selected(get_post_meta($projects_post_id, 'cpt_client_id', true), $client_id); ?>><?php echo Common\cpt_get_name($client_id); ?></option>
              <?php } ?>
            </select>
          <?php } ?>
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="project_name">Project Name<br /><small>(required)</small></label>
        </th>
        <td>
          <input name="project_name" id="project_name" class="regular-text" type="text" required aria-required="true" value="<?php echo $project_data['project_name']; ?>">
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="project_id">Project ID<br /><small>(optional)</small></label>
        </th>
        <td>
          <input name="project_id" id="project_id" class="regular-text" type="text" autocapitalize="none" autocorrect="off" value="<?php echo $project_data['project_id']; ?>">
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="project_status">Project Status</label>
        </th>
        <td>
          <?php echo cpt_get_status_select('cpt_project_statuses', 'project_status', 'cpt_default_project_status') ?>
        </td>
      </tr>
      <?php $custom_fields = Common\cpt_custom_client_fields(); ?>
      <?php if ($custom_fields) { ?>
        <?php foreach($custom_fields as $field) { ?>
          <tr>
            <th scope="row">
              <label for="<?php echo $field['id']; ?>"><?php echo $field['label']; ?><br /><small>(<?php echo $field['required'] ? 'required' : 'optional'; ?>)</small></label>
            </th>
            <td>
              <input name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" class="regular-text" type="<?php echo $field['type']; ?>" data-required="<?php echo $field['required'] ? 'true' : 'false'; ?>">
            </td>
          </tr>
        <?php } ?>
      <?php } ?>
    </tbody>
  </table>
  <p class="submit">
    <input name="submit" id="submit" class="button button-primary" type="submit" value="Update Project">
  </p>
</form>
