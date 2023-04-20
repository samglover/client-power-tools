<?php

namespace Client_Power_Tools\Core\Common;

function cpt_get_projects_label($n = null) {
  $projects_label = get_option('cpt_projects_label');
  switch ($n) {
    case ('singular'):
      return $projects_label[0];
      break;
    case ('plural'):
      return $projects_label[1];
      break;
    default:
      return  $projects_label;
  }
}


add_filter('the_title', __NAMESPACE__ . '\cpt_projects_page_title', 10, 2);
function cpt_projects_page_title($title, $id) {
  $client_dashboard_id = get_option('cpt_client_dashboard_page_selection');
  if (cpt_is_projects() && $id == $client_dashboard_id && in_the_loop()) $title = $title . ': ' . cpt_get_projects_label('plural');
  return $title;
}


function cpt_get_project_data($projects_post_id) {
  if (!$projects_post_id) return;
  $project_data = [
    'projects_post_id'  => $projects_post_id,
    'project_id'        => get_post_meta($projects_post_id, 'cpt_project_id', true),
    'project_name'      => get_the_title($projects_post_id),
    'project_type'      => get_term(get_post_meta($projects_post_id, 'cpt_project_type', true))->name,
    'project_status'    => get_post_meta($projects_post_id, 'cpt_project_status', true),
    'project_stage'     => get_post_meta($projects_post_id, 'cpt_project_stage', true),
    'clients_user_id'   => get_post_meta($projects_post_id, 'cpt_client_id', true),
    'managers_user_id'  => cpt_get_client_manager_id(get_post_meta($projects_post_id, 'cpt_client_id', true)),
  ];
  return $project_data;
}


function cpt_get_clients_projects($clients_user_id = null) {
  if (!$clients_user_id) $clients_user_id = get_current_user_id();
  if (!cpt_is_client($clients_user_id)) return;
  $projects = new \WP_Query([
    'meta_key'        => 'cpt_client_id',
    'meta_value'      => $clients_user_id,
    'orderby'         => isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'project',
    'order'           => isset($_REQUEST['order']) ? sanitize_key($_REQUEST['order']) : 'ASC',
    'post_type'       => 'cpt_project',
    'posts_per_page'  => -1,
  ]);
  return $projects;
}


function cpt_clients_projects($clients_user_id = null) {
  if (!$clients_user_id) $clients_user_id = get_current_user_id();
  if (!cpt_is_client($clients_user_id)) return;
  $projects_label = cpt_get_projects_label();
  $projects = cpt_get_clients_projects($clients_user_id);
  if ($projects->have_posts()) :
    ?>
      <section class="cpt-projects-list">
        <?php while ($projects->have_posts()) : $projects->the_post(); ?>
          <?php 
            $post_id = get_the_ID(); 
            $project_status = get_post_meta($post_id, 'cpt_project_status', true);
            $project_type = '';
            $project_classes = 'cpt-project cpt-project-status-' . sanitize_title(strtolower($project_status));
            $project_id = get_post_meta($post_id, 'cpt_project_id', true);
          ?>
          <div class="<?php echo $project_classes; ?>">
            <h3 class="cpt-project-title">
              <?php 
                if (is_admin()) {
                  $project_url = get_admin_url() . 'admin.php?page=cpt-projects&projects_post_id=' . $post_id;
                } else {
                  $project_url = cpt_get_client_dashboard_url() . '?tab=projects&projects_post_id=' . $post_id;
                }
              ?>
              <a href="<?php echo $project_url; ?>"><?php the_title(); ?></a>
            </h3>
            <ul class="cpt-project-meta">
              <?php 
                if ($project_id) echo '<li class="cpt-project-id">' . sprintf(__('%1$s ID: %2$s', 'client-power-tools'), $projects_label[0], $project_id) . '</li>';
                if ($project_status) echo '<li class="cpt-project-status">' . sprintf(__('%1$s Status: %2$s','client-power-tools'), $projects_label[0], $project_status) . '</li>';
                if ($project_type) echo '<li class="cpt-project-type">' . sprintf(__('%1$s Type: %2$s','client-power-tools'), $projects_label[0], $project_type) . '</li>';
              ?>
            </ul>
          </div>
        <?php endwhile; ?>
      </section>
    <?php
  endif;
}

function cpt_project_page($projects_post_id) {
  $project_data = cpt_get_project_data($projects_post_id);
  ?>
    <p><small><a href="<?php echo remove_query_arg('projects_post_id'); ?>">&larr; <?php _e('Back to Projects', 'client-power-tools'); ?></a></small></p>
    <h1>
      <?php echo $project_data['project_name']; ?>
      <?php if ($project_data['project_id']) { ?>
        <span style="color:silver; font-weight: normal;">(<?php echo $project_data['project_id']; ?>)</span>
      <?php } ?>
    </h1>
    <p><?php echo __('Status', 'client-power-tools') . ': ' . $project_data['project_status']; ?></p>
  <?php 
}