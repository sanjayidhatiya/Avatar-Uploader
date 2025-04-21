<?php

/**
 * Saves user avatar uploaded via AJAX request.
 * Checks file validity and saves attachment.
 *
 * @return void JSON response with success/error message.
 */
function mbtAvatarSave()
{

	check_ajax_referer('mbt_avatar_nonce', 'security');
	if (!is_user_logged_in()) {
		wp_send_json_error([
			'message' => esc_html__('You must be logged in to upload avatars.', 'mbt-avatar-uploader'),
		]);
	}

	$user_id			= get_current_user_id();
	$title				= $user_id . '-' . time();
	$upload_dir			= wp_upload_dir();

	$upload_path 		= apply_filters('mbt_user_avatar_upload_url', $upload_dir['basedir'] . '/mbt_user_avatars');
	if (!is_writable($upload_path)) {
		wp_send_json_error([
			'message' => esc_html__('Server issue: Avatar upload directory is not writable.', 'mbt-avatar-uploader'),
		]);
	}
	$old_attach_id = get_user_meta($user_id, 'mbt_user_avatar_attachment_id', true);
	if ($old_attach_id > 0) {
		wp_delete_attachment($old_attach_id);
	}

	$base64_img			= $_POST['user_avatar'];
	$img				= str_replace('data:image/png;base64,', '', $base64_img);
	$img             	= str_replace(' ', '+', $img);
	$decoded         	= base64_decode($img);
	$filename        	= $title . '.png';
	$file_type       	= 'image/png';
	$hashed_filename 	= $filename;

	$allowed_file_types = ['image/png', 'image/jpeg'];
	$file_info = getimagesizefromstring($decoded);

	if (!$file_info || !in_array($file_info['mime'], $allowed_file_types)) {
		wp_send_json_error([
			'message' => esc_html__('Invalid file type uploaded. Allowed: PNG, JPG, JPEG.', 'mbt-avatar-uploader'),
		]);
	}

	if (strlen($decoded) > (get_option('mbt_ua_setting')['max_size'] * 1024)) {
		wp_send_json_error([
			'message' => esc_html__('Uploaded file exceeds maximum allowed size.', 'mbt-avatar-uploader'),
		]);
	}

	// Save the image in the uploads directory.
	$upload_file = file_put_contents($upload_path . '/' . $hashed_filename, $decoded);
	if ($upload_file === false) {
		wp_send_json_error([
			'message' => esc_html__('Failed to write image to the server.', 'mbt-avatar-uploader'),
		]);
	}

	$pic_path = $upload_path . '/' . sanitize_file_name($hashed_filename);

	$attachment = array(
		'post_mime_type' => $file_type,
		'post_title'     => preg_replace('/\.[^.]+$/', '', basename($hashed_filename)),
		'post_content'   => '',
		'post_status'    => 'inherit',
		'guid'           => $upload_dir['baseurl'] . '/mbt_user_avatars/' . sanitize_file_name($hashed_filename)
	);

	$attach_id = wp_insert_attachment($attachment, $pic_path);
	if (is_wp_error($attach_id)) {
		wp_send_json_error(
			array(
				'message' => $attach_id->get_error_message(),
			)
		);
	}

	// phpcs:ignore WordPress.Files.FileInclude.FileInclude -- Needed for wp_generate_attachment_metadata()
	include_once ABSPATH . 'wp-admin/includes/image.php';


	// Generate and save the attachment metas into the database.
	wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $pic_path));

	$url = wp_get_attachment_url($attach_id);
	if (empty($url)) {
		$url = MBT_AU_PLUGIN_URL . '/assets/images/mbt-default-user-avatar.png';
	}
	$user_id = get_current_user_id();
	update_user_meta($user_id, 'mbt_user_avatar_attachment_id', $attach_id);

	wp_send_json_success(
		array(
			'attachment_id'	=> $attach_id,
			'avatar_url'	=> $url,
			'message'		=> esc_html__('Avatar updated successfully', 'mbt-avatar-uploader'),
		)
	);
}
add_action('wp_ajax_nopriv_mbt_avatar_save', 'mbtAvatarSave');
add_action('wp_ajax_mbt_avatar_save', 'mbtAvatarSave');

/*
 * Remove Avatar
 */
function mbtAvatarRemove()
{
	check_ajax_referer('mbt_avatar_nonce', 'security');
	if (!is_user_logged_in()) {
		wp_send_json_error([
			'message' => esc_html__('You must be logged in to remove avatars.', 'mbt-avatar-uploader'),
		]);
	}

	$user_id			= get_current_user_id();
	$old_attach_id		= get_user_meta($user_id, 'mbt_user_avatar_attachment_id', true);
	if ($old_attach_id > 0) {
		wp_delete_attachment($old_attach_id);
		delete_user_meta($user_id, 'mbt_user_avatar_attachment_id');
	}

	$url = MBT_AU_PLUGIN_URL . '/assets/images/mbt-default-user-avatar.png';

	wp_send_json_success(
		array(
			'avatar_url'	=> $url,
			'message'		=> esc_html__('Avatar removed successfully', 'mbt-avatar-uploader'),
		)
	);
}
add_action('wp_ajax_nopriv_mbt_avatar_remove', 'mbtAvatarRemove');
add_action('wp_ajax_mbt_avatar_remove', 'mbtAvatarRemove');
