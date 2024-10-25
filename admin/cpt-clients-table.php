<?php

/**
 * This is based on the Custom List Table Example plugin by Matt van Andel.
 * https://wordpress.org/plugins/custom-list-table-example/
 */

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

class Client_List_Table extends \WP_List_Table {
	function __construct() {
		global $status, $page;

		parent::__construct(
			array(
				'singular' => 'client',
				'plural'   => 'clients',
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
		return $item[ $column_name ];
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
	 * Client Column Method
	 */
	function column_client_name( $item ) {
		// Return the contents.
		$url = admin_url( 'admin.php?page=cpt' );
		return sprintf(
			'<strong><a href="' . add_query_arg( 'user_id', $item['ID'], $url ) . '">%1$s</a></strong>%2$s',
			/* $1%s */ $item['client_name'],
			/* $2%s */ $item['client_id'] ? ' <span style="color:silver">(' . $item['client_id'] . ')</span>' : '',
		);
	}

	/**
	 * Client Messages Method
	 */
	function column_client_messages( $item ) {
		if ( $item['msg_count'] ) {
			return sprintf( $item['msg_count'] );
		}
	}

	/**
	 * Client Projects Method
	 */
	function column_client_projects( $item ) {
		if ( $item['project_count'] ) {
			return sprintf( $item['project_count'] );
		}
	}

	/**
	 * Client Status Method
	 */
	function column_client_status( $item ) {
		return sprintf( $item['client_status'] );
	}

	/**
	 * Client Manager Method
	 */
	function column_client_manager( $item ) {
		if ( $item['client_manager'] ) {
			return sprintf( $item['client_manager'] );
		}
	}

	function column_last_activity( $item ) {
		if ( $item['last_activity'] ) {
			$current_timestamp = time();
			return sprintf( human_time_diff( $item['last_activity'], $current_timestamp ) . ' ' . __( 'ago', 'client-power-tools' ) );
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
			'client_name'     => 'Client',
			'client_messages' => 'Messages',
			'client_projects' => 'Projects',
			'client_status'   => 'Status',
			'client_manager'  => 'Manager',
			'last_activity'   => 'Last Activity',
		);

		// Remove columns for disabled modules. (It's easier to remove columns add
		// them in the correct order.)
		if ( ! get_option( 'cpt_module_messaging' ) ) {
			unset( $columns['client_messages'] );
		}
		if ( ! get_option( 'cpt_module_projects' ) ) {
			unset( $columns['client_projects'] );
		}

		return $columns;
	}

	/**
	 * Sortable Columns
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'client_name'    => array( 'client_name', true ),
			'client_manager' => array( 'client_manager', false ),
			'last_activity'  => array( 'last_activity', false ),
		);
		return $sortable_columns;
	}


	function get_views() {
		$params         = explode( "\n", get_option( 'cpt_client_statuses' ) );
		$current_status = isset( $_REQUEST['client_status'] ) ? sanitize_text_field( urldecode( $_REQUEST['client_status'] ) ) : 'all';
		$curr_user      = Common\cpt_get_display_name( get_current_user_id() );
		$curr_mgr_param = isset( $_REQUEST['client_manager'] ) ? sanitize_text_field( urldecode( $_REQUEST['client_manager'] ) ) : '';
		$views          = array();
		$client_ids     = Common\cpt_get_clients( array( 'fields' => 'ID' ) );

		if ( $client_ids ) {
			array_unshift( $params, 'Mine' );
		}
		array_unshift( $params, 'All' );

		foreach ( $params as $key => $val ) {
			$class      = '';
			$val        = trim( $val );
			$curr_param = rawurlencode( $val );

			if (
				$current_status === $curr_param ||
				( 'Mine' === $curr_param && $curr_mgr_param === $curr_user ) ||
				( 'All' === $curr_param && ! isset( $_REQUEST['client_status'] ) && ! isset( $_REQUEST['client_manager'] ) )
			) {
				$class = ' class="current"';
			}

			$url = admin_url( 'admin.php?page=cpt' );
			if ( 'All' === $curr_param ) {
				$link = '<a href="' . remove_query_arg( array( 'client_status', 'client_manager' ), $url ) . '"' . $class . '>' . $val . '</a>';
			} else {
				$link = '<a href="' . add_query_arg( 'client_status', $curr_param, $url ) . '"' . $class . '>' . $val . '</a>';
			}

			if ( 'Mine' === $curr_param ) {
				$link = '<a href="' . add_query_arg( 'client_manager', $curr_user, $url ) . '"' . $class . '>' . $val . '</a>';
			}

			$views[ $curr_param ] = $link;
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
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$clients = Common\cpt_get_clients();
		$data    = array();

		// Creates the data set.
		if ( ! empty( $clients ) ) {
			foreach ( $clients as $client ) {
				$cpt_messages = new \WP_Query(
					array(
						'fields'         => 'ids',
						'meta_key'       => 'cpt_clients_user_id',
						'meta_value'     => $client->ID,
						'post_type'      => 'cpt_message',
						'posts_per_page' => -1,
					)
				);

				$cpt_projects = new \WP_Query(
					array(
						'fields'         => 'ids',
						'meta_key'       => 'cpt_client_id',
						'meta_value'     => $client->ID,
						'post_type'      => 'cpt_project',
						'posts_per_page' => -1,
					)
				);

				$manager_data = get_userdata( get_user_meta( $client->ID, 'cpt_client_manager', true ) );

				if ( $manager_data ) {
					// Checks for clients whose manager is no longer assigned that role.
					if ( ! in_array( 'cpt-client-manager', $manager_data->roles ) ) {
						$manager_name = '<span style="color: silver;">Unassigned</span>';
					} else {
						$manager_name = trim( Common\cpt_get_display_name( $manager_data->ID ) );
					}
				} else {
					$manager_name = '<span style="color: silver;">Unassigned</span>';
				}

				$data[] = array(
					'ID'             => $client->ID,
					'client_name'    => Common\cpt_get_client_name( $client->ID ),
					'client_email'   => $client->user_email,
					'client_id'      => get_user_meta( $client->ID, 'cpt_client_id', true ),
					'client_manager' => $manager_name,
					'client_status'  => get_user_meta( $client->ID, 'cpt_client_status', true ),
					'last_activity'  => get_user_meta( $client->ID, 'cpt_last_activity', true ),
					'msg_count'      => number_format_i18n( $cpt_messages->post_count ),
					'project_count'  => number_format_i18n( $cpt_projects->post_count ),
				);
			}
		}

		// Filters the data set.
		if ( isset( $_REQUEST['client_status'] ) ) {
			$client_status_filter = sanitize_text_field( urldecode( $_REQUEST['client_status'] ) );
			if ( $client_status_filter ) {
				foreach ( $data as $i => $client ) {
					if ( $client['client_status'] !== $client_status_filter ) {
						unset( $data[ $i ] );
					}
				}
			}
		}

		if ( isset( $_REQUEST['client_manager'] ) ) {
			$client_status_filter = sanitize_text_field( urldecode( $_REQUEST['client_manager'] ) );
			if ( $client_status_filter ) {
				foreach ( $data as $i => $client ) {
					if ( $client['client_manager'] !== $client_status_filter ) {
						unset( $data[ $i ] );
					}
				}
			}
		}

		// Sorts the data set.
		$orderby = isset( $_REQUEST['orderby'] ) ? sanitize_key( $_REQUEST['orderby'] ) : 'client_name';
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
