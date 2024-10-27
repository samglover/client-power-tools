<?php

namespace Client_Power_Tools\Core\Admin;

use Client_Power_Tools\Core\Common;

function cpt_clients() {
	if ( ! current_user_can( 'cpt_view_clients' ) ) {
		wp_die( '<p>' . esc_html__( 'Sorry, you are not allowed to access this page.', 'client-power-tools' ) . '</p>', 403 );
	}
	?>
		<div id="cpt-admin" class="wrap">
			<?php if ( isset( $_REQUEST['user_id'] ) ) { ?>
				<p>
					<a href="<?php echo esc_url( remove_query_arg( 'user_id' ) ); ?>">
						&lt;
						<?php esc_html_e( 'Back to Clients', 'client-power-tools' ); ?>
					</a>
				</p>
			<?php } ?>
			<header id="cpt-admin-header">
			<img class="cpt-logo" src="<?php echo esc_url( CLIENT_POWER_TOOLS_DIR_URL ); ?>assets/images/cpt-logo.png" alt="Client Power Tools" />
				<div id="cpt-admin-page-title">
					<?php if ( ! isset( $_REQUEST['user_id'] ) ) { ?>
						<h1 id="cpt-page-title">
							<?php esc_html_e( 'Clients', 'client-power-tools' ); ?>
						</h1>
						<p id="cpt-subtitle">Client Power Tools</p>
					<?php } else { ?>
						<?php
							$user_id         = isset( $_REQUEST['user_id'] ) ? sanitize_key( intval( $_REQUEST['user_id'] ) ) : false;
							$clients_user_id = Common\cpt_is_client( $user_id ) ? $user_id : false;
							$client_data     = $clients_user_id ? Common\cpt_get_client_data( $clients_user_id ) : false;
							$client_id       = $clients_user_id ? $client_data['client_id'] : false;
						?>
						<?php if ( $clients_user_id ) { ?>
							<p id="cpt-client-status">
								<?php echo esc_html( $client_data['status'] ); ?>
							</p>
						<?php } ?>
						<h1 id="cpt-page-title">
							<?php
							if ( $clients_user_id ) {
								echo esc_html( $client_data['client_name'] );
							} else {
								esc_html_e( 'Error: No such client.', 'client-power-tools' );
							}
							?>
							<?php if ( $client_id ) { ?>
								<span style="color:silver">(<?php echo esc_html( $client_id ); ?>)</span>
							<?php } ?>
						</h1>
						<?php if ( isset( $client_data['manager_id'] ) && ! empty( $client_data['manager_id'] ) ) { ?>
							<p id="cpt-client-manager">
								<?php
								if ( get_current_user_id() === $client_data['manager_id'] ) {
									esc_html_e( 'Your Client', 'client-power-tools' );
								} else {
									echo esc_html( Common\cpt_get_display_name( $client_data['manager_id'] ) . '\'s ' . __( 'Client', 'client-power-tools' ) );
								}
								?>
							</p>
						<?php } ?>
					<?php } ?>
				</div>
			</header>
			<hr class="wp-header-end">
			<?php
			if ( ! isset( $_REQUEST['user_id'] ) ) {
				if ( current_user_can( 'cpt_manage_clients' ) ) {
					?>
							<button class="button wp-element-button cpt-click-to-expand">
								<?php esc_html_e( 'Add a Client' ); ?>
							</button>
							<div class="cpt-this-expands">
								<div class="form-wrap form-wrap-new_client">
									<h2><?php esc_html_e( 'Add a Client', 'client-power-tools' ); ?></h2>
									<?php include CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-new-client-form.php'; ?>
								</div>
							</div>
						<?php
				}
				cpt_client_list();
			} elseif ( Common\cpt_is_client( $clients_user_id ) ) {
					cpt_get_client_profile( $clients_user_id );
			}
			?>
		</div>
	<?php
}


function cpt_client_list() {
	$client_list = new Client_List_Table();
	$client_list->prepare_items();
	?>
		<form id="client-list" method="GET">
			<?php $client_list->views(); ?>
			<?php $client_list->display(); ?>
		</form>
	<?php
}


function cpt_get_client_profile( $clients_user_id ) {
	if ( ! $clients_user_id ) {
		return;
	}
	$client_data = Common\cpt_get_client_data( $clients_user_id );
	$client_name = Common\cpt_get_client_name( $clients_user_id );
	?>
		<nav class="cpt-buttons cpt-row gap-sm">
			<?php if ( current_user_can( 'cpt_manage_clients' ) ) { ?>
				<button class="button wp-element-button cpt-click-to-expand">
					<?php esc_html_e( 'Edit Client', 'client-power-tools' ); ?>
				</button>
			<?php } ?>
			<?php if ( get_option( 'cpt_module_projects' ) ) { ?>
				<button class="button wp-element-button cpt-click-to-expand">
					<?php
						printf(
							// translators: %s is the projects label.
							esc_html__( 'Add a %s', 'client-power-tools' ),
							esc_html( Common\cpt_get_projects_label( 'singular' ) )
						);
					?>
				</button>
			<?php } ?>
			<?php if ( get_option( 'cpt_module_messaging' ) ) { ?>
				<button class="button wp-element-button cpt-click-to-expand">
					<?php esc_html_e( 'New Message', 'client-power-tools' ); ?>
				</button>
			<?php } ?>
		</nav>
		<div class="cpt-expanders">
			<?php if ( current_user_can( 'cpt_manage_clients' ) ) { ?>
				<div class="cpt-this-expands">
					<div class="form-wrap">
						<h2><?php esc_html_e( 'Edit This Client', 'client-power-tools' ); ?></h2>
						<?php include CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-edit-client-form.php'; ?>
						<span id="cpt-delete-client-link">
							<?php
								printf(
									// translators: %s is the client's name.
									esc_html__( 'Delete %s', 'client-power-tools' ),
									esc_html( $client_name )
								);
							?>
						</span>
						<?php cpt_delete_client_modal( $clients_user_id ); ?>
					</div>
				</div>
			<?php } ?>
			<?php if ( get_option( 'cpt_module_projects' ) ) { ?>
				<div class="cpt-this-expands">
					<div class="form-wrap form-wrap-new_project">
						<h2>
							<?php
								printf(
									// translators: %s is the projects label.
									esc_html__( 'Add a %s', 'client-power-tools' ),
									esc_html( Common\cpt_get_projects_label( 'singular' ) )
								);
							?>
						</h2>
						<?php include CLIENT_POWER_TOOLS_DIR_PATH . 'admin/cpt-new-project-form.php'; ?>
					</div>
				</div>
			<?php } ?>
			<?php if ( get_option( 'cpt_module_messaging' ) ) { ?>
				<div class="cpt-this-expands">
					<div class="form-wrap">
					<h3><?php esc_html_e( 'New Message', 'client-power-tools' ); ?></h3>
					<?php Common\cpt_new_message_form( $clients_user_id ); ?>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php
	if ( get_option( 'cpt_module_projects' ) ) {
		?>
			<section id="cpt-projects">
				<h2 class="cpt-row"><?php echo esc_html( Common\cpt_get_projects_label( 'plural' ) ); ?></h2>
				<?php Common\cpt_get_projects_list( $clients_user_id ); ?>
			</section>
		<?php
	}
	if ( get_option( 'cpt_module_messaging' ) ) {
		?>
			<section id="cpt-messages">
				<h2 class="cpt-row"><?php esc_html_e( 'Messages', 'client-power-tools' ); ?></h2>
				<?php Common\cpt_messages( $clients_user_id ); ?>
			</section>
		<?php
	}
}
