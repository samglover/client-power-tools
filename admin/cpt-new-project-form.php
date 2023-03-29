<?php
  namespace Client_Power_Tools\Core\Admin;
  use Client_Power_Tools\Core\Common;
?>

<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
  <?php wp_nonce_field('cpt_new_project_added', 'cpt_new_project_nonce'); ?>
  <input name="action" value="cpt_new_project_added" type="hidden">
  <table class="form-table" role="presentation">
    <tbody>
      <tr>
        <th scope="row">
          <label for="client_id">Client<br /><small>(required)</small></label>
        </th>
        <td>
          <?php 
            if ($clients) {
              ?>
                <select name="client_id" id="client_id" required>
                  <option disabled selected value><?php _e('Select client.', 'client-power-tools'); ?></option>
                  <?php
                    foreach ($clients as $client) {
                      echo '<option value="' . $client . '">' . Common\cpt_get_name($client) . '</option>';
                    }
                  ?>
                </select>
              <?php
            }
          ?>
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="project_name">Project Name<br /><small>(required)</small></label>
        </th>
        <td>
          <input name="project_name" id="project_name" class="regular-text" type="text" required aria-required="true">
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="project_id">Project ID<br /><small>(optional)</small></label>
        </th>
        <td>
          <input name="project_id" id="project_id" class="regular-text" type="text" autocapitalize="none" autocorrect="off">
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
    <input name="submit" id="submit" class="button button-primary" type="submit" value="<?php _e('Add Project', 'client-power-tools'); ?>">
  </p>
</form>
