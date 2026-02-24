<?php
/**
 * MBT Avatar Uploader – shortcode handlers.
 *
 * Shortcodes:
 *  [MBT_Avatar_Field]         – upload/crop form for the current user.
 *  [MBT_Avatar user_id="123"] – display avatar for any user (defaults to current user).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ---------------------------------------------------------------------------
// [MBT_Avatar_Field] – upload form.
// ---------------------------------------------------------------------------
add_shortcode( 'MBT_Avatar_Field', 'mbt_au_avatar_field_shortcode' );
function mbt_au_avatar_field_shortcode(): string {
    // Enqueue CSS/JS the moment this shortcode is rendered (works on any page type).
    mbt_au_enqueue_frontend_assets();

    ob_start();
    require MBT_AU_PLUGIN_DIR . '/form/avatar-field.php';
    return ob_get_clean();
}

// ---------------------------------------------------------------------------
// [MBT_Avatar user_id=""] – display avatar (optionally for a specific user).
// ---------------------------------------------------------------------------
add_shortcode( 'MBT_Avatar', 'mbt_au_avatar_photo_shortcode' );
function mbt_au_avatar_photo_shortcode( $atts ): string {
    // Enqueue the stylesheet so the avatar img gets styled correctly.
    mbt_au_enqueue_frontend_assets();

    $atts = shortcode_atts(
        array( 'user_id' => 0 ),
        $atts,
        'MBT_Avatar'
    );

    $user_id = (int) $atts['user_id'];
    if ( $user_id <= 0 ) {
        $user_id = (int) get_current_user_id();
    }

    if ( $user_id <= 0 ) {
        return '';
    }

    ob_start();

    $attach_id = (int) get_user_meta( $user_id, 'mbt_user_avatar_attachment_id', true );

    if ( $attach_id > 0 ) {
        $avatar_url = wp_get_attachment_url( $attach_id );
    }

    if ( empty( $avatar_url ) ) {
        $avatar_url = MBT_AU_PLUGIN_URL . '/assets/images/mbt-default-user-avatar.png';
    }

    $settings  = get_option( 'mbt_ua_setting', array() );
    $style     = isset( $settings['file_style'] ) && in_array( $settings['file_style'], array( 'circle', 'square' ), true )
        ? $settings['file_style']
        : 'circle';
    $dim_w     = isset( $settings['file_dimension']['w'] ) ? absint( $settings['file_dimension']['w'] ) : 200;
    $dim_h     = isset( $settings['file_dimension']['h'] ) ? absint( $settings['file_dimension']['h'] ) : 200;

    $user_info = get_userdata( $user_id );
    $alt_text  = $user_info ? esc_attr( sprintf(
        /* translators: %s: display name */
        __( '%s avatar', 'mbt-avatar-uploader' ),
        $user_info->display_name
    ) ) : esc_attr__( 'User avatar', 'mbt-avatar-uploader' );

    printf(
        '<img id="mbt_au_avatar" class="mbt-au-avatar mbt-au-avatar--%s" src="%s" width="%d" height="%d" alt="%s" loading="lazy" />',
        esc_attr( $style ),
        esc_url( $avatar_url ),
        $dim_w,
        $dim_h,
        $alt_text
    );

    return ob_get_clean();
}