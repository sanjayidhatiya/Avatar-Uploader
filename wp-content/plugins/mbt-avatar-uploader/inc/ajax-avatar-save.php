<?php
/*
 * Save Avatar via Avatar Field
 */
function mbt_avatar_save(){
	global $wpdb;
	
	$user_id			= get_current_user_id();
	$title				= $user_id.'-'.time();

	$upload_dir			= wp_upload_dir();
	
	$upload_path 		= apply_filters( 'mbt_user_avatar_upload_url', $upload_dir['basedir'] . '/mbt_user_avatars' ); 
	if (!is_writable( $upload_path ) ) {  /*Check if upload dir is writable*/
		wp_send_json_error(
			array(
				'message' => esc_html__( 'Upload path permission deny', 'mbt-avatar-uploader' ),
			)
		);
	}
	$old_attach_id = get_user_meta( $user_id, 'mbt_user_avatar_attachment_id', true);
	if($old_attach_id>0) {
		wp_delete_attachment( $old_attach_id );
	}

	$base64_img			= $_POST['user_avatar'];
	$img				= str_replace( 'data:image/png;base64,', '', $base64_img );
	$img             	= str_replace( ' ', '+', $img );
	$decoded         	= base64_decode( $img );
	$filename        	= $title . '.png';
	$file_type       	= 'image/png';
	$hashed_filename 	= $filename;

	// Save the image in the uploads directory.
	$upload_file = file_put_contents( $upload_path .'/'. $hashed_filename, $decoded );

	$pic_path = $upload_path . '/' . sanitize_file_name( $hashed_filename );

	$attachment = array(
		'post_mime_type' => $file_type,
		'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $hashed_filename ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
		'guid'           => $upload_dir['baseurl'] . '/mbt_user_avatars/' .sanitize_file_name( $hashed_filename )
	);

	$attach_id = wp_insert_attachment( $attachment, $pic_path );
	if ( is_wp_error( $attach_id ) ) {
		wp_send_json_error(
			array(
				'message' => $attach_id->get_error_message(),
			)
		);
	}
	include_once ABSPATH . 'wp-admin/includes/image.php';

	// Generate and save the attachment metas into the database.
	wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $pic_path ) );

	$url = wp_get_attachment_url( $attach_id );
	if ( empty( $url ) ) {
		$url = MBT_AU_PLUGIN_URL . '/assets/images/mbt-default-user-avatar.png';
	}
	$user_id = get_current_user_id();
	update_user_meta( $user_id, 'mbt_user_avatar_attachment_id', $attach_id );

	wp_send_json_success(
		array(
			'attachment_id'	=> $attach_id,
			'avatar_url'	=> $url,
			'message'		=> esc_html__( 'Avatar updated successfully', 'mbt-avatar-uploader' ),
		)
	);
}
add_action('wp_ajax_nopriv_mbt_avatar_save', 'mbt_avatar_save');
add_action('wp_ajax_mbt_avatar_save', 'mbt_avatar_save');

/*
 * Remove Avatar
 */
function mbt_avatar_remove(){
	global $wpdb;
	
	$user_id			= get_current_user_id();
	
	$old_attach_id		= get_user_meta( $user_id, 'mbt_user_avatar_attachment_id', true);
	if($old_attach_id>0) {
		wp_delete_attachment( $old_attach_id );
		delete_user_meta( $user_id, 'mbt_user_avatar_attachment_id');
	}

	$url = MBT_AU_PLUGIN_URL . '/assets/images/mbt-default-user-avatar.png';

	wp_send_json_success(
		array(
			'avatar_url'	=> $url,
			'message'		=> esc_html__( 'Avatar removed successfully', 'mbt-avatar-uploader' ),
		)
	);
}
add_action('wp_ajax_nopriv_mbt_avatar_remove', 'mbt_avatar_remove');
add_action('wp_ajax_mbt_avatar_remove', 'mbt_avatar_remove');