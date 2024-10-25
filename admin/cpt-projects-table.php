<?php

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

class Project_List_Table extends \WP_List_Table {
	function __construct() {
		global $status, $page;
		parent::__construct(
			array(
				'singular' => strtolower( Common\cpt_get_projects_label( 'singular' ) ),
				'plural'   => strtolower( Common\cpt_get_projects_label( 'plural' ) ),
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
	 * Project Column Method
	 */
	function column_project( $item ) {
		$url = admin_url( 'admin.php?page=cpt-projects' );
		return '<strong><a href="' . add_query_arg( 'projects_post_id', $item['ID'], $url ) . '">' . $item['project'] . '</a></strong>';
	}

	/**
	 * Client Column Method
	 */
	function column_client_name( $item ) {
		return sprintf(
			'<strong><a href="' . Common\cpt_get_client_profile_url( $item['clients_user_id'] ) . '">%1$s</a></strong>%2$s',
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
		$columns = array(
			'project_id'     => Common\cpt_get_projects_label( 'singular' ) . ' ' . __( 'ID', 'client-power-tools' ),
			'project'        => Common\cpt_get_projects_label( 'plural' ),
			'client_name'    => __( 'Client', 'client-power-tools' ),
			'project_type'   => __( 'Type', 'client-power-tools' ),
			'project_stage'  => __( 'Stage', 'client-power-tools' ),
			'project_status' => __( 'Status', 'client-power-tools' ),
		);
		return $columns;
	}


	/**
	 * Sortable Columns
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'project'    => array( 'title', true ),
			'project_id' => array( 'project_id', true ),
			// 'project_type' => ['project_type', true],
			// 'client_name' => ['client_name', false],
		);
		return $sortable_columns;
	}


	function get_views() {
		$count_projects = wp_count_posts( $post_type = 'cpt_project' );
		$current_status = isset( $_REQUEST['project_status'] ) ? sanitize_text_field( urldecode( $_REQUEST['project_status'] ) ) : 'all';
		$views          = array();

		$params = array( 'All' );
		$params = array_merge( $params, explode( "\n", get_option( 'cpt_project_statuses' ) ) );
		if ( $count_projects->trash > 0 ) {
			array_push( $params, 'Trash' );
		}

		foreach ( $params as $key => $val ) {
			$class   = '';
			$current = ' class="current"';
			$val     = trim( $val );

			switch ( $val ) {
				case 'All':
					if ( ! isset( $_REQUEST['project_status'] ) && ! isset( $_REQUEST['post_status'] ) ) {
						$class = $current;
					}
					$url  = remove_query_arg( array( 'project_status', 'post_status' ) );
					$link = '<a href="' . esc_url( $url ) . '"' . $class . '>' . $val . ' <span class="count">(' . $count_projects->publish . ')</span></a>';
					break;
				case 'Trash':
					if ( isset( $_REQUEST['post_status'] ) && strtolower( $_REQUEST['post_status'] ) == 'trash' ) {
						$class = $current;
					}
					$url  = remove_query_arg( array( 'project_type', 'project_status' ) );
					$url  = add_query_arg( 'post_status', 'trash', $url );
					$link = '<a href="' . esc_url( $url ) . '"' . $class . '>' . $val . ' <span class="count">(' . $count_projects->trash . ')</span></a>';
					break;
				default:
					if ( isset( $_REQUEST['project_status'] ) && $_REQUEST['project_status'] == $val ) {
						$class = $current;
					}
					$url  = esc_url( remove_query_arg( 'post_status' ) );
					$url  = add_query_arg( 'project_status', $val, $url );
					$link = '<a href="' . esc_url( $url ) . '"' . $class . '>' . $val . '</a>';
					break;
			}

			$views[ $val ] = $link;
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

		/**
		 * Query Projects
		 */
		$args = array(
			'orderby'        => isset( $_REQUEST['orderby'] ) ? sanitize_key( $_REQUEST['orderby'] ) : 'title',
			'order'          => isset( $_REQUEST['order'] ) ? sanitize_key( $_REQUEST['order'] ) : 'ASC',
			'post_type'      => 'cpt_project',
			'posts_per_page' => -1,
		);
		if ( isset( $_REQUEST['post_status'] ) ) {
			$args['post_status'] = sanitize_key( $_REQUEST['post_status'] );
		}
		$projects = new \WP_Query( $args );
		$data     = array();

		if ( $projects->have_posts() ) :
			while ( $projects->have_posts() ) :
				$projects->the_post();
				$project_data = Common\cpt_get_project_data( get_the_ID() );
				$data[]       = array(
					'ID'              => $project_data['projects_post_id'],
					'project_id'      => $project_data['project_id'],
					'project'         => $project_data['project_name'],
					'clients_user_id' => $project_data['clients_user_id'],
					'client_id'       => get_user_meta( $project_data['clients_user_id'], 'cpt_client_id', true ),
					'client_name'     => Common\cpt_get_client_name( get_post_meta( $project_data['projects_post_id'], 'cpt_client_id', true ) ),
					'project_type'    => $project_data['project_type'],
					'project_stage'   => $project_data['project_type'] ? $project_data['project_stage'] : '',
					'project_status'  => $project_data['project_status'],
				);
			endwhile;
		endif;

		// Filters the data set.
		if ( isset( $_REQUEST['project_status'] ) ) {
			$project_status_filter = sanitize_text_field( urldecode( $_REQUEST['project_status'] ) );
			if ( $project_status_filter ) {
				foreach ( $data as $i => $project ) {
					if ( $project['project_status'] !== $project_status_filter ) {
						unset( $data[ $i ] );
					}
				}
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
