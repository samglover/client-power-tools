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
    return;
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
   * Client Column Method
   */
  function column_client($item) {
    // Return the contents.
    return sprintf('<strong>%1$s</strong> %2$s',
      /* $1%s */ $item['client_name'],
      /* $2%s */ $item['client_id'] ? '<span style="color:silver">(' . $item['client_id'] . ')</span>' : '',
    );
  }

  /**
   * Sender Column Method
   */
  function column_sender($item) {
    return sprintf($item['sender']);
  }


  /**
   * Subject Column Method
   */
  function column_subject($item) {
    $clients_url  = add_query_arg('user_id', $item['clients_user_id'], esc_url(admin_url('admin.php?page=cpt')));
    $msg_url      = $clients_url . '&paged=' . cpt_get_message_pagenum($item['clients_user_id'], $item['ID']) .  '#cpt-message-' . $item['ID'];

    // Return the contents.
    return sprintf('<strong><a href="%1$s">%2$s</a></strong>',
      /* $1%s */ $msg_url,
      /* $2%s */ $item['subject'],
   );
  }


  /**
   * Date Column Method
   */
  function column_date($item) {
    return sprintf($item['date']);
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
      'project_messages'  => 'Messages',
      'project_status'    => 'Status',
      'last_activity'     => 'Last Activity',
    ];
   return $columns;
  }


  /**
   * Sortable Columns
   */
  function get_sortable_columns() {
    $sortable_columns = [
      'project'           => ['project', false],
      'client_name'       => ['client_name', false],
      'project_messages'  => ['msg_count', false],
      'project_status'    => ['client_status', false],
      'last_activity'     => ['last_activity', true],
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


  /**
   * Prepare Data for Display
   */
  function prepare_items() {
    /**
     * Column Headers
     */
    $columns = $this->get_columns();
    $hidden = [];
    $sortable = $this->get_sortable_columns();

    $this->_column_headers = [$columns, $hidden, $sortable];
    $this->process_bulk_action();

    /**
     * Query Messages
     */
    $data = [];

    // Creates the data set.
    $cpt_projects = new \WP_Query([
      'post_type' => 'cpt_project',
      'posts_per_page' => -1,
    ]);

    if ($cpt_projects->have_posts()) : while ($cpt_projects->have_posts()) : $cpt_projects->the_post();
      $post_id = get_the_ID();
      $clients_user_id = get_post_meta($post_id, 'cpt_clients_user_id', true);

      $data[] = [
        'ID' => $post_id,
        'client_name' => Common\cpt_get_name($clients_user_id),
        'clients_user_id' => $clients_user_id,
        'client_id' => get_user_meta($clients_user_id, 'cpt_client_id', true),
        'sender' => get_the_author(),
        'subject' => get_the_title() ? get_the_title() : '[Message from ' . get_the_author() . ']',
        'date' => get_the_date(),
      ];
    endwhile; endif;

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
