<?php
/**
 * MBT Avatar Uploader – AJAX handlers (save & remove).
 *
 * Both actions require:
 *  - The user to be logged in.
 *  - A valid nonce (mbt_au_nonce).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ---------------------------------------------------------------------------
// Save / upload avatar.
// Only registered for logged-in users (wp_ajax_*); nopriv removed.
// ---------------------------------------------------------------------------
add_action( 'wp_ajax_mbt_avatar_save', 'mbt_avatar_save' );
function mbt_avatar_save(): void {
    // 1. Authentication.
    if ( ! is_user_logged_in() ) {
        wp_send_json_error(
            array( 'message' => esc_html__( 'You must be logged in to upload an avatar.', 'mbt-avatar-uploader' ) ),
            403
        );
    }

    // 2. Nonce verification.
    check_ajax_referer( 'mbt_au_nonce', 'nonce' );

    $user_id = (int) get_current_user_id();

    // 3. Validate payload.
    if ( empty( $_POST['user_avatar'] ) ) {
        wp_send_json_error(
            array( 'message' => esc_html__( 'No image data received.', 'mbt-avatar-uploader' ) ),
            400
        );
    }

    // 4. Retrieve and validate settings.
    $settings    = get_option( 'mbt_ua_setting', array() );
    $max_size_kb = isset( $settings['max_size'] ) ? absint( $settings['max_size'] ) : 1024;

    // 5. Decode base64 image (croppie always outputs a data URI).
    $raw          = wp_unslash( $_POST['user_avatar'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    $base64_match = preg_match( '/^data:(image\/(?:png|jpeg|jpg));base64,(.+)$/s', $raw, $parts );

    if ( ! $base64_match ) {
        wp_send_json_error(
            array( 'message' => esc_html__( 'Invalid image data format.', 'mbt-avatar-uploader' ) ),
            400
        );
    }

    $decoded = base64_decode( $parts[2], true ); // strict mode
    if ( false === $decoded || strlen( $decoded ) < 8 ) {
        wp_send_json_error(
            array( 'message' => esc_html__( 'Could not decode image data.', 'mbt-avatar-uploader' ) ),
            400
        );
    }

    // 6. Server-side size check.
    $size_kb = (int) ( strlen( $decoded ) / 1024 );
    if ( $max_size_kb > 0 && $size_kb > $max_size_kb ) {
        wp_send_json_error(
            array(
                'message' => sprintf(
                    /* translators: %s: max file size */
                    esc_html__( 'File size exceeds the maximum allowed size of %s KB.', 'mbt-avatar-uploader' ),
                    esc_html( (string) $max_size_kb )
                ),
            ),
            400
        );
    }

    // 7. Write to a temp file and validate it's a real image.
    $tmp_file = wp_tempnam( 'mbt_avatar_' );
    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
    if ( false === file_put_contents( $tmp_file, $decoded ) ) {
        wp_send_json_error(
            array( 'message' => esc_html__( 'Could not create temporary file.', 'mbt-avatar-uploader' ) ),
            500
        );
    }

    // getimagesize works on the file; it confirms the binary is a real image.
    $image_info = @getimagesize( $tmp_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
    if ( false === $image_info ) {
        @unlink( $tmp_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
        wp_send_json_error(
            array( 'message' => esc_html__( 'Uploaded file is not a valid image.', 'mbt-avatar-uploader' ) ),
            400
        );
    }

    // 8. MIME-type whitelist.
    $allowed_mimes = array( 'image/png', 'image/jpeg' );
    if ( ! in_array( $image_info['mime'], $allowed_mimes, true ) ) {
        @unlink( $tmp_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
        wp_send_json_error(
            array( 'message' => esc_html__( 'Image type not allowed. Only JPG and PNG are accepted.', 'mbt-avatar-uploader' ) ),
            400
        );
    }

    // 9. Prepare upload directory.
    $upload_dir  = wp_upload_dir();
    $upload_path = trailingslashit( $upload_dir['basedir'] ) . 'mbt_user_avatars';
    $upload_url  = trailingslashit( $upload_dir['baseurl'] ) . 'mbt_user_avatars';

    if ( ! is_dir( $upload_path ) ) {
        wp_mkdir_p( $upload_path );
    }

    // Place / update .htaccess protection on every upload.
    $htaccess = $upload_path . '/.htaccess';
    if ( ! file_exists( $htaccess ) ) {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        file_put_contents( $htaccess, "Options -Indexes\n<Files *.php>\nDeny from all\n</Files>\n" );
    }

    if ( ! is_writable( $upload_path ) ) {
        @unlink( $tmp_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
        wp_send_json_error(
            array( 'message' => esc_html__( 'Upload directory is not writable. Please contact the site administrator.', 'mbt-avatar-uploader' ) ),
            500
        );
    }

    // 10. Remove the old attachment before saving the new one.
    $old_attach_id = (int) get_user_meta( $user_id, 'mbt_user_avatar_attachment_id', true );
    if ( $old_attach_id > 0 ) {
        wp_delete_attachment( $old_attach_id, true );
        delete_user_meta( $user_id, 'mbt_user_avatar_attachment_id' );
    }

    // 11. Move temp file to final destination.
    $ext      = ( 'image/png' === $image_info['mime'] ) ? 'png' : 'jpg';
    $filename = absint( $user_id ) . '-' . time() . '.' . $ext;
    $filepath = $upload_path . '/' . $filename;

    // phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
    if ( ! rename( $tmp_file, $filepath ) ) {
        // Fallback: copy + delete.
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_copy
        if ( ! copy( $tmp_file, $filepath ) ) {
            @unlink( $tmp_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
            wp_send_json_error(
                array( 'message' => esc_html__( 'Failed to save image file.', 'mbt-avatar-uploader' ) ),
                500
            );
        }
        @unlink( $tmp_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
    }

    // 12. Insert as a WP attachment and generate metadata.
    $attachment = array(
        'post_mime_type' => $image_info['mime'],
        'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
        'post_content'   => '',
        'post_status'    => 'inherit',
        'guid'           => $upload_url . '/' . $filename,
    );

    $attach_id = wp_insert_attachment( $attachment, $filepath );
    if ( is_wp_error( $attach_id ) ) {
        @unlink( $filepath ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
        wp_send_json_error(
            array( 'message' => $attach_id->get_error_message() ),
            500
        );
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $filepath ) );

    // 13. Persist attachment ID in user meta.
    update_user_meta( $user_id, 'mbt_user_avatar_attachment_id', $attach_id );

    $url = wp_get_attachment_url( $attach_id );
    if ( empty( $url ) ) {
        $url = MBT_AU_PLUGIN_URL . '/assets/images/mbt-default-user-avatar.png';
    }

    wp_send_json_success(
        array(
            'attachment_id' => $attach_id,
            'avatar_url'    => esc_url_raw( $url ),
            'message'       => esc_html__( 'Avatar updated successfully.', 'mbt-avatar-uploader' ),
        )
    );
}

// ---------------------------------------------------------------------------
// Remove avatar.
// Only registered for logged-in users; nopriv removed.
// ---------------------------------------------------------------------------
add_action( 'wp_ajax_mbt_avatar_remove', 'mbt_avatar_remove' );
function mbt_avatar_remove(): void {
    // 1. Authentication.
    if ( ! is_user_logged_in() ) {
        wp_send_json_error(
            array( 'message' => esc_html__( 'You must be logged in to remove your avatar.', 'mbt-avatar-uploader' ) ),
            403
        );
    }

    // 2. Nonce verification.
    check_ajax_referer( 'mbt_au_nonce', 'nonce' );

    $user_id       = (int) get_current_user_id();
    $old_attach_id = (int) get_user_meta( $user_id, 'mbt_user_avatar_attachment_id', true );

    if ( $old_attach_id > 0 ) {
        wp_delete_attachment( $old_attach_id, true );
        delete_user_meta( $user_id, 'mbt_user_avatar_attachment_id' );
    }

    $url = MBT_AU_PLUGIN_URL . '/assets/images/mbt-default-user-avatar.png';

    wp_send_json_success(
        array(
            'avatar_url' => esc_url_raw( $url ),
            'message'    => esc_html__( 'Avatar removed successfully.', 'mbt-avatar-uploader' ),
        )
    );
}