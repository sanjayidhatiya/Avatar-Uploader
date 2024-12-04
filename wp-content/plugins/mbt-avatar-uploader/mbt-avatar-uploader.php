<?php
/**
 * Plugin Name: MBT Avatar Uploader
 * Plugin URI: https://www.mindboxtechnologies.com/plugins/avatar-uploader
 * Description: Easily add and edit profile pictures with an avatar upload field on your website's frontend pages and Edit Profile screen. Empower users to personalize their profiles with custom profile pictures in just a few clicks.
 * Version: 1.0.0
 * Author: Mindbox Technologies
 * Author URI: https://mindboxtechnologies.com
 * Text Domain: mbt-avatar-uploader
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Copyright 2023 Mindbox Technologies ( email : info@mindboxtechnologies.com )
 */

define('MBT_AU_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('MBT_AU_PLUGIN_DIR', untrailingslashit( dirname(__FILE__)));
define('MBT_AU_PLUGIN_URL', untrailingslashit(plugins_url('', __FILE__)));
define('MBT_AU_CONTENT_URL', content_url());
define('MBT_AU_HOME_URL', home_url());

/**
* Add Default Settings
**/

function mbt_au_activation_actions(){
    do_action( 'mbt_au_extension_activation' );
}
register_activation_hook( __FILE__, 'mbt_au_activation_actions' );

function mbt_au_default_options(){
    $default = array(
        'max_size'          => '1024',
        'file_type'         => array('jpg'=>'jpg', 'jpeg'=>'jpeg', 'png'=>'png'),
        'file_style'        => 'circle',
        'file_dimension'    => array('w'=>'200','h'=>'200'),
    );
    update_option( 'mbt_ua_setting', $default );

    // Create a folder to store avatars if not present.
    $path = WP_CONTENT_DIR . '/uploads/mbt_user_avatars';
    if ( ! is_dir( $path ) ) {
        mkdir( $path, 0777, true );
    }
}
add_action( 'mbt_au_extension_activation', 'mbt_au_default_options' );

/**
* Add Setting Link
**/
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'mbt_au_settings_link');
function mbt_au_settings_link( array $links ) {
    $url            = get_admin_url() . "users.php?page=mbt-avatar-uploader";
    $settings_link  = '<a href="' . $url . '">' . esc_html__('Settings', 'mbt-avatar-uploader') . '</a>';
    $links[]        = $settings_link;
    return $links;
}

/**
* Translation Init
**/
function mbt_avatar_uploader_init() {
    load_plugin_textdomain( 'mbt-avatar-uploader', false, 'mbt-avatar-uploader/languages' );
}
add_action('init', 'mbt_avatar_uploader_init');

/**
* Load admin function
**/
if(is_admin()){
    require_once MBT_AU_PLUGIN_DIR. '/admin/admin.php';
}
/**
* Load custom function
**/
require_once MBT_AU_PLUGIN_DIR. '/mbt_functions.php';
/**
* Display Avatar Field Form via Short Code [MBT_Avatar_Field]
* Display Avatar Fieled via Short Code [MBT_Avatar]
**/
require_once MBT_AU_PLUGIN_DIR. '/inc/avatar-field.php';
/**
* Save Avatar via Avatar Field
**/
require_once MBT_AU_PLUGIN_DIR. '/inc/ajax-avatar-save.php';