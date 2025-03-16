<?php
/**
 * Message list table
 *
 * @file       class-client-manager-list-table.php
 * @package    Client_Power_Tools
 * @subpackage Core\Admin
 * @since      1.2.1
 * @since      1.10.4 File renamed from cpt-client-manager-table.php to class-client-manager-list-table.php.
 * @link       https://wordpress.org/plugins/custom-list-table-example/
 */

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

/**
 * List of client managers
 *
 * @see WP_List_Table
 */
class Client_Manager_List_Table extends \WP_List_Table {
	/**
	 * Construct
	 */
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
	 * Column default
	 *
	 * @param array $item A singular item (one full row's worth of data).
	 * @param array $column_name The name/slug of the column to be processed.
	 * @return string Text or HTML to be placed inside the column <td>.
	 */
	function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Checkboxes
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data).
	 * @return string Text to be placed inside the column <td>.
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/* $1%s */ $this->_args['singular'],
			/* $2%s */ $item['ID']
		);
	}

	/**
	 * Manager name column method
	 *
	 * @param array $item A singular item (one full row's worth of data).
	 */
	function column_manager_name( $item ) {
		$remove_url = wp_nonce_url(
			'?page=cpt-managers&action=cpt_remove_client_manager&manager_id=' . $item['ID'],
			'cpt_remove_client_manager'
		);

		$actions = array(
			'remove' => '<a href="' . $remove_url . '">Remove</a>',
		);

		return sprintf(
			'<strong>%1$s</strong><br />%2$s',
			/* $1%s */ $item['manager_name'],
			/* $2%s */ $this->row_actions( $actions )
		);
	}

	/**
	 * Clients column method
	 *
	 * @param array $item A singular item (one full row's worth of data).
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
	 * Get columns
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information:
	 * 'slugs' => 'Visible Titles'.
	 */
	function get_columns() {
		$columns = array(
			'manager_name'     => 'Manager Name',
			'managers_clients' => 'Clients',
		);

		return $columns;
	}

	/**
	 * Sortable columns
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'manager_name' => array( 'manager_name', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Bulk actions
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
		}
		return;
	}

	/**
	 * Prepare data for display
	 */
	function prepare_items() {
		/* Column headers */
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		/**
		 * Query client managers
		 */
		$data                  = array();
		$client_managers_query = new \WP_User_Query(
			array(
				'role'    => 'cpt-client-manager',
				'orderby' => 'display_name',
				'order'   => 'ASC',
			)
		);
		$client_managers       = $client_managers_query->get_results();

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
		if ( isset( $_REQUEST['orderby'] ) ) {
			$orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
		} else {
			$orderby = 'display_name';
		}

		if ( isset( $_REQUEST['order'] ) ) {
			$order = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
		} else {
			$order = 'ASC';
		}

		$data = wp_list_sort( $data, $orderby, $order );

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

		$this->items = $data;
	}
}
