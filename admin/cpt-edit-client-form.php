<?php
  namespace Client_Power_Tools\Core\Admin;
  use Client_Power_Tools\Core\Common;
?>

<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
  <?php wp_nonce_field('cpt_client_updated', 'cpt_client_updated_nonce'); ?>
  <input name="action" value="cpt_client_updated" type="hidden">
  <input name="clients_user_id" value="<?php echo $client_data['user_id']; ?>" type="hidden">
  <table class="form-table" role="presentation">
    <tbody>
      <tr>
        <th scope="row">
          <label for="first_name">First Name<br /><small>(required)</small></label>
        </th>
        <td>
          <input name="first_name" id="first_name" class="regular-text" type="text" data-required="true" value="<?php echo $client_data['first_name']; ?>">
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="last_name">Last Name<br /><small>(required)</small></label>
        </th>
        <td>
          <input name="last_name" id="last_name" class="regular-text" type="text" data-required="true" value="<?php echo $client_data['last_name']; ?>">
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="email">Email Address<br /><small>(required)</small></label>
        </th>
        <td>
          <input name="email" id="email" class="regular-text" type="text" data-required="true" autocapitalize="none" autocorrect="off" value="<?php echo $client_data['email']; ?>">
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="client_id">Client ID<br /><small>(optional)</small></label>
        </th>
        <td>
          <input name="client_id" id="client_id" class="regular-text" type="text" autocapitalize="none" autocorrect="off" value="<?php echo $client_data['client_id']; ?>">
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="client_manager">Client Manager</label>
        </th>
        <td>
          <?php echo cpt_get_client_manager_select('', $client_data['manager_id']); ?>
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="client_status">Client Status</label>
        </th>
        <td>
          <?php echo cpt_get_client_statuses_select('', $client_data['status']); ?>
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
              <input name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" class="regular-text" type="<?php echo $field['type']; ?>" data-required="<?php echo $field['required'] ? 'true' : 'false'; ?>" value="<?php echo $client_data[$field['id']]; ?>">
            </td>
          </tr>
        <?php } ?>
      <?php } ?>
    </tbody>
  </table>
  <p class="submit">
    <input name="submit" id="submit" class="button button-primary" type="submit" value="Update Client">
  </p>
</form>
