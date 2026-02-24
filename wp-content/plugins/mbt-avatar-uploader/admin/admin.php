<?php
/**
 * MBT Avatar Uploader – admin settings page.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ---------------------------------------------------------------------------
// Register the submenu under Users.
// ---------------------------------------------------------------------------
add_action( 'admin_menu', 'mbt_avatar_uploader_menu' );
function mbt_avatar_uploader_menu(): void {
    // Let WordPress itself guard the page with the capability – no manual role check needed.
    add_submenu_page(
        'users.php',
        esc_html__( 'MBT Avatar Uploader', 'mbt-avatar-uploader' ),
        esc_html__( 'Avatar Uploader', 'mbt-avatar-uploader' ),
        'manage_options',
        'mbt-avatar-uploader',
        'mbt_avatar_uploader_settings_page'
    );
}

// ---------------------------------------------------------------------------
// Render the settings page.
// ---------------------------------------------------------------------------
function mbt_avatar_uploader_settings_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to access this page.', 'mbt-avatar-uploader' ) );
    }
    ?>
    <div class="wrap mbt-au-settings-wrap">
        <h1><?php esc_html_e( 'MBT Avatar Uploader', 'mbt-avatar-uploader' ); ?></h1>

        <div class="mbt-au-info-box">
            <span class="mbt-au-info-text">
                <?php
                printf(
                    /* translators: %s: shortcode tag */
                    esc_html__( 'Use %s shortcode to display the avatar upload field.', 'mbt-avatar-uploader' ),
                    '<strong>[MBT_Avatar_Field]</strong>'
                );
                ?>
            </span>
            <span class="mbt-au-info-text">
                <?php
                printf(
                    /* translators: %s: shortcode tag */
                    esc_html__( 'Use %s shortcode to display the user avatar.', 'mbt-avatar-uploader' ),
                    '<strong>[MBT_Avatar]</strong>'
                );
                ?>
            </span>
        </div>

        <form action="options.php" method="post">
            <?php
            settings_fields( 'mbt_ua_setting' );
            do_settings_sections( 'mbt_ua_setting' );
            submit_button( esc_html__( 'Save Settings', 'mbt-avatar-uploader' ) );
            ?>
        </form>
    </div>
    <?php
}

// ---------------------------------------------------------------------------
// Register settings, sections and fields.
// ---------------------------------------------------------------------------
add_action( 'admin_init', 'mbt_au_settings' );
function mbt_au_settings(): void {
    register_setting(
        'mbt_ua_setting',
        'mbt_ua_setting',
        array(
            'sanitize_callback' => 'mbt_au_sanitize_settings',
        )
    );

    add_settings_section(
        'mbt_ua_setting_section',
        esc_html__( 'Settings', 'mbt-avatar-uploader' ),
        '__return_false',
        'mbt_ua_setting'
    );

    add_settings_field(
        'mbt_au_plugin_options_max_size',
        esc_html__( 'Max Avatar Size Allowed', 'mbt-avatar-uploader' ),
        'mbt_au_plugin_options_max_size_callback',
        'mbt_ua_setting',
        'mbt_ua_setting_section'
    );
    add_settings_field(
        'mbt_au_plugin_options_allowed_file_type',
        esc_html__( 'Allowed Avatar Type', 'mbt-avatar-uploader' ),
        'mbt_au_plugin_options_allowed_file_type_callback',
        'mbt_ua_setting',
        'mbt_ua_setting_section'
    );
    add_settings_field(
        'mbt_au_plugin_options_file_style',
        esc_html__( 'Avatar Style', 'mbt-avatar-uploader' ),
        'mbt_au_plugin_options_file_style_callback',
        'mbt_ua_setting',
        'mbt_ua_setting_section'
    );
    add_settings_field(
        'mbt_au_plugin_options_dimension',
        esc_html__( 'Avatar Dimension', 'mbt-avatar-uploader' ),
        'mbt_au_plugin_options_dimension_callback',
        'mbt_ua_setting',
        'mbt_ua_setting_section'
    );
}

/**
 * Sanitise the entire settings array on save.
 *
 * @param  mixed $input Raw POST input.
 * @return array        Sanitised settings.
 */
function mbt_au_sanitize_settings( $input ): array {
    $clean = array();

    // Max size (KB) – positive integer, min 1, max 10240.
    $clean['max_size'] = isset( $input['max_size'] )
        ? (string) min( 10240, max( 1, absint( $input['max_size'] ) ) )
        : '1024';

    // Allowed file types – whitelist only.
    $allowed_types = array( 'jpg', 'jpeg', 'png' );
    $clean['file_type'] = array();
    if ( isset( $input['file_type'] ) && is_array( $input['file_type'] ) ) {
        foreach ( $input['file_type'] as $k => $v ) {
            $k = sanitize_key( $k );
            $v = sanitize_key( $v );
            if ( in_array( $v, $allowed_types, true ) ) {
                $clean['file_type'][ $k ] = $v;
            }
        }
    }
    // Default to all types if nothing was checked.
    if ( empty( $clean['file_type'] ) ) {
        $clean['file_type'] = array( 'jpg' => 'jpg', 'jpeg' => 'jpeg', 'png' => 'png' );
    }

    // Avatar style – must be circle or square.
    $allowed_styles     = array( 'circle', 'square' );
    $clean['file_style'] = ( isset( $input['file_style'] ) && in_array( $input['file_style'], $allowed_styles, true ) )
        ? $input['file_style']
        : 'circle';

    // Dimensions – min 50, max 800.
    $dim_w = isset( $input['file_dimension']['w'] ) ? absint( $input['file_dimension']['w'] ) : 200;
    $dim_h = isset( $input['file_dimension']['h'] ) ? absint( $input['file_dimension']['h'] ) : 200;
    $clean['file_dimension'] = array(
        'w' => (string) min( 800, max( 50, $dim_w ) ),
        'h' => (string) min( 800, max( 50, $dim_h ) ),
    );

    return $clean;
}

// ---------------------------------------------------------------------------
// Field renderers.
// ---------------------------------------------------------------------------

/** Max size field. */
function mbt_au_plugin_options_max_size_callback(): void {
    $options  = get_option( 'mbt_ua_setting', array() );
    $max_size = isset( $options['max_size'] ) ? absint( $options['max_size'] ) : 1024;

    printf(
        '<input name="mbt_ua_setting[max_size]" type="number" min="1" max="10240" value="%s" class="mbt-au-avatar-setting-field" /> <span>KB</span>',
        esc_attr( (string) $max_size )
    );
}

/** Allowed file types field. */
function mbt_au_plugin_options_allowed_file_type_callback(): void {
    $options       = get_option( 'mbt_ua_setting', array() );
    $saved_types   = isset( $options['file_type'] ) && is_array( $options['file_type'] ) ? $options['file_type'] : array();
    $file_type_arr = array( 'jpg' => 'jpg', 'jpeg' => 'jpeg', 'png' => 'png' );

    foreach ( $file_type_arr as $key => $value ) {
        $is_checked = array_key_exists( $key, $saved_types ) && $saved_types[ $key ] === $value;
        printf(
            '<label><input type="checkbox" name="mbt_ua_setting[file_type][%s]" value="%s"%s /> %s</label> &nbsp;&nbsp;&nbsp;&nbsp;',
            esc_attr( $key ),
            esc_attr( $value ),
            checked( true, $is_checked, false ),
            esc_html( strtoupper( $value ) )
        );
    }
}

/** Avatar style field. */
function mbt_au_plugin_options_file_style_callback(): void {
    $options        = get_option( 'mbt_ua_setting', array() );
    $current_style  = isset( $options['file_style'] ) ? $options['file_style'] : 'circle';
    $file_style_arr = array(
        'circle' => esc_html__( 'Circle', 'mbt-avatar-uploader' ),
        'square' => esc_html__( 'Square', 'mbt-avatar-uploader' ),
    );

    foreach ( $file_style_arr as $key => $label ) {
        printf(
            '<label><input type="radio" name="mbt_ua_setting[file_style]" value="%s"%s /> %s</label> &nbsp;&nbsp;&nbsp;&nbsp;',
            esc_attr( $key ),
            checked( $key, $current_style, false ),
            esc_html( $label )
        );
    }
}

/** Dimension field. */
function mbt_au_plugin_options_dimension_callback(): void {
    $options  = get_option( 'mbt_ua_setting', array() );
    $dim_w    = isset( $options['file_dimension']['w'] ) ? absint( $options['file_dimension']['w'] ) : 200;
    $dim_h    = isset( $options['file_dimension']['h'] ) ? absint( $options['file_dimension']['h'] ) : 200;

    echo esc_html__( 'Width', 'mbt-avatar-uploader' );
    printf(
        ': <input name="mbt_ua_setting[file_dimension][w]" type="number" min="50" max="800" value="%s" class="mbt-au-avatar-setting-field" /> <span>px</span> &nbsp;&nbsp;&nbsp;&nbsp;',
        esc_attr( (string) $dim_w )
    );

    echo esc_html__( 'Height', 'mbt-avatar-uploader' );
    printf(
        ': <input name="mbt_ua_setting[file_dimension][h]" type="number" min="50" max="800" value="%s" class="mbt-au-avatar-setting-field" /> <span>px</span>',
        esc_attr( (string) $dim_h )
    );
}