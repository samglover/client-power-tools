<?php

/**
* This is based on the Custom List Table Example plugin by Matt van Andel.
* https://wordpress.org/plugins/custom-list-table-example/
*/

namespace Client_Power_Tools\Core\Admin;
use Client_Power_Tools\Core\Includes;

class Client_Manager_List_Table extends Includes\WP_List_Table  {

  function __construct() {

    global $page;

    parent::__construct( [
      'singular'  => 'manager',
      'plural'    => 'managers',
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
  function column_default( $item, $column_name ) {
    return;
  }


  /**
  * Checkboxes
  *
  * @see WP_List_Table::::single_row_columns()
  * @param array $item A singular item (one full row's worth of data)
  * @return string Text to be placed inside the column <td>
  */
  function column_cb( $item ) {

    return sprintf(
      '<input type="checkbox" name="%1$s[]" value="%2$s" />',
      /* $1%s */ $this->_args[ 'singular' ],
      /* $2%s */ $item[ 'ID' ]
    );

  }


  /**
  * Name Column Method
  */
  function column_manager_name( $item ) {

    $actions = [
      'remove' => '<a href="' . add_query_arg( [ 'user_id' => $item[ 'ID' ], 'cpt_action' => 'cpt_remove_client_manager' ] ) . '">Remove</a>',
    ];

    // Return the contents.
    return sprintf( '<strong>%1$s</strong><br />%2$s',
      /* $1%s */ $item[ 'manager_name' ],
      /* $2%s */ $this->row_actions( $actions, true )
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
      // 'cb'              => '<input type="checkbox" />',
      'manager_name'     => 'Manager Name',
    ];

    return $columns;

  }


  /**
  * Sortable Columns
  */
  function get_sortable_columns() {

    $sortable_columns = [
      'manager_name' => [ 'manager_name', true ],
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
      'remove'  => 'remove',
    ];

    return $actions;

  }


  function process_bulk_action() {

    $action = $this->current_action();

    switch ( $action ) {

      case 'remove':
        wp_die( 'Remove client manager permissions.' );
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
    $columns  = $this->get_columns();
    $hidden   = [];
    $sortable = $this->get_sortable_columns();

    $this->_column_headers = [ $columns, $hidden, $sortable ];

    $this->process_bulk_action();


    /**
    * Query Client Managers
    */
    $args = [
      'role'      => 'cpt-client-manager',
      'orderby'   => 'display_name',
      'order'     => 'ASC',
    ];

    $client_managers_query  = new \WP_USER_QUERY( $args );
    $client_managers        = $client_managers_query->get_results();
    $data                   = [];

    // Creates the data set.
    if ( ! empty( $client_managers ) ) {

      foreach ( $client_managers as $client_manager ) {

        $data[] = [
          'ID'            => $client_manager->ID,
          'manager_name'  => $client_manager->display_name,
          'manager_email' => $client_manager->user_email,
        ];

      }

    }

    /**
    * Pagination
    */
    $total_items  = count( $data );
    $per_page     = 25;
    $current_page = $this->get_pagenum();
    $data         = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

    $this->set_pagination_args(
      [
        'total_items' => $total_items,
        'per_page'    => $per_page,
        'total_pages' => ceil( $total_items / $per_page ),
      ],
    );


    /**
    * $this->items contains the data that will actually be displayed on the
    * current page.
    */
    $this->items = $data;

  }

}
