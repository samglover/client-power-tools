<?php

namespace Client_Power_Tools\Core\Common;

function cpt_is_project($post_id) {
  if (!$post_id) return false;
  if (get_post_type($post_id) == 'cpt_project') return true;
}


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
  if (cpt_is_client_dashboard('projects') && $id == $client_dashboard_id && in_the_loop()) $title = $title . ': ' . cpt_get_projects_label('plural');
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


function cpt_get_projects($clients_user_id = null) {
  if (!$clients_user_id) $clients_user_id = get_current_user_id();
  if (!cpt_is_client($clients_user_id)) return;
  $projects_label = cpt_get_projects_label();
  $projects = new \WP_Query([
    'meta_key'        => 'cpt_client_id',
    'meta_value'      => $clients_user_id,
    'orderby'         => isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'project',
    'order'           => isset($_REQUEST['order']) ? sanitize_key($_REQUEST['order']) : 'ASC',
    'post_type'       => 'cpt_project',
    'posts_per_page'  => -1,
  ]);
  if ($projects->have_posts()) :
    ?>
      <section class="cpt-projects-list">
        <?php while ($projects->have_posts()) : $projects->the_post(); ?>
          <?php $projects_post_id = get_the_ID(); ?>
          <div class="cpt-project">
            <h3 class="cpt-project-title">
              <?php 
                if (is_admin()) {
                  $project_url = get_admin_url() . 'admin.php?page=cpt-projects&projects_post_id=' . $projects_post_id;
                } else {
                  $project_url = cpt_get_client_dashboard_url() . '?tab=projects&projects_post_id=' . $projects_post_id;
                }
              ?>
              <a href="<?php echo $project_url; ?>"><?php the_title(); ?></a>
            </h3>
            <?php cpt_get_project_progress_bar($projects_post_id); ?>
            <?php cpt_get_project_meta($projects_post_id); ?>
          </div>
        <?php endwhile; ?>
      </section>
    <?php
  endif;
}

function cpt_get_project_progress_bar($projects_post_id) {
  if (!$projects_post_id) $projects_post_id = get_the_ID();
  if (!$projects_post_id) return;

  $project_type = get_term(get_post_meta($projects_post_id, 'cpt_project_type', true));
  $project_stages = array_map('trim', explode("\n", get_term_meta($project_type->term_id, 'cpt_project_type_stages', true)));
  $current_stage = sanitize_text_field(get_post_meta($projects_post_id, 'cpt_project_stage', true));
  $current_stage_key = array_search($current_stage, $project_stages);
  $project_progress = $current_stage_key ? (intval($current_stage_key) + 1) / count($project_stages) * 100 : false;
  
  if ($current_stage) {
    ?>
      <div class="cpt-project-stage-progress">
        <div class="cpt-stage-container">
          <div class="cpt-stage-labels">
            <?php
              foreach ($project_stages as $stage) {
                $classes = 'cpt-stage-label';
                if ($stage == $current_stage) $classes .= ' current';
                echo '<div class="' . $classes . '">' . $stage . '</div>';
              }
            ?>
          </div>
          <div class="cpt-stage-progress-container">
            <div class="cpt-stage-progress-indicator" style="width:<?php if ($project_progress) echo $project_progress; ?>%;"></div>
          </div>
        </div>
      </div>
    <?php
  }
}

function cpt_get_project_meta($projects_post_id) {
  if (!$projects_post_id) $projects_post_id = get_the_ID();
  if (!$projects_post_id || !cpt_is_project($projects_post_id)) return;

  $projects_label = cpt_get_projects_label();
  $project_data = cpt_get_project_data($projects_post_id);
  ?>
    <ul class="cpt-project-meta">
      <?php 
        if ($project_data['project_id']) echo '<li class="cpt-project-id">' . sprintf(__('%1$s ID: %2$s', 'client-power-tools'), $projects_label[0], '<strong>' . $project_data['project_id'] . '</strong>') . '</li>';
        if ($project_data['project_status']) echo '<li class="cpt-project-status">' . sprintf(__('%1$s Status: %2$s','client-power-tools'), $projects_label[0], '<strong>' . $project_data['project_status'] . '</strong>') . '</li>';
        if ($project_data['project_type']) echo '<li class="cpt-project-type">' . sprintf(__('%1$s Type: %2$s','client-power-tools'), $projects_label[0], '<strong>' . $project_data['project_type'] . '</strong>') . '</li>';
      ?>
    </ul>
  <?php
}