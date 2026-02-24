<?php
/**
 * MBT Avatar Uploader – Uninstall script.
 *
 * Runs when the plugin is deleted via the WordPress admin.
 * Removes all plugin options and user meta, then cleans up the upload folder.
 */

// Block direct access and non-uninstall calls.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// ---------------------------------------------------------------------------
// 1. Delete plugin option.
// ---------------------------------------------------------------------------
delete_option( 'mbt_ua_setting' );

// On multisite, delete from every site.
if ( is_multisite() ) {
    $sites = get_sites( array( 'number' => 0 ) );
    foreach ( $sites as $site ) {
        switch_to_blog( (int) $site->blog_id );
        delete_option( 'mbt_ua_setting' );
        restore_current_blog();
    }
}

// ---------------------------------------------------------------------------
// 2. Delete user meta (attachment IDs) from all users.
// ---------------------------------------------------------------------------
$users_with_avatar = get_users(
    array(
        'meta_key'    => 'mbt_user_avatar_attachment_id',
        'meta_compare' => 'EXISTS',
        'fields'      => 'ID',
        'number'      => -1,
    )
);

foreach ( $users_with_avatar as $user_id ) {
    $attach_id = (int) get_user_meta( (int) $user_id, 'mbt_user_avatar_attachment_id', true );
    if ( $attach_id > 0 ) {
        // Delete the media file from the Media Library.
        wp_delete_attachment( $attach_id, true );
    }
    delete_user_meta( (int) $user_id, 'mbt_user_avatar_attachment_id' );
}

// ---------------------------------------------------------------------------
// 3. Remove the mbt_user_avatars upload folder and its contents.
// ---------------------------------------------------------------------------
$upload_dir = wp_upload_dir();
$avatar_dir = trailingslashit( $upload_dir['basedir'] ) . 'mbt_user_avatars';

if ( is_dir( $avatar_dir ) ) {
    mbt_au_rrmdir( $avatar_dir );
}

/**
 * Recursively delete a directory and all its contents.
 *
 * @param string $dir Absolute path to directory.
 */
function mbt_au_rrmdir( string $dir ): void {
    if ( ! is_dir( $dir ) ) {
        return;
    }
    $items = scandir( $dir );
    if ( ! $items ) {
        return;
    }
    foreach ( $items as $item ) {
        if ( '.' === $item || '..' === $item ) {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if ( is_dir( $path ) ) {
            mbt_au_rrmdir( $path );
        } else {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
            unlink( $path );
        }
    }
    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
    rmdir( $dir );
}
