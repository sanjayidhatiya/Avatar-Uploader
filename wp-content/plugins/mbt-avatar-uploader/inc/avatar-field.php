<?php
/**
* Display Avatar Field Form on Frontend Side
**/
function avatar_field(){
    ob_start();
        require_once MBT_AU_PLUGIN_DIR . '/form/avatar-field.php';    
    return ob_get_clean();
}
add_shortcode('MBT_Avatar_Field', 'avatar_field');
/**
* Display Avatar Photo on Frontend Side
**/
function avatar_photo(){
    ob_start();
        $user_ID = get_current_user_id();
        if($user_ID > 0) {
            $attach_id = get_user_meta( $user_ID, 'mbt_user_avatar_attachment_id', true); 
            if($attach_id>0) {
                $avatar_url = wp_get_attachment_url( $attach_id );
                if ( empty( $avatar_url ) ) {
                    $avatar_url = MBT_AU_PLUGIN_URL . '/assets/images/mbt-default-user-avatar.png';
                }
                echo '<img id="mbt_au_avatar" src="'. $avatar_url .'">';
            } else {
                $no_avatar_msg = esc_html__( 'No Avatar Available', 'mbt-avatar-uploader' );
                echo '<p>'.$no_avatar_msg.'</p>';
            }
        }    
    return ob_get_clean();
}
add_shortcode('MBT_Avatar', 'avatar_photo');