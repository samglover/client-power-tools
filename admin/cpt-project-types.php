<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;

function cpt_project_types() {
  if (!current_user_can('cpt_view_projects')) wp_die('<p>' . __('Sorry, you are not allowed to access this page.') . '</p>', 403);
  $projects_label = Common\cpt_get_projects_label();
  ?>
    <div id="cpt-admin" class="wrap">
      <div id="cpt-admin-header">
        <?php echo file_get_contents(CLIENT_POWER_TOOLS_DIR_PATH . 'assets/images/cpt-logo.svg'); ?>
        <div id="cpt-admin-page-title">
          <h1 id="cpt-page-title"><?php printf(__('%s Types', 'client-power-tools'), $projects_label[0]); ?></h1>
          <p id="cpt-subtitle">Client Power Tools</p>
        </div>
      </div>
      <hr class="wp-header-end">
      <div id="col-container">
        <div id="col-left">
          <div class="col-wrap">
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
              <?php wp_nonce_field('cpt_new_project_type_added', 'cpt_new_project_type_nonce'); ?>
              <input name="action" value="cpt_new_project_type_added" type="hidden">
              <table class="form-table" role="presentation">
                <tbody>
                  <tr>
                    <th scope="row">
                      <label for="project_type">
                        <?php printf(__('%s Type', 'client-power-tools'), $projects_label[0]); ?><br />
                        <small>(<?php _e('required', 'client-power-tools'); ?>)</small>
                      </label>
                    </th>
                    <td>
                      <input name="project_type" id="project_type" class="regular-text" type="text" required aria-required="true">
                    </td>
                  </tr>
                </tbody>
              </table>
              <p class="submit">
                <input name="submit" id="submit" class="button button-primary" type="submit" value="<?php printf(__('Add %s Type', 'client-power-tools'), $projects_label[0]); ?>">
              </p>
            </form>
          </div>
        </div>
        <div id="col-right">
          <div class="col-wrap">
          <?php
            $project_types = get_terms([
              'taxonomy' => 'cpt-project-type',
              'hide_empty' => false,
            ]);
            var_dump($project_types);
            if ($project_types) {
              ?>
                <ul>
                  <?php foreach($project_types as $key => $project_type) { ?>
                    <li><?php echo $project_type->name; ?></li>
                  <?php } ?>
                </ul>
              <?php
            }
          ?>
          </div>
        </div>
      </div>
    </div>
  <?php
}

add_action('admin_post_cpt_new_project_type_added', __NAMESPACE__ . '\cpt_process_new_project_type');
function cpt_process_new_project_type() {
  if (!isset($_POST['cpt_new_project_type_nonce']) || !wp_verify_nonce($_POST['cpt_new_project_type_nonce'], 'cpt_new_project_type_added')) exit('Invalid nonce.');

  $new_project_type = wp_insert_term(
    sanitize_text_field($_POST['project_type']),
    'cpt-project-type'
  );

  if (is_wp_error($new_project_type)) {
    $result = 'Project type could not be created. Error message: ' . $new_project_type->get_error_message();
  } else {
    $result = 'Project type created.';
  }

  set_transient('cpt_notice_for_user_' . get_current_user_id(), $result, 15);
  wp_redirect($_POST['_wp_http_referer']);
  exit;
}