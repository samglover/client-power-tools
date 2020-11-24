<?php

/*
Plugin Name: Client Power Tools
Plugin URI: https://clientpowertools.com
Description: Client Power Tools is an easy-to-use private client dashboard and communication portal built for independent contractors, consultants, lawyers, and other professionals.
Author: Sam Glover
Version: 1.3
Author URI: https://samglover.net
*/

namespace Client_Power_Tools\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Constants
*/
define( 'CLIENT_POWER_TOOLS_PLUGIN_VERSION', '1.3' );
define( 'CLIENT_POWER_TOOLS_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'CLIENT_POWER_TOOLS_DIR_URL', plugin_dir_url( __FILE__ ) );


/**
* Plugin Files
*/
require_once( CLIENT_POWER_TOOLS_DIR_PATH . 'common/cpt-common.php' );
require_once( CLIENT_POWER_TOOLS_DIR_PATH . 'common/cpt-client-dashboard.php' );
require_once( CLIENT_POWER_TOOLS_DIR_PATH . 'common/cpt-messages.php' );


function cpt_register_common_scripts() {

	wp_register_style( 'cpt-common-css', CLIENT_POWER_TOOLS_DIR_URL . 'common/cpt-common.css' );
	wp_enqueue_style( 'cpt-common-css' );

	wp_register_script( 'cpt-common-js', CLIENT_POWER_TOOLS_DIR_URL . 'common/cpt-common.js', [ 'jquery' ], '', true );
	wp_enqueue_script( 'cpt-common-js' );

}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\cpt_register_common_scripts' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\cpt_register_common_scripts' );


if ( ! is_admin() ) {

	require_once( CLIENT_POWER_TOOLS_DIR_PATH . 'frontend/cpt-frontend.php' );

	function cpt_register_frontend_scripts() {

		global $post; // For localizing cpt-frontend.js

		wp_register_style( 'cpt-frontend-css', CLIENT_POWER_TOOLS_DIR_URL . 'frontend/cpt-frontend.css' );
		wp_enqueue_style( 'cpt-frontend-css' );

		wp_register_script( 'cpt-frontend-js', CLIENT_POWER_TOOLS_DIR_URL . 'frontend/cpt-frontend.js', [ 'jquery' ], '', true );
		wp_localize_script( 'cpt-frontend-js', 'cpt_frontend_js_vars',
			[
				'postID'			=> $post->ID,
				'dashboardID'	=> get_option( 'cpt_client_dashboard_page_selection' ),
			]
		);
		wp_enqueue_script( 'cpt-frontend-js' );

	}

	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\cpt_register_frontend_scripts' );

}


if ( is_admin() ) {

	require_once( CLIENT_POWER_TOOLS_DIR_PATH . 'includes/class-wp-list-table.php' );
	require_once( CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-admin.php' );
	require_once( CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-admin-messages.php' );
	require_once( CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-admin-messages-table.php' );
	require_once( CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-new-client.php' );
	require_once( CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-clients.php' );
	require_once( CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-client-table.php' );
	require_once( CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-edit-client.php' );
	require_once( CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-client-managers.php' );
	require_once( CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-client-manager-table.php' );
	require_once( CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-settings.php' );

}


function cpt_register_admin_styles() {

	wp_register_style( 'cpt-admin-css', CLIENT_POWER_TOOLS_DIR_URL . 'admin/cpt-admin.css' );
	wp_enqueue_style( 'cpt-admin-css' );

	wp_register_script( 'cpt-admin-js', CLIENT_POWER_TOOLS_DIR_URL . 'admin/cpt-admin.js', [ 'jquery' ], '', true );
	wp_enqueue_script( 'cpt-admin-js' );

}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\cpt_register_admin_styles' );


// Activation
function cpt_activate() {

	/*
	* Checks to see if there is already a Client Dashboard, and
	* creates a page for it if not.
	*/
	if ( ! get_option( 'cpt_client_dashboard_page_selection' ) ) {

		$client_dashboard = [
			'post_status'   => 'publish',
      'post_title'    => __( 'Client Dashboard' ),
			'post_type'     => 'page',
		];

		$page = wp_insert_post( $client_dashboard, $wp_error );

		if ( is_wp_error( $page ) ) {

			?>

				<div class="cpt-notice notice notice-error is-dismissible">
					<p><?php _e( 'Something went wrong when creating a page for your client dashboard. Please select a page from the <a href="' . admin_url( 'admin.php?page=cpt-settings' ) . '">Settings page</a>.' ); ?></p>
					<p>Error message: <?php echo $post->get_error_message(); ?></p>
				</div>

			<?php

		} else {

			update_option( 'cpt_client_dashboard_page_selection', $page );

		}

	}


	/*
	* Checks for default options and adds them if necessary.
	*/

	$admin = get_user_by_email( get_bloginfo( 'admin_email' ) );

	$defaults = [
		'cpt_client_statuses'									=> 'Active' . "\n" . 'Potential' . "\n" . 'Inactive',
		'cpt_default_client_manager'					=> $admin->ID,
		'cpt_default_client_status'						=> 'Active',
		'cpt_show_status_update_req_button'		=> true,
		'cpt_status_update_req_freq'					=> 30,
		'cpt_status_update_req_notice_email'	=> null,
		'cpt_send_message_content'						=> false,
    'cpt_new_client_email_subject_line'  	=> 'Your client account has been created! Please set your password.',
    'cpt_new_client_email_message_body'  	=> '',
  ];

  foreach ( $defaults as $key => $val ) {

    if ( ! get_option( $key ) ) {
      update_option( $key, $val );
    }

  }

	// Register CPT Messages Custom Post Type
	function cpt_message_post_type() {

		$labels = [
			'name'                  => _x( 'Messages', 'Post Type General Name', 'text_domain' ),
			'singular_name'         => _x( 'Message', 'Post Type Singular Name', 'text_domain' ),
			'menu_name'             => __( 'Messages', 'text_domain' ),
			'name_admin_bar'        => __( 'Message', 'text_domain' ),
			'archives'              => __( 'Message Archives', 'text_domain' ),
			'attributes'            => __( 'Message Attributes', 'text_domain' ),
			'parent_item_colon'     => __( 'Parent Message:', 'text_domain' ),
			'all_items'             => __( 'All Messages', 'text_domain' ),
			'add_new_item'          => __( 'Add New Message', 'text_domain' ),
			'add_new'               => __( 'Add New', 'text_domain' ),
			'new_item'              => __( 'New Message', 'text_domain' ),
			'edit_item'             => __( 'Edit Message', 'text_domain' ),
			'update_item'           => __( 'Update Message', 'text_domain' ),
			'view_item'             => __( 'View Message', 'text_domain' ),
			'view_items'            => __( 'View Messages', 'text_domain' ),
			'search_items'          => __( 'Search Messages', 'text_domain' ),
			'not_found'             => __( 'Message Not found', 'text_domain' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
			'featured_image'        => __( 'Featured Image', 'text_domain' ),
			'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
			'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
			'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
			'insert_into_item'      => __( 'Insert into message', 'text_domain' ),
			'uploaded_to_this_item' => __( 'Uploaded to this message', 'text_domain' ),
			'items_list'            => __( 'Messages list', 'text_domain' ),
			'items_list_navigation' => __( 'Messages list navigation', 'text_domain' ),
			'filter_items_list'     => __( 'Filter messages list', 'text_domain' ),
		];

		$capabilities = [
			'edit_post'             => 'cpt_edit_message',
			'read_post'             => 'cpt_read_message',
			'delete_post'           => 'cpt_delete_message',
			'edit_posts'            => 'cpt_edit_messages',
			'edit_others_posts'     => 'cpt_edit_others_messages',
			'publish_posts'         => 'cpt_publish_message',
			'read_private_posts'    => 'cpt_read_private_messages',
		];

		$args = [
			'label'                 => __( 'Message', 'text_domain' ),
			'description'           => __( 'Client Power Tools messages', 'text_domain' ),
			'labels'                => $labels,
			'supports'              => [ 'title', 'editor' ],
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => false,
			'show_in_menu'          => false,
			'menu_position'         => 5,
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => false,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'query_var'             => 'cpt_message',
			'rewrite'               => false,
			'capabilities'          => $capabilities,
			'show_in_rest'          => false,
		];

		register_post_type( 'cpt_message', $args );

	}

	add_action( 'init', __NAMESPACE__ . '\cpt_message_post_type', 0 );


	// Clears the permalinks.
	flush_rewrite_rules();

}

register_activation_hook( __FILE__, __NAMESPACE__ . '\cpt_activate' );
