<?php

/**
 * Plugin Name: Client Power Tools
 * Plugin URI: https://clientpowertools.com
 * Description: Client Power Tools is an easy-to-use private client dashboard and communication portal built for independent contractors, consultants, lawyers, and other professionals.
 * Version: 1.6.5
 * Author: Sam Glover
 * Author URI: https://samglover.net
 * Text Domain: client-power-tools
 */

namespace Client_Power_Tools\Core;
use Client_Power_Tools\Core\Common;
use Client_Power_Tools\Core\Frontend;

if (!defined('ABSPATH')) exit;

/**
 * Constants
 */
define('CLIENT_POWER_TOOLS_PLUGIN_VERSION', '1.6.5');
define('CLIENT_POWER_TOOLS_DIR_PATH', plugin_dir_path(__FILE__));
define('CLIENT_POWER_TOOLS_DIR_URL', plugin_dir_url(__FILE__));


/**
 * Plugin Files
 */
require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'common/cpt-common.php');
require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'common/cpt-login.php');
require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'common/cpt-status-update-request-button.php');
require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'common/cpt-messages.php');

function cpt_register_common_scripts() {
	wp_enqueue_script('cpt-common', CLIENT_POWER_TOOLS_DIR_URL . 'assets/js/cpt-common.js', ['jquery'], CLIENT_POWER_TOOLS_PLUGIN_VERSION, true);
}

add_action('wp_enqueue_scripts', __NAMESPACE__ . '\cpt_register_common_scripts');
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\cpt_register_common_scripts');


if (!is_admin()) {
	require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'shortcodes.php');
	require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'frontend/cpt-frontend.php');
	require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'frontend/cpt-client-dashboard.php');

	function cpt_register_frontend_scripts() {
		global $post; // For localizing cpt-frontend.js

		wp_enqueue_style('cpt-common', CLIENT_POWER_TOOLS_DIR_URL . 'assets/css/style.css', [], CLIENT_POWER_TOOLS_PLUGIN_VERSION);

		wp_register_script('cpt-frontend', CLIENT_POWER_TOOLS_DIR_URL . 'assets/js/cpt-frontend.js', ['jquery'], CLIENT_POWER_TOOLS_PLUGIN_VERSION, true);
		wp_localize_script('cpt-frontend', 'cpt_vars', [
			'postID' => $post ? $post->ID : null,
			'isCPT'	=> Frontend\cpt_is_cpt(),
			'ajaxURL' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('cpt-login-nonce'),
		]);
		wp_enqueue_script('cpt-frontend');
	}

	add_action('wp_enqueue_scripts', __NAMESPACE__ . '\cpt_register_frontend_scripts');
}


if (is_admin()) {
	require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'includes/class-wp-list-table.php');
	require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-admin.php');
	require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-admin-messages.php');
	require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-admin-messages-table.php');
	require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-new-client.php');
	require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-clients.php');
	require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-client-table.php');
	require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-edit-client.php');
	require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-client-managers.php');
	require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-client-manager-table.php');
	require_once(CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-settings.php');
}


function cpt_register_admin_styles() {
	wp_enqueue_style('cpt-admin', CLIENT_POWER_TOOLS_DIR_URL . 'assets/css/admin.css', [], CLIENT_POWER_TOOLS_PLUGIN_VERSION);
	wp_enqueue_script('cpt-admin', CLIENT_POWER_TOOLS_DIR_URL . 'assets/js/cpt-admin.js', [], CLIENT_POWER_TOOLS_PLUGIN_VERSION, true);
}

add_action('admin_enqueue_scripts', __NAMESPACE__ . '\cpt_register_admin_styles');


// Activation
function cpt_activate() {
	set_transient('cpt_show_welcome_message', true, 86400);

	// Checks for page selections and creates pages if necessary.
	$cpt_pages = [
		'cpt_client_dashboard_page_selection' => 'Client Dashboard',
		'cpt_knowledge_base_page_selection' => 'Knowledge Base',
	];

	foreach ($cpt_pages as $key => $val) {
		if (! get_option($key)) {
			$new_page = [
				'post_status' => 'publish',
	      'post_title' => __($val),
				'post_type' => 'page',
			];

			$page = wp_insert_post($new_page, $wp_error);

			if (is_wp_error($page)) {
				?>
					<div class="cpt-notice notice notice-error is-dismissible">
						<p><?php _e('Something went wrong when creating pages. Please select a page from the <a href="' . admin_url('admin.php?page=cpt-settings') . '">Settings page</a>.'); ?></p>
						<p>Error message: <?php echo $post->get_error_message(); ?></p>
					</div>
				<?php
			} else {
				update_option($key, $page);
			}
		}
  }


	/*
	* Checks for default options and adds them if necessary.
	*/
	$admin = get_user_by_email(get_bloginfo('admin_email'));

	$default_options = [
		'cpt_client_statuses'									=> 'Active' . "\n" . 'Potential' . "\n" . 'Inactive',
		'cpt_default_client_manager'					=> $admin->ID,
		'cpt_default_client_status'						=> 'Active',
		'cpt_new_client_email_subject_line'  	=> '[' . get_bloginfo('title') . '] ' . __('Your client account has been created!', 'client-power-tools'),
    'cpt_new_client_email_message_body'  	=> '',
		'cpt_module_status_update_req_button'	=> true,
		'cpt_status_update_req_freq'					=> 30,
		'cpt_status_update_req_notice_email'	=> null,
		'cpt_module_messaging'								=> true,
		'cpt_send_message_content'						=> false,
 ];

  foreach ($default_options as $key => $val) {
    if (!get_option($key)) {
      update_option($key, $val);
    }
  }

	// Register CPT Messages Custom Post Type
	function cpt_message_post_type() {
		$labels = [
			'name'                  => _x('Messages', 'Post Type General Name', 'client-power-tools'),
			'singular_name'         => _x('Message', 'Post Type Singular Name', 'client-power-tools'),
			'menu_name'             => __('Messages', 'client-power-tools'),
			'name_admin_bar'        => __('Message', 'client-power-tools'),
			'archives'              => __('Message Archives', 'client-power-tools'),
			'attributes'            => __('Message Attributes', 'client-power-tools'),
			'parent_item_colon'     => __('Parent Message:', 'client-power-tools'),
			'all_items'             => __('All Messages', 'client-power-tools'),
			'add_new_item'          => __('Add New Message', 'client-power-tools'),
			'add_new'               => __('Add New', 'client-power-tools'),
			'new_item'              => __('New Message', 'client-power-tools'),
			'edit_item'             => __('Edit Message', 'client-power-tools'),
			'update_item'           => __('Update Message', 'client-power-tools'),
			'view_item'             => __('View Message', 'client-power-tools'),
			'view_items'            => __('View Messages', 'client-power-tools'),
			'search_items'          => __('Search Messages', 'client-power-tools'),
			'not_found'             => __('Message Not found', 'client-power-tools'),
			'not_found_in_trash'    => __('Not found in Trash', 'client-power-tools'),
			'featured_image'        => __('Featured Image', 'client-power-tools'),
			'set_featured_image'    => __('Set featured image', 'client-power-tools'),
			'remove_featured_image' => __('Remove featured image', 'client-power-tools'),
			'use_featured_image'    => __('Use as featured image', 'client-power-tools'),
			'insert_into_item'      => __('Insert into message', 'client-power-tools'),
			'uploaded_to_this_item' => __('Uploaded to this message', 'client-power-tools'),
			'items_list'            => __('Messages list', 'client-power-tools'),
			'items_list_navigation' => __('Messages list navigation', 'client-power-tools'),
			'filter_items_list'     => __('Filter messages list', 'client-power-tools'),
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
			'label'                 => __('Message', 'client-power-tools'),
			'description'           => __('Client Power Tools messages', 'client-power-tools'),
			'labels'                => $labels,
			'supports'              => ['title', 'editor'],
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

		register_post_type('cpt_message', $args);
	}

	add_action('init', __NAMESPACE__ . '\cpt_message_post_type', 0);

	// Clears the permalinks.
	flush_rewrite_rules();
}

register_activation_hook(__FILE__, __NAMESPACE__ . '\cpt_activate');
