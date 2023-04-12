<?php

namespace Client_Power_Tools\Core\Common;

function cpt_project_list($clients_user_id) {
  $projects = new \WP_Query([
    'meta_query'      => [
      [
        'key'   => 'cpt_client_id',
        'value' => $clients_user_id,
      ],
    ],
    'orderby'         => isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'project',
    'order'           => isset($_REQUEST['order']) ? sanitize_key($_REQUEST['order']) : 'ASC',
    'post_type'       => 'cpt_project',
    'posts_per_page'  => -1,
  ]);

  if ($projects->have_posts()) :
    ?>
      <section class="cpt-projects-list cpt-row">
        <?php while ($projects->have_posts()) : $projects->the_post(); ?>
          <?php 
            $post_id = get_the_ID(); 
            $project_status = get_post_meta($post_id, 'cpt_project_status', true);
            $project_classes = 'cpt-project cpt-project-status-' . sanitize_title(strtolower($project_status));
            $project_id = get_post_meta($post_id, 'cpt_project_id', true);
          ?>
          <div class="<?php echo $project_classes; ?>">
            <h3 class="cpt-project-title">
              <a href="<?php echo get_admin_url() . 'admin.php?page=cpt-projects&projects_post_id=' . $post_id; ?>"><?php the_title(); ?></a>
              <?php if ($project_id) { ?>
                <span style="color:silver; font-weight: normal;">(<?php echo $project_id; ?>)</span>
              <?php } ?>
            </h3>
            <p class="cpt-project-status">Status: <?php echo $project_status; ?></p>
          </div>
        <?php endwhile; ?>
      </section>
    <?php
  endif;
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
    'project_status'    => get_post_meta($projects_post_id, 'cpt_project_status', true),
    'clients_user_id'   => get_post_meta($projects_post_id, 'cpt_client_id', true),
    'manager_id'        => cpt_get_client_manager_id(get_post_meta($projects_post_id, 'cpt_client_id', true)),
  ];
  return $project_data;
}