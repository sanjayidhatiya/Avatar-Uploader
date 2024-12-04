<?php
/**
 * Load JS and CSS file from the WordPress admin
 */
function mbt_au_admin_custom_css($hook){
    // Load Custom CSS
    wp_enqueue_style('mbt-au-style', plugins_url('/assets/css/admin/style.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'mbt_au_admin_custom_css');


/**
 * Load JS and CSS file from the WordPress website
 */
function mbt_au_frontend_custom_scripts() {
    // Load Custom CSS
    wp_enqueue_style('mbt-au-croppie-style', plugins_url('/assets/croppie/croppie.css', __FILE__));
    wp_enqueue_style('mbt-au-style', plugins_url('/assets/css/style.css', __FILE__));
    // Load Custom JS
    wp_enqueue_script( 'mbt-au-jquery', 'https://code.jquery.com/jquery-3.6.4.min.js');
    wp_enqueue_script( 'mbt-au-croppie-script', plugins_url('/assets/croppie/croppie.js', __FILE__), array(), false);
    
    wp_enqueue_script( 'mbt-au-frontend-config', plugins_url('/assets/js/script.js', __FILE__), array(), false);
    
    // Admin Ajax URL Call 
    wp_localize_script('mbt-au-frontend-config', 'mbt_au_frontend_object',
        array( 
            'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
            'mbt_ua_setting'    => get_option( 'mbt_ua_setting' ),
            'mbt_ua_btn_text'   => esc_html__( 'Upload Avatar', 'mbt-avatar-uploader' ),
            'mbt_ca_btn_text'       => esc_html__( 'Change Avatar', 'mbt-avatar-uploader' ),
            'mbt_ff_error_text' => esc_html__( 'Only formats are allowed', 'mbt-avatar-uploader' ),
            'mbt_fs_error_text' => esc_html__( 'Please upload file less than', 'mbt-avatar-uploader' ),
        )
    );
}
add_action('wp_enqueue_scripts', 'mbt_au_frontend_custom_scripts');

/**
 * Retrun MBT avatar URL in get_avatar_url wordpress function
 */
add_filter( 'get_avatar_url', 'mbt_au_filter_get_avatar_url', 10, 3 );
function mbt_au_filter_get_avatar_url( $url, $id_or_email, $args ) {
    // Check if MBT avatar is uploaded
    if ( is_numeric($id_or_email) ) {
        $user_ID = $id_or_email;
        $attach_id = get_user_meta( $user_ID, 'mbt_user_avatar_attachment_id', true); 
        if($attach_id>0) {
            $avatar_url = wp_get_attachment_url( $attach_id );
            if ( !empty( $avatar_url ) ) {
                $url = $avatar_url;
            }
        }
    }
    // Return the original avatar URL for other cases
    return $url;
}