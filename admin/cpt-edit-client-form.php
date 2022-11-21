<?php namespace Client_Power_Tools\Core\Admin; ?>

<?php
  $fields = apply_filters('client_fields', [
      [
        'id' => 'first_name',
        'label' => 'First Name',
        'required' => true,
        'type' => 'text',
      ],
      [
        'id' => 'last_name',
        'label' => 'Last Name',
        'required' => true,
        'type' => 'text',
      ],
      [
        'id' => 'email',
        'label' => 'Email Address',
        'required' => true,
        'type' => 'email',
      ],
      [
        'id' => 'client_id',
        'label' => 'Client ID',
        'required' => false,
        'type' => 'text',
      ],
    ],
  );
?>

<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
  <?php wp_nonce_field('cpt_client_updated', 'cpt_client_updated_nonce'); ?>
  <input name="action" value="cpt_client_updated" type="hidden">
  <input name="clients_user_id" value="<?php echo $client_data['user_id']; ?>" type="hidden">
  <table class="form-table" role="presentation">
    <tbody>
      <?php foreach($fields as $field) { ?>
        <tr>
          <th scope="row">
            <label for="<?php echo $field['id']; ?>"><?php echo $field['label']; ?><br /><small>(<?php echo $field['required'] ? 'required' : 'optional'; ?>)</small></label>
          </th>
          <td>
            <input name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" class="regular-text" type="<?php echo $field['type']; ?>" data-required="<?php echo $field['required'] ? 'true' : 'false'; ?>" value="<?php echo $client_data[$field['id']]; ?>">
          </td>
        </tr>
      <?php } ?>
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
    </tbody>
  </table>
  <p class="submit">
    <input name="submit" id="submit" class="button button-primary" type="submit" value="Update Client">
  </p>
</form>
