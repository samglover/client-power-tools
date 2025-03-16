<?php
/**
 * Project list table
 *
 * @file       cpt-project-types-table.php
 * @package    Client_Power_Tools
 * @subpackage Core\Admin
 * @since      1.7.0
 * @since      1.10.4 File renamed from cpt-project-types-table.php to class-project-types-list-table.php.
 */

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

/**
 * List of project types
 *
 * @see WP_List_Table
 */
class Project_Types_List_Table extends \WP_List_Table {
	/**
	 * Construct
	 */
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

	/**
	 * Single row
	 *
	 * @param array $term A singular item (one full row's worth of data).
	 * @param int   $level Depth for heirarchical terms.
	 */
	function single_row( $term, $level = 0 ) {
		?>
		<tr 
			data-id="<?php echo esc_attr( $term['ID'] ); ?>"
			data-name="<?php echo esc_attr( $term['project_type'] ); ?>" 
			data-stages="<?php echo esc_attr( $term['project_type_stages_attr'] ); ?>"
		>
			<?php $this->single_row_columns( $term ); ?>
		</tr>
		<?php
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
	 * Get columns
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information:
	 * 'slugs' => 'Visible Titles'.
	 */
	function get_columns() {
		$columns = array(
			'project_type'        => sprintf(
				// Translators: %s is the singular project label.
				__( '%s Type', 'client-power-tools' ),
				Common\cpt_get_projects_label( 'singular' )
			),
			'project_type_stages' => __( 'Stages', 'client-power-tools' ),
			'project_count'       => __( 'Count', 'client-power-tools' ),
		);
		return $columns;
	}

	/**
	 * Sortable columns
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'project_type'  => array( 'project_type', true ),
			'project_count' => array( 'project_count', false ),
		);
		return $sortable_columns;
	}

	/**
	 * Handle row actions
	 *
	 * @param array  $item A singular item (one full row's worth of data).
	 * @param string $column_name A singular item (one full row's worth of data).
	 * @param string $primary Primary column name.
	 */
	function handle_row_actions( $item, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return '';
		}

		if ( ! current_user_can( 'cpt_manage_projects' ) ) {
			return '';
		}

		$actions = array(
			'Edit'   => sprintf(
				'<button type="button" class="button-link cpt-edit-link" aria-label="%s" aria-expanded="false">%s</button>',
				$item['project_type'],
				__( 'Edit', 'client-power-tools' )
			),
			'Delete' => '<a href="' . wp_nonce_url( '?page=cpt-project-types&action=delete&project_type_term_id=' . $item['ID'] ) . '">' . __( 'Delete', 'client-power-tools' ) . '</a>',
		);

		return $this->row_actions( $actions );
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

		/* Query projects */
		if ( isset( $_REQUEST['orderby'] ) ) {
			$project_types_orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
		} else {
			$project_types_orderby = 'name';
		}

		if ( isset( $_REQUEST['order'] ) ) {
			$project_types_order = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
		} else {
			$project_types_order = 'ASC';
		}

		$project_types = get_terms(
			array(
				'taxonomy'   => 'cpt-project-type',
				'hide_empty' => false,
				array(
					'orderby' => $project_types_orderby,
					'order'   => $project_types_order,
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
		if ( isset( $_REQUEST['orderby'] ) ) {
			$data_orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
		} else {
			$data_orderby = 'project_id';
		}

		if ( isset( $_REQUEST['order'] ) ) {
			$data_order = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
		} else {
			$data_order = 'ASC';
		}

		$data = wp_list_sort( $data, $data_orderby, $data_order );

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
