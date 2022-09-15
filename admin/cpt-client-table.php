<?php

/**
 * This is based on the Custom List Table Example plugin by Matt van Andel.
 * https://wordpress.org/plugins/custom-list-table-example/
 */

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Includes;
use Client_Power_Tools\Core\Common;

class Client_List_Table extends Includes\WP_List_Table  {
  function __construct() {
    global $status, $page;

    parent::__construct([
      'singular'  => 'client',
      'plural'    => 'clients',
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
  function column_client_name($item) {
    // Return the contents.
    return sprintf('<strong><a href="' . add_query_arg('user_id', $item['ID']) . '">%1$s</a></strong>%2$s',
      /* $1%s */ $item['client_name'],
      /* $2%s */ $item['client_id'] ? ' <span style="color:silver">(' . $item['client_id'] . ')</span>' : '',
    );
  }

  /**
   * Client Messages Method
   */
  function column_client_messages($item) {
    if ($item['msg_count']) {
      return sprintf($item['msg_count']);
    }
  }

  /**
   * Client Status Method
   */
  function column_client_status($item) {
    return sprintf($item['client_status']);
  }

  /**
   * Client Manager Method
   */
  function column_client_manager($item) {
    if ($item['client_manager']) {
      return sprintf($item['client_manager']);
    }
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
      // 'cb'              => '<input type="checkbox" />',
      'client_name'     => 'Client',
      'client_messages' => 'Messages',
      'client_status'   => 'Status',
      'client_manager'  => 'Manager',
    ];

    // Remove columns for disabled modules. (It's easier to remove columns add
    // them in the correct order.)
    if (!get_option('cpt_module_messaging')) {
      unset($columns['client_messages']);
    }

    return $columns;
  }

  /**
   * Sortable Columns
   */
  function get_sortable_columns() {
    $sortable_columns = [
      'client_name'     => ['client_name', true],
      'client_messages' => ['msg_count', false],
      'client_status'   => ['client_status', false],
      'client_manager'  => ['client_manager', false],
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
    $params         = explode("\n", get_option('cpt_client_statuses'));
    $current_status = isset($_REQUEST['client_status']) ? sanitize_text_field(urldecode($_REQUEST['client_status'])) : 'all';
    $curr_mgr       = Common\cpt_get_name(get_current_user_id());
    $curr_mgr_param = isset($_REQUEST['client_manager']) ? sanitize_text_field(urldecode($_REQUEST['client_manager'])) : '';
    $views          = array();

    array_unshift($params, 'All', 'Mine');

    foreach($params as $key => $val) {
      $class          = '';
      $val            = trim($val);
      $curr_param     = urlencode($val);

      if ($current_status == $curr_param || ($key == 1 && $curr_mgr_param == $curr_mgr)) {
        $class = ' class="current"';
      } elseif (!isset($_REQUEST['client_status']) && !isset($_REQUEST['client_manager']) && $key == 0 && $current_status == 'all') {
        $class = ' class="current"';
      }

      if ($curr_param == 'All') {
        $link = '<a href="' . remove_query_arg(['client_status', 'client_manager']) . '"' . $class . '>' . $val . '</a>';
      } else {
        $link = '<a href="' . add_query_arg('client_status', $curr_param) . '"' . $class . '>' . $val . '</a>';
      }

      if ($curr_param == 'Mine') {
        $link = '<a href="' . add_query_arg('client_manager', $curr_mgr) . '"' . $class . '>' . $val . '</a>';
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
     * Query Clients
     */
    $args = [
      'role'          => 'cpt-client',
      'orderby'       => isset($_REQUEST['orderby'])  ? sanitize_key($_REQUEST['orderby'])  : 'display_name',
      'order'         => isset($_REQUEST['order'])    ? sanitize_key($_REQUEST['order'])    : 'ASC',
    ];

    $client_query  = new \WP_USER_QUERY($args);
    $clients       = $client_query->get_results();
    $data          = [];

    // Creates the data set.
    if (!empty($clients)) {
      foreach ($clients as $client) {
        $cpt_messages = new \WP_Query([
          'fields'          => 'ids',
          'meta_key'        => 'cpt_clients_user_id',
          'meta_value'      => $client->ID,
          'post_type'       => 'cpt_message',
          'posts_per_page'  => -1,
        ]);

        $manager_data = get_userdata(get_user_meta($client->ID, 'cpt_client_manager', true));

        if ($manager_data) {
          // Checks for clients whose manager is no longer assigned that role.
          if (!in_array('cpt-client-manager', $manager_data->roles)) {
            $manager_name = '<span style="color: silver;">Unassigned</span>';
          } else {
            $manager_name = trim(Common\cpt_get_name($manager_data->ID));
          }
        } else {
          $manager_name = '<span style="color: silver;">Unassigned</span>';
        }

        $data[] = [
          'ID'              => $client->ID,
          'client_name'     => $client->display_name,
          'client_email'    => $client->user_email,
          'client_id'       => get_user_meta($client->ID, 'cpt_client_id', true),
          'client_manager'  => $manager_name,
          'client_status'   => get_user_meta($client->ID, 'cpt_client_status', true),
          'msg_count'       => number_format_i18n($cpt_messages->post_count),
        ];
      }
    }

    // Filters the data set.
    if (isset($_REQUEST['client_status'])) {
      $client_status_filter = sanitize_text_field(urldecode($_REQUEST['client_status']));

      foreach($data as $i => $client) {
        if ($client['client_status'] !== $client_status_filter) {
          unset($data[$i]);
        }
      }
    }

    if (isset($_REQUEST['client_manager'])) {
      $client_status_filter = sanitize_text_field(urldecode($_REQUEST['client_manager']));

      foreach($data as $i => $client) {
        if ($client['client_manager'] !== $client_status_filter) {
          unset($data[$i]);
        }
      }
    }

    // Sorts the data set.
    $orderby  = isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby'])  : 'display_name';
    $order    = isset($_REQUEST['order'])   ? sanitize_key($_REQUEST['order'])    : 'ASC';
    $data     = wp_list_sort($data, $orderby, $order);

    /**
     * Pagination
     */
    $total_items  = count($data);
    $per_page     = 25;
    $current_page = $this->get_pagenum();
    $data         = array_slice($data, (($current_page - 1) * $per_page), $per_page);

    $this->set_pagination_args([
      'total_items' => $total_items,
      'per_page'    => $per_page,
      'total_pages' => ceil($total_items / $per_page),
    ]);

    /**
     * $this->items contains the data that will actually be displayed on the
     * current page.
     */
    $this->items = $data;
  }
}
