<?php

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

class Project_Types_List_Table extends \WP_List_Table {
	function __construct() {
		global $action;
		parent::__construct(
			array(
				'singular' => strtolower( Common\cpt_get_projects_label( 'singular' ) ),
				'plural'   => strtolower( Common\cpt_get_projects_label( 'plural' ) ),
				'ajax'     => false,
			)
		);

		$action = $this->screen->action;
	}

	function single_row( $term, $level = 0 ) {
		echo '<tr data-id="' . esc_attr( $term['ID'] ) . '" data-name="' . esc_attr( $term['project_type'] ) . '" data-stages="' . esc_attr( $term['project_type_stages_attr'] ) . '">';
		$this->single_row_columns( $term );
		echo '</tr>';
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
	 * Get Columns
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information:
	 * 'slugs' => 'Visible Titles'.
	 */
	function get_columns() {
		$columns = array(
			// 'cb' => '<input type="checkbox" />',
			'project_type'        => sprintf( __( '%s Type', 'client-power-tools' ), Common\cpt_get_projects_label( 'singular' ) ),
			'project_type_stages' => __( 'Stages', 'client-power-tools' ),
			'project_count'       => __( 'Count', 'client-power-tools' ),
		);
		return $columns;
	}

	/**
	 * Sortable Columns
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'project_type'  => array( 'project_type', true ),
			'project_count' => array( 'project_count', false ),
		);
		return $sortable_columns;
	}


	function handle_row_actions( $item, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return '';
		}
		if ( ! current_user_can( 'cpt_manage_projects' ) ) {
			return '';
		}
		$actions = array(
			'Edit'   => sprintf( '<button type="button" class="button-link cpt-edit-link" aria-label="%s" aria-expanded="false">%s</button>', $item['project_type'], __( 'Edit', 'client-power-tools' ) ),
			'Delete' => '<a href="' . wp_nonce_url( '?page=cpt-project-types&action=delete&project_type_term_id=' . $item['ID'] ) . '">' . __( 'Delete', 'client-power-tools' ) . '</a>',
		);
		return $this->row_actions( $actions );
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

		/**
		 * Query Projects
		 */
		$project_types = get_terms(
			array(
				'taxonomy'   => 'cpt-project-type',
				'hide_empty' => false,
				array(
					'orderby' => isset( $_REQUEST['orderby'] ) ? sanitize_key( $_REQUEST['orderby'] ) : 'name',
					'order'   => isset( $_REQUEST['order'] ) ? sanitize_key( $_REQUEST['order'] ) : 'ASC',
				),
			)
		);
		$data          = array();

		if ( $project_types ) {
			foreach ( $project_types as $project_type ) {
				$stages_raw    = sanitize_textarea_field( get_term_meta( $project_type->term_id, 'cpt_project_type_stages', true ) );
				$stages_array  = explode( "\n", $stages_raw );
				$stages_output = implode( '<br>', $stages_array );

				$data[] = array(
					'ID'                       => $project_type->term_id,
					'project_type'             => $project_type->name,
					'project_type_stages'      => $stages_output,
					'project_type_stages_attr' => esc_attr( $stages_raw ),
					'project_count'            => $project_type->count,
				);
			}
		}

		// Sorts the data set.
		$orderby = isset( $_REQUEST['orderby'] ) ? sanitize_key( $_REQUEST['orderby'] ) : 'project_id';
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
