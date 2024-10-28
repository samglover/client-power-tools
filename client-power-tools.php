<?php

/**
 * Plugin Name: Client Power Tools
 * Plugin URI: https://clientpowertools.com
 * Description: Client Power Tools is an easy-to-use client dashboard, project management, and communication portal built for designers, developers, consultants, lawyers, and other professionals.
 * Version: 1.10.0
 * Author: Sam Glover
 * Author URI: https://samglover.net
 * Text Domain: client-power-tools
 */

namespace Client_Power_Tools\Core;

use Client_Power_Tools\Core\Common;
use Client_Power_Tools\Core\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Constants
 */
define( 'CLIENT_POWER_TOOLS_PLUGIN_VERSION', '1.10.0' );
define( 'CLIENT_POWER_TOOLS_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'CLIENT_POWER_TOOLS_DIR_URL', plugin_dir_url( __FILE__ ) );


/**
 * Common
 */
require_once CLIENT_POWER_TOOLS_DIR_PATH . 'common/cpt-common.php';
require_once CLIENT_POWER_TOOLS_DIR_PATH . 'common/cpt-login.php';

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\cpt_register_common_scripts' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\cpt_register_common_scripts' );
function cpt_register_common_scripts() {
	wp_enqueue_style( 'cpt-common', CLIENT_POWER_TOOLS_DIR_URL . 'assets/css/common.css', array(), CLIENT_POWER_TOOLS_PLUGIN_VERSION );
	wp_enqueue_script( 'cpt-common', CLIENT_POWER_TOOLS_DIR_URL . 'assets/js/cpt-common.js', array( 'jquery' ), CLIENT_POWER_TOOLS_PLUGIN_VERSION, true );
}

if ( get_option( 'cpt_module_projects' ) ) {
	require_once CLIENT_POWER_TOOLS_DIR_PATH . 'common/cpt-common-projects.php';
}
if ( get_option( 'cpt_module_status_update_req_button' ) ) {
	require_once CLIENT_POWER_TOOLS_DIR_PATH . 'common/cpt-status-update-request-button.php';
}
if ( get_option( 'cpt_module_messaging' ) ) {
	require_once CLIENT_POWER_TOOLS_DIR_PATH . 'common/cpt-common-messages.php';
}

/**
 * Frontend
 */
if ( ! is_admin() ) {
	require_once CLIENT_POWER_TOOLS_DIR_PATH . 'shortcodes.php';
	require_once CLIENT_POWER_TOOLS_DIR_PATH . 'frontend/cpt-frontend.php';
	require_once CLIENT_POWER_TOOLS_DIR_PATH . 'frontend/cpt-client-dashboard.php';

	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\cpt_register_frontend_scripts' );
	function cpt_register_frontend_scripts() {
		global $post; // For localizing cpt-login-modal.js.

		wp_enqueue_style( 'cpt-frontend', CLIENT_POWER_TOOLS_DIR_URL . 'assets/css/frontend.css', array(), CLIENT_POWER_TOOLS_PLUGIN_VERSION );

		wp_register_script( 'cpt-login-modal', CLIENT_POWER_TOOLS_DIR_URL . 'assets/js/cpt-login-modal.js', array(), CLIENT_POWER_TOOLS_PLUGIN_VERSION, true );
		wp_localize_script(
			'cpt-login-modal',
			'cpt_vars',
			array(
				'postID'  => $post ? $post->ID : null,
				'isCPT'   => Common\cpt_is_client_dashboard(),
				'ajaxURL' => admin_url( 'admin-ajax.php' ),
			)
		);
		wp_enqueue_script( 'cpt-login-modal' );
		wp_enqueue_script( 'cpt-notices', CLIENT_POWER_TOOLS_DIR_URL . 'assets/js/cpt-notices.js', array( 'jquery' ), CLIENT_POWER_TOOLS_PLUGIN_VERSION, true );
	}
}

/**
 * Admin
 */
if ( is_admin() ) {
	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php'; // Ensures the WP_List_Table class is available.
	}
	require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-admin.php';

	require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-clients.php';
	require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-clients-table.php';
	require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-new-client.php';
	require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-edit-client.php';

	require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-client-managers.php';
	require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-client-manager-table.php';

	if ( get_option( 'cpt_module_messaging' ) ) {
		require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-admin-messages.php';
		require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-admin-messages-table.php';
	}

	if ( get_option( 'cpt_module_projects' ) ) {
		require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-projects.php';
		require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-projects-table.php';
		require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-new-project.php';
		require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-edit-project.php';
		require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-project-types.php';
		require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-project-types-table.php';
	}

	require_once CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-settings.php';
}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\cpt_register_admin_scripts' );
function cpt_register_admin_scripts() {
	// Only loads CPT admin styles and scripts on CPT admin pages.
	global $pagenow;
	if ( ! isset( $_GET['page'] ) ) {
		return;
	}
	$page = sanitize_key( $_GET['page'] );
	if ( 'admin.php' !== $pagenow || ! str_starts_with( $page, 'cpt' ) ) {
		return;
	}

	wp_enqueue_style( 'cpt-admin', CLIENT_POWER_TOOLS_DIR_URL . 'assets/css/admin.css', array(), CLIENT_POWER_TOOLS_PLUGIN_VERSION );
	wp_enqueue_script( 'cpt-admin', CLIENT_POWER_TOOLS_DIR_URL . 'assets/js/cpt-admin.js', array(), CLIENT_POWER_TOOLS_PLUGIN_VERSION, true );
	wp_enqueue_script( 'cpt-types', CLIENT_POWER_TOOLS_DIR_URL . 'assets/js/cpt-types.js', array( 'jquery' ), CLIENT_POWER_TOOLS_PLUGIN_VERSION, true );
	wp_register_script( 'cpt-stages', CLIENT_POWER_TOOLS_DIR_URL . 'assets/js/cpt-stages.js', array( 'jquery', 'wp-i18n' ), CLIENT_POWER_TOOLS_PLUGIN_VERSION, true );
	wp_localize_script(
		'cpt-stages',
		'vars',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'update-stages-nonce' ),
		)
	);
	wp_enqueue_script( 'cpt-stages' );
}


// Activation
function cpt_activate() {
	set_transient( 'cpt_show_welcome_message', true, 86400 );

	// Checks for page selections and creates pages if necessary.
	$cpt_pages = array(
		'cpt_client_dashboard_page_selection' => __( 'Client Dashboard', 'client-power-tools' ),
		'cpt_knowledge_base_page_selection'   => __( 'Knowledge Base', 'client-power-tools' ),
	);

	foreach ( $cpt_pages as $key => $val ) {
		if ( ! get_option( $key ) ) {
			$new_page = array(
				'post_status' => 'publish',
				'post_title'  => $val,
				'post_type'   => 'page',
			);
			$page     = wp_insert_post( $new_page, $wp_error );
			if ( is_wp_error( $page ) ) {
				?>
					<div class="cpt-notice notice notice-error is-dismissible">
						<p>
							<?php
								printf(
									wp_kses_post(
										// Translators: %1$s and %2$s are HTML <a> tags for a link to the settings page.
										__( 'Something went wrong when creating pages. Please select a page from the %1$sSettings page%2$s.', 'client-power-tools' )
									),
									/* %1$s */ '<a href="' . esc_url( add_query_arg( 'cpt', 'cpt-settings', admin_url( 'admin.php' ) ) ) . '">',
									/* %2$s */ '</a>'
								);
							?>
						</p>
						<p>Error message: <?php echo esc_html( $post->get_error_message() ); ?></p>
					</div>
				<?php
			} else {
				update_option( $key, $page );
			}
		}
	}

	// Checks for default options and adds them if necessary.
	$admin           = get_user_by_email( get_bloginfo( 'admin_email' ) );
	$default_options = array(
		'cpt_client_statuses'                 => 'Active' . "\n" . 'Potential' . "\n" . 'Inactive',
		'cpt_default_client_status'           => 'Active',
		'cpt_new_client_email_subject_line'   => '[' . get_bloginfo( 'title' ) . '] ' . __( 'Your client account has been created!', 'client-power-tools' ),
		'cpt_new_client_email_message_body'   => '',
		'cpt_module_status_update_req_button' => true,
		'cpt_status_update_req_freq'          => 30,
		'cpt_status_update_req_notice_email'  => null,
		'cpt_module_messaging'                => true,
		'cpt_module_projects'                 => true,
		'cpt_projects_label'                  => array( 'Project', 'Projects' ),
		'cpt_project_statuses'                => 'Open' . "\n" . 'Closed',
		'cpt_default_project_status'          => 'Open',
		'cpt_send_message_content'            => false,
	);

	foreach ( $default_options as $key => $val ) {
		if ( ! get_option( $key ) ) {
			update_option( $key, $val );
		}
	}

	// Register CPT Messages Custom Post Type
	function cpt_message_post_type() {
		$labels = array(
			'name'                  => _x( 'Messages', 'Post Type General Name', 'client-power-tools' ),
			'singular_name'         => _x( 'Message', 'Post Type Singular Name', 'client-power-tools' ),
			'menu_name'             => __( 'Messages', 'client-power-tools' ),
			'name_admin_bar'        => __( 'Message', 'client-power-tools' ),
			'archives'              => __( 'Message Archives', 'client-power-tools' ),
			'attributes'            => __( 'Message Attributes', 'client-power-tools' ),
			'parent_item_colon'     => __( 'Parent Message:', 'client-power-tools' ),
			'all_items'             => __( 'All Messages', 'client-power-tools' ),
			'add_new_item'          => __( 'Add New Message', 'client-power-tools' ),
			'add_new'               => __( 'Add New', 'client-power-tools' ),
			'new_item'              => __( 'New Message', 'client-power-tools' ),
			'edit_item'             => __( 'Edit Message', 'client-power-tools' ),
			'update_item'           => __( 'Update Message', 'client-power-tools' ),
			'view_item'             => __( 'View Message', 'client-power-tools' ),
			'view_items'            => __( 'View Messages', 'client-power-tools' ),
			'search_items'          => __( 'Search Messages', 'client-power-tools' ),
			'not_found'             => __( 'Message Not found', 'client-power-tools' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'client-power-tools' ),
			'featured_image'        => __( 'Featured Image', 'client-power-tools' ),
			'set_featured_image'    => __( 'Set featured image', 'client-power-tools' ),
			'remove_featured_image' => __( 'Remove featured image', 'client-power-tools' ),
			'use_featured_image'    => __( 'Use as featured image', 'client-power-tools' ),
			'insert_into_item'      => __( 'Insert into message', 'client-power-tools' ),
			'uploaded_to_this_item' => __( 'Uploaded to this message', 'client-power-tools' ),
			'items_list'            => __( 'Messages list', 'client-power-tools' ),
			'items_list_navigation' => __( 'Messages list navigation', 'client-power-tools' ),
			'filter_items_list'     => __( 'Filter messages list', 'client-power-tools' ),
		);

		$capabilities = array(
			'edit_post'          => 'cpt_edit_message',
			'read_post'          => 'cpt_read_message',
			'delete_post'        => 'cpt_delete_message',
			'edit_posts'         => 'cpt_edit_messages',
			'edit_others_posts'  => 'cpt_edit_others_messages',
			'publish_posts'      => 'cpt_publish_message',
			'read_private_posts' => 'cpt_read_private_messages',
		);

		$args = array(
			'label'               => __( 'Message', 'client-power-tools' ),
			'description'         => __( 'Client Power Tools messages', 'client-power-tools' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'menu_position'       => 5,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => false,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'query_var'           => 'cpt_message',
			'rewrite'             => false,
			'capabilities'        => $capabilities,
			'show_in_rest'        => false,
		);

		register_post_type( 'cpt_message', $args );
	}

	add_action( 'init', __NAMESPACE__ . '\cpt_message_post_type', 0 );

	// Clears the permalinks.
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\cpt_activate' );


// Register CPT Projects Custom Post Type
// Uses user-defined labels so can't be in the activation hook, above.
if ( get_option( 'cpt_module_projects' ) ) {
	add_action( 'init', __NAMESPACE__ . '\cpt_project_post_type', 0 );
	function cpt_project_post_type() {
		$projects_label = Common\cpt_get_projects_label();

		$labels = array(
			'name'                  => $projects_label[0],
			'singular_name'         => $projects_label[0],
			'menu_name'             => $projects_label[1],
			'name_admin_bar'        => $projects_label[0],
			'archives'              => $projects_label[0] . ' ' . __( 'Archives', 'client-power-tools' ),
			'attributes'            => $projects_label[0] . ' ' . __( 'Attributes', 'client-power-tools' ),
			'parent_item_colon'     => __( 'Parent', 'client-power-tools' ) . ' ' . $projects_label[0] . ':',
			'all_items'             => __( 'All', 'client-power-tools' ) . ' ' . $projects_label[1],
			'add_new_item'          => __( 'Add New', 'client-power-tools' ) . ' ' . $projects_label[0],
			'add_new'               => __( 'Add New', 'client-power-tools' ),
			'new_item'              => __( 'New', 'client-power-tools' ) . ' ' . $projects_label[0],
			'edit_item'             => __( 'Edit', 'client-power-tools' ) . ' ' . $projects_label[0],
			'update_item'           => __( 'Update', 'client-power-tools' ) . ' ' . $projects_label[0],
			'view_item'             => __( 'View', 'client-power-tools' ) . ' ' . $projects_label[0],
			'view_items'            => __( 'View', 'client-power-tools' ) . ' ' . $projects_label[1],
			'search_items'          => __( 'Search ' . $projects_label[1], 'client-power-tools' ),
			'not_found'             => $projects_label[0] . ' ' . __( 'not found', 'client-power-tools' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'client-power-tools' ),
			'featured_image'        => __( 'Featured Image', 'client-power-tools' ),
			'set_featured_image'    => __( 'Set featured image', 'client-power-tools' ),
			'remove_featured_image' => __( 'Remove featured image', 'client-power-tools' ),
			'use_featured_image'    => __( 'Use as featured image', 'client-power-tools' ),
			'insert_into_item'      => __( 'Insert into', 'client-power-tools' ) . ' ' . strtolower( $projects_label[0] ),
			'uploaded_to_this_item' => __( 'Uploaded to this', 'client-power-tools' ) . ' ' . strtolower( $projects_label[0] ),
			'items_list'            => $projects_label[1] . ' ' . __( 'list', 'client-power-tools' ),
			'items_list_navigation' => $projects_label[1] . ' ' . __( 'list navigation', 'client-power-tools' ),
			'filter_items_list'     => __( 'Filter', 'client-power-tools' ) . ' ' . strtolower( $projects_label[1] ) . ' ' . __( 'list', 'client-power-tools' ),
		);

		$capabilities = array(
			'edit_post'          => 'cpt_edit_project',
			'read_post'          => 'cpt_read_project',
			'delete_post'        => 'cpt_delete_project',
			'edit_posts'         => 'cpt_edit_projects',
			'edit_others_posts'  => 'cpt_edit_others_projects',
			'publish_posts'      => 'cpt_publish_project',
			'read_private_posts' => 'cpt_read_private_projects',
		);

		$args = array(
			'label'               => $projects_label[0],
			'description'         => __( 'Client Power Tools', 'client-power-tools' ) . ' ' . strtolower( $projects_label[1] ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'menu_position'       => 5,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => false,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'query_var'           => 'cpt_project',
			'rewrite'             => false,
			'capabilities'        => $capabilities,
			'show_in_rest'        => false,
		);

		register_post_type( 'cpt_project', $args );
	}

	// Project Types
	add_action( 'init', __NAMESPACE__ . '\register_project_type_custom_taxonomy', 0 );
	function register_project_type_custom_taxonomy() {
		$projects_label = Common\cpt_get_projects_label();

		$labels = array(
			'name'                       => $projects_label[0] . ' ' . _x( 'Types', 'Taxonomy General Name', 'client-power-tools' ),
			'singular_name'              => $projects_label[0] . ' ' . _x( 'Type', 'Taxonomy Singular Name', 'client-power-tools' ),
			'menu_name'                  => $projects_label[0] . ' ' . __( 'Types', 'client-power-tools' ),
			'all_items'                  => __( 'All', 'client-power-tools' ) . ' ' . $projects_label[0] . ' ' . __( 'Types', 'client-power-tools' ),
			'parent_item'                => __( 'Parent', 'client-power-tools' ) . ' ' . $projects_label[0] . ' ' . __( 'Type', 'client-power-tools' ),
			'parent_item_colon'          => __( 'All', 'client-power-tools' ) . ' ' . $projects_label[0] . ' ' . __( 'Type', 'client-power-tools' ) . ':',
			'new_item_name'              => __( 'New', 'client-power-tools' ) . ' ' . $projects_label[0] . ' ' . __( 'Type', 'client-power-tools' ),
			'add_new_item'               => __( 'Add New', 'client-power-tools' ) . ' ' . $projects_label[0] . ' ' . __( 'Type', 'client-power-tools' ),
			'edit_item'                  => __( 'Edit', 'client-power-tools' ) . ' ' . $projects_label[0] . ' ' . __( 'Type', 'client-power-tools' ),
			'update_item'                => __( 'Update', 'client-power-tools' ) . ' ' . $projects_label[0] . ' ' . __( 'Type', 'client-power-tools' ),
			'view_item'                  => $projects_label[0] . ' ' . __( 'Type Item', 'client-power-tools' ),
			'separate_items_with_commas' => __( 'Separate', 'client-power-tools' ) . ' ' . strtolower( $projects_label[0] ) . ' ' . __( 'types with commas', 'client-power-tools' ),
			'add_or_remove_items'        => __( 'Add or remove', 'client-power-tools' ) . ' ' . strtolower( $projects_label[0] ) . ' ' . __( 'types', 'client-power-tools' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'client-power-tools' ),
			'popular_items'              => __( 'Popular', 'client-power-tools' ) . ' ' . $projects_label[0] . ' ' . __( 'Types', 'client-power-tools' ),
			'search_items'               => __( 'Search', 'client-power-tools' ) . ' ' . $projects_label[0] . ' ' . __( 'Types', 'client-power-tools' ),
			'not_found'                  => __( 'Not Found', 'client-power-tools' ),
			'no_terms'                   => __( 'No', 'client-power-tools' ) . ' ' . strtolower( $projects_label[0] ) . ' ' . __( 'types', 'client-power-tools' ),
			'items_list'                 => $projects_label[0] . ' ' . __( 'types list', 'client-power-tools' ),
			'items_list_navigation'      => $projects_label[0] . ' ' . __( 'types list navigation', 'client-power-tools' ),
		);

		$args = array(
			'labels'             => $labels,
			'default_term'       => array(
				'name' => 'Default',
				'slug' => 'default',
			),
			'hierarchical'       => false,
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_admin_column'  => true,
			'show_in_nav_menus'  => true,
			'show_tagcloud'      => false,
			'show_in_rest'       => true,
		);

		register_taxonomy( 'cpt-project-type', array( 'cpt_project' ), $args );
	}
}