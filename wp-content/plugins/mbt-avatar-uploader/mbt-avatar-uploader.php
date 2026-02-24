<?php
/**
 * Plugin Name: MBT Avatar Uploader
 * Plugin URI:  https://www.mindboxtechnologies.com/plugins/avatar-uploader
 * Description: Easily add and edit profile pictures with an avatar upload field on your website's frontend pages and Edit Profile screen. Empower users to personalize their profiles with custom profile pictures in just a few clicks.
 * Version:     1.1.0
 * Author:      Mindbox Technologies
 * Author URI:  https://mindboxtechnologies.com
 * Text Domain: mbt-avatar-uploader
 * Domain Path: /languages
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Copyright 2024 Mindbox Technologies ( email : info@mindboxtechnologies.com )
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct file access.
}

define( 'MBT_AU_VERSION',        '1.1.0' );
define( 'MBT_AU_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'MBT_AU_PLUGIN_DIR',      untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'MBT_AU_PLUGIN_URL',      untrailingslashit( plugins_url( '', __FILE__ ) ) );

/**
 * Activation hook – set default options and create upload directory.
 */
register_activation_hook( __FILE__, 'mbt_au_activation_actions' );
function mbt_au_activation_actions() {
    mbt_au_default_options();
    do_action( 'mbt_au_extension_activation' );
}

function mbt_au_default_options() {
    // Only add if the option does not already exist.
    if ( ! get_option( 'mbt_ua_setting' ) ) {
        $default = array(
            'max_size'       => '1024',
            'file_type'      => array( 'jpg' => 'jpg', 'jpeg' => 'jpeg', 'png' => 'png' ),
            'file_style'     => 'circle',
            'file_dimension' => array( 'w' => '200', 'h' => '200' ),
        );
        add_option( 'mbt_ua_setting', $default );
    }

    // Create the avatar upload directory via WP helper (handles permissions properly).
    $path = wp_upload_dir()['basedir'] . '/mbt_user_avatars';
    if ( ! is_dir( $path ) ) {
        wp_mkdir_p( $path );
    }

    // Deny direct PHP execution inside the upload folder.
    $htaccess = $path . '/.htaccess';
    if ( ! file_exists( $htaccess ) ) {
        file_put_contents( $htaccess, "Options -Indexes\n<Files *.php>\nDeny from all\n</Files>\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
    }
}

/**
 * Settings link on the Plugins list page.
 */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'mbt_au_settings_link' );
function mbt_au_settings_link( array $links ): array {
    $url           = esc_url( admin_url( 'users.php?page=mbt-avatar-uploader' ) );
    $settings_link = '<a href="' . $url . '">' . esc_html__( 'Settings', 'mbt-avatar-uploader' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}

/**
 * Load text domain for translations.
 */
add_action( 'init', 'mbt_avatar_uploader_init' );
function mbt_avatar_uploader_init() {
    load_plugin_textdomain( 'mbt-avatar-uploader', false, dirname( MBT_AU_PLUGIN_BASENAME ) . '/languages' );
}

/**
 * Load admin UI.
 */
if ( is_admin() ) {
    require_once MBT_AU_PLUGIN_DIR . '/admin/admin.php';
}

/**
 * Load front-end helpers (script enqueueing, avatar filter).
 */
require_once MBT_AU_PLUGIN_DIR . '/mbt_functions.php';

/**
 * Shortcodes: [MBT_Avatar_Field] and [MBT_Avatar]
 */
require_once MBT_AU_PLUGIN_DIR . '/inc/avatar-field.php';

/**
 * AJAX handlers for save / remove.
 */
require_once MBT_AU_PLUGIN_DIR . '/inc/ajax-avatar-save.php';