<?php

/**
 * This is based on the Custom List Table Example plugin by Matt van Andel.
 * https://wordpress.org/plugins/custom-list-table-example/
 */

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

class Client_Manager_List_Table extends \WP_List_Table {
	function __construct() {
		global $page;

		parent::__construct(
			array(
				'singular' => 'manager',
				'plural'   => 'managers',
				'ajax'     => false,
			)
		);
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
			/* $1%s */ $this->_args['singular'],
			/* $2%s */ $item['ID']
		);
	}


	/**
	 * Name Column Method
	 */
	function column_manager_name( $item ) {
		$url = admin_url( '?page=cpt-managers' );
		$actions = array(
			'remove' => '<a href="' . add_query_arg(
				array(
					'user_id'    => $item['ID'],
					'cpt_action' => 'cpt_remove_client_manager',
				),
				$url
			) . '">Remove</a>',
		);

		// Return the contents.
		return sprintf(
			'<strong>%1$s</strong><br />%2$s',
			/* $1%s */ $item['manager_name'],
			/* $2%s */ $this->row_actions( $actions )
		);
	}


	/**
	 * Clients Column Method
	 */
	function column_managers_clients( $item ) {
		$managers_client_data = cpt_get_managers_clients( $item['ID'] );

		if ( $managers_client_data ) {
			foreach ( $managers_client_data as $client_data ) {
				$clients_url = esc_url( admin_url( 'admin.php?page=cpt' ) );
				$client_url  = add_query_arg( 'user_id', $client_data['user_id'], $clients_url );
				$client      = '<a href="' . $client_url . '">' . Common\cpt_get_client_name( $client_data['user_id'] ) . '</a>';

				if ( $client_data['client_id'] ) {
					$client .= ' <span style="color:silver">(' . $client_data['client_id'] . ')</span>';
				}

				$clients[] = $client;
			}

			return implode( '<br/>' . "\n", $clients );
		} else {
			return '<span style="color:silver">None</span>';
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
		$columns = array(
			// 'cb'              => '<input type="checkbox" />',
			'manager_name'     => 'Manager Name',
			'managers_clients' => 'Clients',
		);

		return $columns;
	}


	/**
	 * Sortable Columns
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'manager_name' => array( 'manager_name', true ),
		);

		return $sortable_columns;
	}


	/**
	 * Bulk Actions
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 */
	function get_bulk_actions() {
		return; // Remove this line to enable bulk actions.

		$actions = array(
			'remove' => 'remove',
		);

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
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();

		/**
		 * Query Client Managers
		 */
		$client_managers_query = new \WP_User_Query(
			array(
				'role'    => 'cpt-client-manager',
				'orderby' => 'display_name',
				'order'   => 'ASC',
			)
		);
		$client_managers       = $client_managers_query->get_results();
		$data                  = array();

		// Creates the data set.
		if ( ! empty( $client_managers ) ) {
			foreach ( $client_managers as $client_manager ) {
				$data[] = array(
					'ID'            => $client_manager->ID,
					'manager_name'  => $client_manager->display_name,
					'manager_email' => $client_manager->user_email,
				);
			}
		}

		// Sorts the data set.
		$orderby = isset( $_REQUEST['orderby'] ) ? sanitize_key( $_REQUEST['orderby'] ) : 'display_name';
		$order   = isset( $_REQUEST['order'] ) ? sanitize_key( $_REQUEST['order'] ) : 'ASC';
		$data    = wp_list_sort( $data, $orderby, $order );

		/**
		 * Pagination
		 */
		$total_items  = count( $data );
		$per_page     = 25;
		$current_page = $this->get_pagenum();
		$data         = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);

		/**
		 * $this->items contains the data that will actually be displayed on the
		 * current page.
		 */
		$this->items = $data;
	}
}
