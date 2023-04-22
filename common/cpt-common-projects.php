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
  $stage_width = 100 / count($project_stages);

  if ( $current_stage_key == 0) {
    $indicator_width = false;
  } elseif ($current_stage_key == count($project_stages) - 1) {
    $indicator_width = 100;
  } else {
    $indicator_width = $stage_width * $current_stage_key + ($stage_width / 2);
  }
  $indicator_style = $indicator_width ? ' style="width: ' . $indicator_width . '%;"' : '';

  if ($current_stage) {
    ?>
      <div class="cpt-project-stage-progress">
        <p class="cpt-section-header">Progress</p>
        <div class="cpt-stages-container">
          <div class="cpt-stage-progress-container">
            <div class="cpt-stage-progress-indicator"<?php echo $indicator_style; ?>></div>
          </div>
          <div class="cpt-stage-labels">
          <?php
            foreach ($project_stages as $key => $stage) {
              $stage_classes = 'cpt-stage-label';
              if ($key < $current_stage_key) $stage_classes .= ' completed';
              if ($stage == $current_stage) $stage_classes .= ' current';
              if ($key > $current_stage_key) $stage_classes .= ' not-started';
              echo '<div class="' . $stage_classes . '" ' . 'style="width: ' . $stage_width . '%;"' . '>' . $stage . '</div>';
            }
          ?>
          </div>
        </div>
      </div>
    <?php
  }
}

function cpt_get_project_meta($projects_post_id) {
  if (!$projects_post_id) $projects_post_id = get_the_ID();
  if (!$projects_post_id || !cpt_is_project($projects_post_id)) return;

  $project_data = cpt_get_project_data($projects_post_id);
  ?>
    <ul class="cpt-project-meta">
      <?php 
        if ($project_data['project_id']) echo '<li class="cpt-project-id">' . __('ID', 'client-power-tools') . ': <strong>' . $project_data['project_id'] . '</strong>' . '</li>';
        if ($project_data['project_type']) echo '<li class="cpt-project-type">' . __('Type','client-power-tools') . ': <strong>' . $project_data['project_type'] . '</strong>' . '</li>';
        if ($project_data['project_status']) echo '<li class="cpt-project-status">' . __('Status','client-power-tools') . ': <strong>' . $project_data['project_status'] . '</strong>' . '</li>';
      ?>
    </ul>
  <?php
}