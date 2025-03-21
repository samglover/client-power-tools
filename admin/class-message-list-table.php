<?php
/**
 * Message list table
 *
 * @file       class-message-list-table.php
 * @package    Client_Power_Tools
 * @subpackage Core\Admin
 * @since      1.0.0
 * @since      1.10.4 File renamed from cpt-admin-messages-table.php to class-message-list-table.php.
 * @link       https://wordpress.org/plugins/custom-list-table-example/
 */

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

/**
 * List of messages
 *
 * @see WP_List_Table
 */
class Message_List_Table extends \WP_List_Table {
	/**
	 * Construct
	 */
	function __construct() {
		global $status, $page;
		parent::__construct(
			array(
				'singular' => 'message',
				'plural'   => 'messages',
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
	 * Client column Method
	 *
	 * @param array $item A singular item (one full row's worth of data).
	 */
	function column_client( $item ) {
		// Return the contents.
		return sprintf(
			'<strong>%1$s</strong> %2$s',
			/* $1%s */ $item['client_name'],
			/* $2%s */ $item['client_id'] ? '<span style="color:silver">(' . $item['client_id'] . ')</span>' : '',
		);
	}

	/**
	 * Sender column method
	 *
	 * @param array $item A singular item (one full row's worth of data).
	 */
	function column_sender( $item ) {
		return sprintf( $item['sender'] );
	}


	/**
	 * Subject Column Method
	 *
	 * @param array $item A singular item (one full row's worth of data).
	 */
	function column_subject( $item ) {
		$clients_url = add_query_arg( 'user_id', $item['clients_user_id'], esc_url( admin_url( 'admin.php?page=cpt' ) ) );
		$msg_url     = $clients_url . '&paged=' . cpt_get_message_pagenum( $item['clients_user_id'], $item['ID'] ) . '#cpt-message-' . $item['ID'];

		// Return the contents.
		return sprintf(
			'<strong><a href="%1$s">%2$s</a></strong>',
			/* $1%s */ $msg_url,
			/* $2%s */ $item['subject'],
		);
	}


	/**
	 * Date column method
	 *
	 * @param array $item A singular item (one full row's worth of data).
	 */
	function column_date( $item ) {
		return sprintf( $item['date'] );
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
			'client'  => 'Client',
			'sender'  => 'Sender',
			'subject' => 'Subject',
			'date'    => 'Date',
		);
		return $columns;
	}


	/**
	 * Sortable columns
	 */
	function get_sortable_columns() {
		return; // Remove this line to enable sortable columns.
		$sortable_columns = array(
			'date' => array( 'msg_count', true ),
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
			'delete' => 'Delete',
		);
		return $actions;
	}

	/**
	 * Process bulk actions
	 */
	function process_bulk_action() {
		$action = $this->current_action();
		switch ( $action ) {
			case 'delete':
				wp_die( 'Delete something.' );
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

		$data         = array();
		$cpt_messages = new \WP_Query(
			array(
				'post_type'      => 'cpt_message',
				'posts_per_page' => -1,
			)
		);

		if ( $cpt_messages->have_posts() ) :
			while ( $cpt_messages->have_posts() ) :
				$cpt_messages->the_post();
				$post_id         = get_the_ID();
				$clients_user_id = get_post_meta( $post_id, 'cpt_clients_user_id', true );

				$data[] = array(
					'ID'              => $post_id,
					'client_name'     => Common\cpt_get_client_name( $clients_user_id ),
					'clients_user_id' => $clients_user_id,
					'client_id'       => get_user_meta( $clients_user_id, 'cpt_client_id', true ),
					'sender'          => get_the_author(),
					'subject'         => get_the_title() ? get_the_title() : '[Message from ' . get_the_author() . ']',
					'date'            => get_the_date(),
				);
			endwhile;
		endif;

		/* Pagination */
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
