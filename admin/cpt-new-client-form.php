<?php namespace Client_Power_Tools\Core\Admin; ?>

<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
  <?php wp_nonce_field('cpt_new_client_added', 'cpt_new_client_nonce'); ?>
  <input name="action" value="cpt_new_client_added" type="hidden">
  <table class="form-table" role="presentation">
    <tbody>
      <tr>
        <th scope="row">
          <label for="first_name">First Name<br /><small>(required)</small></label>
        </th>
        <td>
          <input name="first_name" id="first_name" class="regular-text" type="text" required aria-required="true">
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="last_name">Last Name<br /><small>(required)</small></label>
        </th>
        <td>
          <input name="last_name" id="last_name" class="regular-text" type="text" required aria-required="true">
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="email">Email Address<br /><small>(required)</small></label>
        </th>
        <td>
          <input name="email" id="email" class="regular-text" type="text" required aria-required="true" autocapitalize="none" autocorrect="off">
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="client_id">Client ID<br /><small>(optional)</small></label>
        </th>
        <td>
          <input name="client_id" id="client_id" class="regular-text" type="text" autocapitalize="none" autocorrect="off">
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="client_manager">Client Manager</label>
        </th>
        <td>
          <?php echo cpt_get_client_manager_select() ?>
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="client_status">Client Status</label>
        </th>
        <td>
          <?php echo cpt_get_client_statuses_select() ?>
        </td>
      </tr>
    </tbody>
  </table>
  <p class="submit">
    <input name="submit" id="submit" class="button button-primary" type="submit" value="<?php _e('Add Client', 'client-power-tools'); ?>">
  </p>
</form>
