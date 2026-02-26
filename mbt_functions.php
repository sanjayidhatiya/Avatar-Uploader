<?php

/**
 * Load JS and CSS file from the WordPress admin
 */
function mbtAuAdminCustomCss($hook)
{

    if (!in_array($hook, ['users_page_mbt-avatar-uploader', 'profile.php', 'user-edit.php'])) {
        return;
    }
    
    // Styles
    wp_enqueue_style('mbt-au-style', plugins_url('/assets/css/admin/style.css', __FILE__));
    wp_enqueue_style('mbt-au-croppie-style', plugins_url('/assets/croppie/croppie.css', __FILE__));

    // Scripts
    wp_enqueue_script('jquery');
    wp_enqueue_script('mbt-au-croppie-script', plugins_url('/assets/croppie/croppie.js', __FILE__), ['jquery'], null, true);
    wp_enqueue_script('mbt-au-script', plugins_url('/assets/js/script.js', __FILE__), ['jquery'], null, true);

    // Data
    wp_localize_script('mbt-au-script', 'mbt_au_frontend_object', array(
        'ajaxUrl'           => admin_url('admin-ajax.php'),
        'nonce'             => wp_create_nonce('mbt_avatar_nonce'),
        'mbt_ua_setting'    => get_option('mbt_ua_setting'),
        'mbt_ua_btn_text'   => esc_html__('Upload Avatar', 'mbt-avatar-uploader'),
        'mbt_ca_btn_text'   => esc_html__('Change Avatar', 'mbt-avatar-uploader'),
        'mbt_ff_error_text' => esc_html__('Only formats are allowed', 'mbt-avatar-uploader'),
        'mbt_fs_error_text' => esc_html__('Please upload file less than', 'mbt-avatar-uploader'),
    ));
}
add_action('admin_enqueue_scripts', 'mbtAuAdminCustomCss');


/**
 * Load JS and CSS file from the WordPress website
 */
function mbtAuFrontendCustomScripts()
{
    if (!is_page('user-profile') && !has_shortcode(get_post()->post_content, 'MBT_Avatar_Field')) {
        return;
    }

    // Load Custom CSS
    wp_enqueue_style('mbt-au-croppie-style', plugins_url('/assets/croppie/croppie.css', __FILE__));
    wp_enqueue_style('mbt-au-style', plugins_url('/assets/css/style.css', __FILE__));

    // Load Custom JS
    wp_enqueue_script('jquery');
    wp_enqueue_script('mbt-au-croppie-script', plugins_url('/assets/croppie/croppie.js', __FILE__), array(), false);
    wp_enqueue_script('mbt-au-frontend-config', plugins_url('/assets/js/script.js', __FILE__), array(), false);

    // Admin Ajax URL Call
    wp_localize_script(
        'mbt-au-frontend-config',
        'mbt_au_frontend_object',
        array(
            'ajaxUrl'           => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('mbt_avatar_nonce'),
            'mbt_ua_setting'    => get_option('mbt_ua_setting'),
            'mbt_ua_btn_text'   => esc_html__('Upload Avatar', 'mbt-avatar-uploader'),
            'mbt_ca_btn_text'       => esc_html__('Change Avatar', 'mbt-avatar-uploader'),
            'mbt_ff_error_text' => esc_html__('Only formats are allowed', 'mbt-avatar-uploader'),
            'mbt_fs_error_text' => esc_html__('Please upload file less than', 'mbt-avatar-uploader'),
        )
    );
}
add_action('wp_enqueue_scripts', 'mbtAuFrontendCustomScripts');

/**
 * Retrun MBT avatar URL in get_avatar_url wordpress function
 */
add_filter('get_avatar_url', 'mbtAuFilterGetAvatarUrl', 10, 3);
function mbtAuFilterGetAvatarUrl($url, $id_or_email)
{
    // Check if MBT avatar is uploaded
    if (is_numeric($id_or_email)) {
        $user_ID = $id_or_email;
        $attach_id = get_user_meta($user_ID, 'mbt_user_avatar_attachment_id', true);
        if ($attach_id > 0) {
            $avatar_url = wp_get_attachment_url($attach_id);
            if (!empty($avatar_url)) {
                $url = $avatar_url;
            }
        }
    }
    // Return the original avatar URL for other cases
    return $url;
}

add_filter('get_avatar', 'mbtAuOverrideAvatar', 10, 4);
function mbtAuOverrideAvatar($avatar, $idOrEmail, $size, $alt) {
    $user = false;

    if (is_numeric($idOrEmail)) {
        $user = get_user_by('id', (int) $idOrEmail);
    } elseif (is_object($idOrEmail)) {
        if (!empty($idOrEmail->user_id)) {
            $user = get_user_by('id', (int) $idOrEmail->user_id);
        }
    } else {
        $user = get_user_by('email', $idOrEmail);
    }

    if ($user) {
        $attach_id = get_user_meta($user->ID, 'mbt_user_avatar_attachment_id', true);
        if ($attach_id) {
            $avatar_url = wp_get_attachment_url($attach_id);
            if (!empty($avatar_url)) {
                $avatar = "<img alt='" . esc_attr($alt) . "' src='" . esc_url($avatar_url) . "' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
            }
        }
    }

    return $avatar;
}
