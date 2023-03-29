<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;
use Client_Power_Tools\Core\Includes;

class Project_List_Table extends Includes\WP_List_Table  {
  function __construct() {
    global $status, $page;
    parent::__construct([
      'singular'  => strtolower(Common\cpt_get_projects_label('singular')),
      'plural'    => strtolower(Common\cpt_get_projects_label('plural')),
      'ajax'      => false,
    ]);
  }

  /**
   * Column Default
   *
   * @param array $item A singular item (one full row's worth of data)
   * @param array $column_name The name/slug of the column to be processed
   * @return string Text or HTML to be placed inside the column <td>
   */
  function column_default($item, $column_name) {
    return $item[$column_name];
  }

  /**
   * Checkboxes
   *
   * @see WP_List_Table::::single_row_columns()
   * @param array $item A singular item (one full row's worth of data)
   * @return string Text to be placed inside the column <td>
   */
  function column_cb($item) {
    return sprintf(
      '<input type="checkbox" name="%1$s[]" value="%2$s" />',
      /* $1%s */ $this->_args['singular'],
      /* $2%s */ $item['ID']
    );
  }

  /**
   * Project Column Method
   */
  function column_project($item) {
    // Return the contents.
    return sprintf('<strong><a href="' . add_query_arg('project_id', $item['ID']) . '">%1$s</a></strong>%2$s',
      /* $1%s */ $item['project'],
      /* $2%s */ $item['project_id'] ? ' <span style="color:silver">(' . $item['project_id'] . ')</span>' : '',
    );
  }

  /**
   * Client Column Method
   */
  function column_client_name($item) {
    // Return the contents.
    return sprintf('<strong><a href="' . Common\cpt_get_client_profile_url($item['ID']) . '">%1$s</a></strong>%2$s',
      /* $1%s */ $item['client_name'],
      /* $2%s */ $item['client_id'] ? ' <span style="color:silver">(' . $item['client_id'] . ')</span>' : '',
    );
  }


  /**
   * Get Columns
   *
   * @see WP_List_Table::::single_row_columns()
   * @return array An associative array containing column information:
   * 'slugs' => 'Visible Titles'.
   */
  function get_columns() {
    $columns = [
      // 'cb'      => '<input type="checkbox" />',
      'project'           => 'Project',
      'client_name'       => 'Client',
      'project_status'    => 'Status',
    ];
    return $columns;
  }


  /**
   * Sortable Columns
   */
  function get_sortable_columns() {
    $sortable_columns = [
      'project'         => ['project', true],
      'client_name'     => ['client_name', false],
      'project_status'  => ['project_status', false],
    ];
    return $sortable_columns;
  }

  /**
   * Bulk Actions
   *
   * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
   */
  function get_bulk_actions() {
    return; // Remove this line to enable bulk actions.
    $actions = [
      'delete'  => 'Delete',
    ];
    return $actions;
  }


  function process_bulk_action() {
    $action = $this->current_action();
    switch ($action) {
      case 'delete':
        wp_die('Delete something.');
        break;
      default:
        return;
        break;
    }
    return;
  }


  function get_views() {
    $params         = explode("\n", get_option('cpt_project_statuses'));
    $current_status = isset($_REQUEST['project_status']) ? sanitize_text_field(urldecode($_REQUEST['project_status'])) : 'all';
    $views          = array();

    array_unshift($params, 'All');

    foreach($params as $key => $val) {
      $class          = '';
      $val            = trim($val);
      $curr_param     = urlencode($val);

      if (
        $current_status == $curr_param || 
        ($curr_param == 'All' && isset($_REQUEST['project_status']) && !$current_status) ||
        ($curr_param == 'All' && !isset($_REQUEST['project_status']))
      ) {
        $class = ' class="current"';
      }

      if ($curr_param == 'All') {
        $link = '<a href="' . remove_query_arg('project_status') . '"' . $class . '>' . $val . '</a>';
      } else {
        $link = '<a href="' . add_query_arg('project_status', $curr_param) . '"' . $class . '>' . $val . '</a>';
      }

      $views[$curr_param] = $link;
    }

    return $views;
  }


  /**
   * Prepare Data for Display
   */
  function prepare_items() {
    /**
     * Column Headers
     */
    $columns  = $this->get_columns();
    $hidden   = [];
    $sortable = $this->get_sortable_columns();

    $this->_column_headers = [$columns, $hidden, $sortable];
    $this->process_bulk_action();

    /**
     * Query Projects
     */
    $projects = new \WP_Query([
      'orderby'         => isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'project',
      'order'           => isset($_REQUEST['order']) ? sanitize_key($_REQUEST['order']) : 'ASC',
      'post_type'       => 'cpt_project',
      'posts_per_page'  => -1,
    ]);
    $data = [];

    if ($projects->have_posts()) : while ($projects->have_posts()) : $projects->the_post();
      $post_id = get_the_ID();
      $clients_user_id = get_post_meta($post_id, 'cpt_client_id', true);
      $data[] = [
        'ID' => $post_id,
        'project' => get_the_title(),
        'project_id' => get_post_meta($post_id, 'cpt_project_id', true),
        'clients_user_id' => $clients_user_id,
        'client_id' => get_user_meta($clients_user_id, 'cpt_client_id', true),
        'client_name' => Common\cpt_get_name(get_post_meta($post_id, 'cpt_client_id', true)),
        'project_status' => get_post_meta($post_id, 'cpt_project_status', true),
      ];
    endwhile; endif;

    // Filters the data set.

    if (isset($_REQUEST['project_status'])) {
      $project_status_filter = sanitize_text_field(urldecode($_REQUEST['project_status']));
      if ($project_status_filter) {
        foreach($data as $i => $project) {
          if ($project['project_status'] !== $project_status_filter) unset($data[$i]);
        }
      }
    }

    /**
     * Pagination
     */
    $total_items = count($data);
    $per_page = 25;
    $current_page = $this->get_pagenum();
    $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

    $this->set_pagination_args([
      'total_items' => $total_items,
      'per_page' => $per_page,
      'total_pages' => ceil($total_items / $per_page),
    ]);

    /**
     * $this->items contains the data that will actually be displayed on the
     * current page.
     */
    $this->items = $data;
  }
}
