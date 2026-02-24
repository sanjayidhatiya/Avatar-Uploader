<?php
/**
 * MBT Avatar Uploader – helper functions (script/style enqueueing & avatar filter).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ---------------------------------------------------------------------------
// Admin: enqueue CSS only on the plugin's own settings page.
// ---------------------------------------------------------------------------
add_action( 'admin_enqueue_scripts', 'mbt_au_admin_custom_css' );
function mbt_au_admin_custom_css( string $hook ): void {
    if ( 'users_page_mbt-avatar-uploader' !== $hook ) {
        return;
    }
    wp_enqueue_style(
        'mbt-au-admin-style',
        plugins_url( '/assets/css/admin/style.css', __FILE__ ),
        array(),
        MBT_AU_VERSION
    );
}

// ---------------------------------------------------------------------------
// Front-end: always enqueue assets on any frontend page.
// The CSS/JS are lightweight and shortcodes can appear in widgets, builders,
// or loop pages where post-content scanning is unreliable.
// ---------------------------------------------------------------------------
add_action( 'wp_enqueue_scripts', 'mbt_au_frontend_register_scripts' );
function mbt_au_frontend_register_scripts(): void {
    // Styles.
    wp_enqueue_style(
        'mbt-au-croppie-style',
        plugins_url( '/assets/croppie/croppie.css', __FILE__ ),
        array(),
        MBT_AU_VERSION
    );
    wp_enqueue_style(
        'mbt-au-style',
        plugins_url( '/assets/css/style.css', __FILE__ ),
        array(),
        MBT_AU_VERSION
    );

    // Scripts (loaded in footer so shortcode output is already in the DOM).
    wp_enqueue_script(
        'mbt-au-croppie-script',
        plugins_url( '/assets/croppie/croppie.js', __FILE__ ),
        array( 'jquery' ),
        MBT_AU_VERSION,
        true
    );
    wp_enqueue_script(
        'mbt-au-frontend-config',
        plugins_url( '/assets/js/script.js', __FILE__ ),
        array( 'jquery', 'mbt-au-croppie-script' ),
        MBT_AU_VERSION,
        true
    );

    // Pass settings and nonce to JS.
    $raw_settings  = get_option( 'mbt_ua_setting', array() );
    $safe_settings = mbt_au_sanitize_settings_for_js( $raw_settings );

    wp_localize_script(
        'mbt-au-frontend-config',
        'mbt_au_frontend_object',
        array(
            'ajaxUrl'           => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
            'nonce'             => wp_create_nonce( 'mbt_au_nonce' ),
            'mbt_ua_setting'    => $safe_settings,
            'mbt_ua_btn_text'   => esc_html__( 'Upload Avatar', 'mbt-avatar-uploader' ),
            'mbt_ca_btn_text'   => esc_html__( 'Change Avatar', 'mbt-avatar-uploader' ),
            'mbt_ff_error_text' => esc_html__( 'Only the following formats are allowed', 'mbt-avatar-uploader' ),
            'mbt_fs_error_text' => esc_html__( 'Please upload a file smaller than', 'mbt-avatar-uploader' ),
        )
    );
}

/** No-op kept so nothing breaks if other code references this name. */
function mbt_au_enqueue_frontend_assets(): void {}

// ---------------------------------------------------------------------------
// Back-compat stub – do not remove.
// ---------------------------------------------------------------------------
add_action( 'wp_enqueue_scripts', 'mbt_au_frontend_custom_scripts' );
function mbt_au_frontend_custom_scripts(): void {}

/**
 * Return a JS-safe copy of the stored settings (no raw DB values sent directly).
 *
 * @param  array $raw Raw option value.
 * @return array
 */
function mbt_au_sanitize_settings_for_js( array $raw ): array {
    $allowed_types  = array( 'jpg', 'jpeg', 'png' );
    $allowed_styles = array( 'circle', 'square' );

    $file_type = array();
    if ( isset( $raw['file_type'] ) && is_array( $raw['file_type'] ) ) {
        foreach ( $raw['file_type'] as $k => $v ) {
            if ( in_array( $v, $allowed_types, true ) ) {
                $file_type[ sanitize_key( $k ) ] = $v;
            }
        }
    }

    $file_style = isset( $raw['file_style'] ) && in_array( $raw['file_style'], $allowed_styles, true )
        ? $raw['file_style']
        : 'circle';

    $dim_w = isset( $raw['file_dimension']['w'] ) ? absint( $raw['file_dimension']['w'] ) : 200;
    $dim_h = isset( $raw['file_dimension']['h'] ) ? absint( $raw['file_dimension']['h'] ) : 200;
    if ( $dim_w < 50 )  { $dim_w = 50; }
    if ( $dim_h < 50 )  { $dim_h = 50; }
    if ( $dim_w > 800 ) { $dim_w = 800; }
    if ( $dim_h > 800 ) { $dim_h = 800; }

    return array(
        'max_size'       => absint( $raw['max_size'] ?? 1024 ),
        'file_type'      => $file_type,
        'file_style'     => $file_style,
        'file_dimension' => array( 'w' => $dim_w, 'h' => $dim_h ),
    );
}

// ---------------------------------------------------------------------------
// Hook into get_avatar_url so standard WP calls use the MBT avatar.
// ---------------------------------------------------------------------------
add_filter( 'get_avatar_url', 'mbt_au_filter_get_avatar_url', 10, 3 );
function mbt_au_filter_get_avatar_url( string $url, $id_or_email, array $args ): string {
    $user_id = 0;

    if ( is_numeric( $id_or_email ) ) {
        $user_id = (int) $id_or_email;
    } elseif ( $id_or_email instanceof WP_User ) {
        $user_id = (int) $id_or_email->ID;
    } elseif ( $id_or_email instanceof WP_Post ) {
        $user_id = (int) $id_or_email->post_author;
    } elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
        $user = get_user_by( 'email', $id_or_email );
        if ( $user ) {
            $user_id = (int) $user->ID;
        }
    }

    if ( $user_id > 0 ) {
        $attach_id = (int) get_user_meta( $user_id, 'mbt_user_avatar_attachment_id', true );
        if ( $attach_id > 0 ) {
            $avatar_url = wp_get_attachment_url( $attach_id );
            if ( ! empty( $avatar_url ) ) {
                return $avatar_url;
            }
        }
    }

    return $url;
}