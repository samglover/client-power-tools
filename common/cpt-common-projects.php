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

function cpt_get_project_data($projects_post_id) {
  if (!$projects_post_id) return;
  $project_data = [
    'projects_post_id' => $projects_post_id,
    'project_id'      => get_post_meta($projects_post_id, 'cpt_project_id', true),
    'project_name'    => get_the_title($projects_post_id),
    'project_status'  => get_post_meta($projects_post_id, 'cpt_project_status', true),
    'clients_user_id' => get_post_meta($projects_post_id, 'cpt_client_id', true),
    'manager_id'      => cpt_get_client_manager_id(get_post_meta($projects_post_id, 'cpt_client_id', true)),
  ];
  return $project_data;
}