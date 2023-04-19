<?php

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Common;
use Client_Power_Tools\Core\Includes;

class Project_Types_List_Table extends Includes\WP_List_Table  {
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
   * Get Columns
   *
   * @see WP_List_Table::::single_row_columns()
   * @return array An associative array containing column information:
   * 'slugs' => 'Visible Titles'.
   */
  function get_columns() {
    $columns = [
      'project_type' => sprintf(__('%s Type', 'client-power-tools'), Common\cpt_get_projects_label('singular')),
      'project_count' => sprintf(__('%s Count', 'client-power-tools'), Common\cpt_get_projects_label('singular')),
    ];
    return $columns;
  }


  /**
   * Sortable Columns
   */
  function get_sortable_columns() {
    $sortable_columns = [
      'project_type' => ['project_type', true],
      'project_count' => ['project_count', false],
    ];
    return $sortable_columns;
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

    /**
     * Query Projects
     */
    $project_types = get_terms([
      'taxonomy' => 'cpt-project-type',
      'hide_empty' => false,
      [
        'orderby' => isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'name',
        'order' => isset($_REQUEST['order']) ? sanitize_key($_REQUEST['order']) : 'ASC',
      ],
    ]);
    $data = [];

    if ($project_types) {
      foreach($project_types as $project_type) {
        $projects = get_posts([
          'fields' => 'ids',
          'numberposts' => -1,
          'post_type' => 'cpt_project',
          'tax_query' => [
            [
              'field' => 'slug',
              'taxonomy' => 'cpt-project-type',
              'terms' => $project_type->slug,
            ],
          ],
        ]);

        $data[] = [
          'ID' => $project_type->term_id,
          'project_type' => $project_type->name,
          'project_count' => count($projects),
        ];
      }
    }

    // Sorts the data set.
    $orderby  = isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'project_id';
    $order    = isset($_REQUEST['order']) ? sanitize_key($_REQUEST['order']) : 'ASC';
    $data     = wp_list_sort($data, $orderby, $order);

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
