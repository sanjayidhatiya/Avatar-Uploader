<?php
function mbt_avatar_uploader_menu() {
    // Get user role:
    $user      = new WP_User(get_current_user_id());
    $user_role = $user->roles[0];
    // Check user role:
    if($user_role == 'administrator') {
        add_submenu_page( 
            'users.php',
            esc_html__( 'MBT Avatar Uploader', 'mbt-avatar-uploader' ),
            esc_html__( 'Avatar Uploader', 'mbt-avatar-uploader' ),
            'manage_options',
            'mbt-avatar-uploader',
            'mbt_avatar_uploader_settings_page',
        );
    }
}
add_action('admin_menu', 'mbt_avatar_uploader_menu');

function mbt_avatar_uploader_settings_page() {
	?>
    <h2><?php esc_html_e( 'MBT Avatar Uploader', 'mbt-avatar-uploader' ); ?></h2>
    <div class="mbt-au-info-box">
        <span class="mbt-au-info-text">
            <?php printf(__( 'Use %s Short Code to Display Avatar Uploader Field', 'mbt-avatar-uploader' ), '<strong>[MBT_Avatar_Field]</strong>'); ?>
        </span>
        <span class="mbt-au-info-text">
            <?php printf(__( 'Use %s Short Code to Display User Avatar', 'mbt-avatar-uploader' ), '<strong>[MBT_Avatar]</strong>'); ?>
        </span>
    </div>

    <form action="options.php" method="post">
        <?php 
        settings_fields( 'mbt_ua_setting' );
        do_settings_sections( 'mbt_ua_setting' ); 
        submit_button();
        ?>
    </form>
    <?php
}

function mbt_au_settings() {
    register_setting( 'mbt_ua_setting', 'mbt_ua_setting');
    
    add_settings_section( 
        'mbt_ua_setting_section', 
        esc_html__( 'Settings', 'mbt-avatar-uploader' ),
        '', 
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
add_action( 'admin_init', 'mbt_au_settings' );

/**
*  Max Avatar Size Setting
*/
function mbt_au_plugin_options_max_size_callback() {
    $options = get_option( 'mbt_ua_setting' );
    
    $max_size = '1024';
    if ( isset( $options['max_size'] ) ) {
        $max_size = esc_html( $options['max_size'] );
    }

    echo '<input name="mbt_ua_setting[max_size]" type="number" value="' . esc_attr( $max_size ) . '" class="mbt-au-avatar-setting-field" placeholder="" /> (Kb)';
}
/**
*  Avatar Type Setting
**/
function mbt_au_plugin_options_allowed_file_type_callback() {
    $options = get_option( 'mbt_ua_setting' );
    
    $file_type_arr = array('jpg'=>'jpg', 'jpeg'=>'jpeg', 'png'=>'png');
    foreach ($file_type_arr as $key => $value) {
        
        $is_checked = '';
        if ( isset( $options['file_type'] ) ) {
            $is_checked = esc_html( $options['file_type'][$key] == $value);
        }

        echo '<label><input type="checkbox"  name="mbt_ua_setting[file_type]['.$key.']" value="'.$value.'"' . checked( 1, $is_checked, false ) . '/> '.$value.'</label> &nbsp;&nbsp;&nbsp;&nbsp;';
    }
}
/**
*  Avatar Style Setting
**/
function mbt_au_plugin_options_file_style_callback() {
    $options = get_option( 'mbt_ua_setting' );
    
    $file_style_arr = array(
        'circle'    => esc_html__( 'Circle' ),
        'square'    => esc_html__( 'Square' )
    );
    foreach ($file_style_arr as $key => $value) {
        
        $is_checked = '';
        if ( isset( $options['file_style'] ) ) {
            $is_checked = esc_html( $options['file_style'] );
        }

        echo '<label><input type="radio"  name="mbt_ua_setting[file_style]" value="'.$key.'"' . checked( $key, $is_checked, false ) . '/> '.$value.'</label> &nbsp;&nbsp;&nbsp;&nbsp;';
    }
}
/**
*  Max Avatar Size Setting
*/
function mbt_au_plugin_options_dimension_callback() {
    $options = get_option( 'mbt_ua_setting' );
    
    $file_dimension_w = esc_html( $options['file_dimension']['w'] );
    $file_dimension_h = esc_html( $options['file_dimension']['h'] );
    
    esc_html_e( 'Width' );
    echo ': <input name="mbt_ua_setting[file_dimension][w]" type="number" value="' . esc_attr( $file_dimension_w ) . '" class="mbt-au-avatar-setting-field" placeholder="" /> (px) &nbsp;&nbsp;&nbsp;&nbsp;';
    
    esc_html_e( 'Height' );
    echo ': <input name="mbt_ua_setting[file_dimension][h]" type="number" value="' . esc_attr( $file_dimension_h ) . '" class="mbt-au-avatar-setting-field" placeholder="" /> (px)';
}
?>