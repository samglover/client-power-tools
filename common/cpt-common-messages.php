<?php

namespace Client_Power_Tools\Core\Common;

/*
 * This has to be loaded as a common file in order to use the admin-post action
 * hook. And it would probably be even more confusing to load it as a common
 * file but store it in the frontend directory/namespace. Even that sentence is
 * confusing.
 */

function cpt_messages( $clients_user_id ) {
	if (
		! get_option( 'cpt_module_messaging' ) ||
		! $clients_user_id
	) {
		return;
	}

	?>
		<div class="cpt-messages-list">
			<?php echo wp_kses_post( cpt_get_message_list( $clients_user_id ) ); ?>
		</div>
	<?php
}


function cpt_get_message_list( $clients_user_id ) {
	$paged = isset( $_GET['paged'] ) ? intval( sanitize_key( $_GET['paged'] ) ) : get_query_var( 'paged' );

	$cpt_messages = new \WP_Query(
		array(
			'meta_key'   => 'cpt_clients_user_id',
			'meta_value' => $clients_user_id,
			'orderby'    => array( 'date' => 'DESC' ),
			'paged'      => $paged,
			'post_type'  => 'cpt_message',
		)
	);

	ob_start();

	if ( $cpt_messages->have_posts() ) :
		while ( $cpt_messages->have_posts() ) :
			$cpt_messages->the_post();
			
			$message_id               = get_the_ID();
			$message_classes          = array( 'cpt-message', 'card' );
			$message_meta             = '';
			$is_status_update_request = get_post_meta( $message_id, 'cpt_status_update_request', true );

			if ( $is_status_update_request ) {
				$message_classes[] = 'status-update-request';
			}

			if ( get_the_author_meta( 'ID' ) === get_current_user_id() ) {
				$message_classes[] = 'my-message';
				$message_meta     .= '<strong>' . __( 'Sent by you ', 'client-power-tools' ) . '</strong>';
			} else {
				$message_classes[] = 'not-my-message';
				$message_meta     .= '<strong>' . __( 'Sent by ', 'client-power-tools' ) . get_the_author() . '</strong>';
			}

			$message_meta .= ' ' . __( 'on', 'client-power-tools' ) . ' ' . get_the_date( 'F jS, Y' ) . ' ' . __( 'at', 'client-power-tools' ) . ' ' . get_the_date( 'g:i a' ) . '.';

			$email_to  = get_post_meta( $message_id, 'cpt_email_to', true ) ? get_post_meta( $message_id, 'cpt_email_to', true ) : false;
			$email_ccs = cpt_cleanse_array_of_emails( explode( "\n", get_post_meta( $message_id, 'cpt_email_ccs', true ) ) );

			if ( $email_to ) {
				$message_meta .= ' <strong>' . __( 'Sent to', 'client-power-tools' ) . '</strong> ' . $email_to . '.';
			}
			if ( count( $email_ccs ) ) {
				$message_meta .= ' <strong>' . __( 'CCed to', 'client-power-tools' ) . '</strong> ' . cpt_array_to_strlist( $email_ccs ) . '.';
			}

			?>
				<div 
					id="cpt-message-<?php echo esc_attr( $message_id ); ?>" 
					class="<?php echo esc_attr( implode( ' ', $message_classes ) ); ?>"
				>
					<div class="cpt-message-content">
						<?php if ( get_the_title() && get_the_title() !== 'Untitled' ) { ?>
							<h3 class="cpt-message-title"><?php the_title(); ?></h3>
						<?php } ?>
						<?php
						if ( ! $is_status_update_request ) {
							echo wp_kses_post( get_the_content() );
						}
						?>
					</div>
					<p class="cpt-message-meta"><?php echo wp_kses_post( $message_meta ); ?></p>
				</div>
			<?php
		endwhile;

		$big        = 999999;
		$page_links = paginate_links(
			array(
				'base'    => str_replace( $big, '%#%', get_pagenum_link( $big, false ) ),
				'format'  => '?paged=%#%',
				'current' => max( 1, $paged ),
				'total'   => $cpt_messages->max_num_pages,
			)
		);

		echo $page_links ? wp_kses_post( $page_links ) : '';
	else :
		?>
			<p><?php esc_html_e( 'No messages found.', 'client-power-tools' ); ?></p>
		<?php
		wp_reset_postdata();
	endif;

	return ob_get_clean();
}


function cpt_new_message_form( $clients_user_id ) {
	?>
		<form 
			action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" 
			method="POST"
		>
			<?php wp_nonce_field( 'cpt_new_message_added', 'cpt_new_message_nonce' ); ?>
			<input 
				name="action" 
				value="cpt_new_message_added" 
				type="hidden"
			>
			<input 
				name="clients_user_id" 
				value="<?php echo esc_attr( $clients_user_id ); ?>" 
				type="hidden"
			>
			<div class="cpt-row">
				<div class="form-field span-6">
					<label for="subject_line">
						<?php esc_html_e( 'Subject Line', 'client-power-tools' ); ?>
					</label>
					<input 
						name="subject_line" 
						id="subject_line" 
						class="large-text" 
						type="text"
					>
					<p class="description">
						<?php
							printf(
								// translators: %s is the current user's display name.
								esc_html__( 'If you leave this field empty the subject line will be "New message from %s".', 'client-power-tools' ),
								esc_html( cpt_get_display_name( get_current_user_id() ) )
							);
						?>
					</p>
				</div>
			</div>
			<div class="cpt-row">
				<div class="form-field span-6">
					<label for="message">
						<?php esc_html_e( 'Message', 'client-power-tools' ); ?>
					</label>
					<?php
					$editor_args = array(
						'editor_height' => 205,
						'media_buttons' => false,
						'quicktags'     => false,
						'textarea_name' => 'message',
						'tinymce'       => array(
							'toolbar1' => 'bold, italic, bullist, numlist, blockquote, outdent, indent, link, unlink',
							'toolbar2' => '',
							'toolbar3' => '',
						),
					);
					\wp_editor( '', 'cpt-message-editor', $editor_args );
					?>
				</div>
			</div>
			<?php
			if ( is_admin() ) {
				$email_ccs = explode( "\n", get_user_meta( $clients_user_id, 'cpt_email_ccs', true ) );
				$email_ccs = cpt_cleanse_array_of_emails( $email_ccs );
				if ( $email_ccs ) {
					?>
						<div class="cpt-row">
							<div class="form-field span-6">
								<fieldset>
									<legend>Email CCs</legend>
										<?php foreach ( $email_ccs as $key => $val ) { ?>
											<label for="email_cc_<?php echo esc_attr( $key ); ?>">
												<input 
													name="email_ccs[]" 
													value="<?php echo esc_attr( $val ); ?>"
													id="email_cc_<?php echo esc_attr( $key ); ?>"
													type="checkbox" 
													checked
												>
												<?php echo esc_html( $val ); ?>
											</label>
										<?php } ?>
								</fieldset>
								<p class="description">
									<?php
										printf(
											wp_kses_post(
												// translators: %1$s and %2$s are strong tags.
												__( 'To add or remove emails, use the %1$sEdit Client%2$s button, above.', 'client-power-tools' )
											),
											'<strong>',
											'</strong>'
										);
									?>
								</p>
							</div>
						</div>
					<?php
				}
				?>
				<div class="cpt-row">
					<div class="form-field span-6">
						<fieldset>
						<?php if ( get_option( 'cpt_send_message_content' ) === false ) { ?>
								<label for="send_message_content">
									<input name="send_message_content" id="send_message_content" type="checkbox" value="1">
									<?php esc_html_e( 'Send message content.', 'client-power-tools' ); ?>
								</label>
								<p class="description">
									<?php esc_html_e( 'If checked, the client will receive the actual message by email instead of a notification with a prompt to log into their client portal. This is less secure.', 'client-power-tools' ); ?>
								</p>
							<?php } else { ?>
								<label for="send_notification_only">
									<input name="send_notification_only" id="send_notification_only" type="checkbox" value="1">
									<?php esc_html_e( 'Send notification only.', 'client-power-tools' ); ?>
								</label>
								<p class="description">
									<?php esc_html_e( 'If checked, the client will receive an email letting them know they have a message, but they will have to log into their client dashboard to view the body of the message. This is more secure.', 'client-power-tools' ); ?>
								</p>
							<?php } ?>
						</fieldset>
					</div>
				</div>
			<?php } ?>
			<div class="cpt-row cpt-buttons">
				<p class="submit">
					<input 
						name="submit" 
						id="submit" 
						class="button button-primary wp-element-button" 
						type="submit" 
						value="<?php esc_html_e( 'Send Message', 'client-power-tools' ); ?>"
					>
				</p>
			</div>
		</form>
	<?php

	if ( is_admin() ) {
		\_WP_Editors::enqueue_scripts();
		\_WP_Editors::editor_js();
		\print_footer_scripts();
	}
}


add_action( 'admin_post_cpt_new_message_added', __NAMESPACE__ . '\cpt_process_new_message' );
function cpt_process_new_message() {
	if ( isset( $_POST['cpt_new_message_nonce'] ) && wp_verify_nonce( $_POST['cpt_new_message_nonce'], 'cpt_new_message_added' ) ) {
		$post_title      = wp_strip_all_tags( sanitize_text_field( $_POST['subject_line'] ) );
		$post_content    = wp_kses_post( $_POST['message'] );
		$clients_user_id = sanitize_key( intval( $_POST['clients_user_id'] ) );
		$client_data     = cpt_get_client_data( $clients_user_id );

		// Figures out whether to send the full content of this message.
		$send_msg_content_default = get_option( 'cpt_send_message_content' );

		if ( ! $send_msg_content_default ) {
			if ( isset( $_POST['send_message_content'] ) && $_POST['send_message_content'] === 1 ) {
				$send_this_msg_content = true;
			} else {
				$send_this_msg_content = false;
			}
		} elseif ( isset( $_POST['send_notification_only'] ) && $_POST['send_notification_only'] === 1 ) {
				$send_this_msg_content = false;
		} else {
			$send_this_msg_content = true;
		}

		/**
		 * Note. When creating a new message, for the post slug we generate an md5
		 * hash from the timestamp plus a random integer, making the message URL
		 * pretty much impossible to guess.
		 */
		if ( is_admin() && isset( $_POST['email_ccs'] ) ) {
			$email_ccs = implode( "\n", cpt_cleanse_array_of_emails( $_POST['email_ccs'] ) );
		}

		$new_message = array(
			'post_name'    => md5( time() . random_int( 0, PHP_INT_MAX ) ),
			'post_title'   => $post_title,
			'post_content' => $post_content,
			'post_status'  => 'publish',
			'post_type'    => 'cpt_message',
			'meta_input'   => array(
				'cpt_clients_user_id'      => $clients_user_id,
				'cpt_send_message_content' => $send_this_msg_content,
				'cpt_email_to'             => isset( $_POST['email_to'] ) ? $_POST['email_to'] : false,
				'cpt_email_ccs'            => isset( $email_ccs ) ? $email_ccs : false,
			),
		);

		$post = wp_insert_post( $new_message, $wp_error );

		if ( is_wp_error( $post ) ) {
			$result = sprintf(
				// translators: %s is the error message.
				__( 'Message could not be sent. Error message: %s', 'client-power-tools' ),
				$post->get_error_message()
			);
		} else {
			cpt_message_notification( $post );
			$result = __( 'Message sent!', 'client-power-tools' );
		}

		set_transient( 'cpt_notice_for_user_' . get_current_user_id(), $result, 15 );
		wp_redirect( $_POST['_wp_http_referer'] );
		exit;
	} else {
		die();
	}
}


function cpt_message_notification( $message_id ) {
	if ( ! $message_id ) {
		return;
	}

	$send_this_msg_content = get_post_meta( $message_id, 'cpt_send_message_content', true );

	$msg_obj         = get_post( $message_id );
	$sender_id       = $msg_obj->post_author;
	$clients_user_id = get_post_meta( $message_id, 'cpt_clients_user_id', true );
	$client_data     = cpt_get_client_data( $clients_user_id );

	$from_name  = get_the_author_meta( 'display_name', $msg_obj->post_author );
	$from_email = get_the_author_meta( 'user_email', $msg_obj->post_author );
	$email_ccs  = explode( "\n", get_post_meta( $message_id, 'cpt_email_ccs', true ) );

	$headers[] = 'Content-Type: text/html; charset=UTF-8';
	$headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';

	foreach ( $email_ccs as $i => $cc ) {
		$headers[] = 'Cc: ' . trim( $cc );
	}

	if ( $msg_obj->post_title ) {
		$subject = $msg_obj->post_title;
	} else {
		$subject = sprintf(
			// translators: %s is the sender's name.
			__( 'You have a new message from %s', 'client-power-tools' ),
			$from_name
		);
	}

	if ( $send_this_msg_content ) {
		$message = apply_filters( 'the_content', get_the_content( null, false, $msg_obj ) );
	} elseif ( $sender_id === $clients_user_id ) {
		$message = '<p>' . __( 'To read your message, please visit your client dashboard.', 'client-power-tools' ) . '</p>';
	} else {
		$message = '<p>' . __( 'To read this message, please view the client page.', 'client-power-tools' ) . '</p>';
	}

	if ( $sender_id === $clients_user_id ) {
		$to         = $client_data['manager_email'];
		$button_url = cpt_get_client_profile_url( $clients_user_id ) . '#cpt-message-' . $message_id;
	} else {
		$to         = $client_data['email'];
		$button_url = cpt_get_client_dashboard_url() . '#cpt-message-' . $message_id;
	}

	$button_txt = __( 'Go to Message', 'client-power-tools' );
	$message    = cpt_get_email_card( $subject, $message, $button_txt, $button_url );

	wp_mail( $to, $subject, $message, $headers );
}
