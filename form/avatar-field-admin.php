<?php
$user_ID = get_current_user_id();
$attach_id = get_user_meta($user_ID, 'mbt_user_avatar_attachment_id', true);
$avatar_url = $attach_id ? wp_get_attachment_url($attach_id) : MBT_AU_PLUGIN_URL . '/assets/images/mbt-default-user-avatar.png';
?>

<div class="mbt-admin-avatar-wrap">
    <!-- Left section: Current Avatar + Actions -->
    <div class="mbt-avatar-left">
        <img src="<?php echo esc_url($avatar_url); ?>" id="mbt_au_avatar" alt="Avatar" />
        <br>
        <input type="file" class="file-field" id="mbt_avatar" name="mbt_avatar" accept="image/*" />
        <br>
        <button type="button" class="mbt-avatar-submit button button-primary"><?php esc_html_e('Upload', 'mbt-avatar-uploader'); ?></button>
        <button type="button" class="file-remove-btn button"><?php esc_html_e('Remove', 'mbt-avatar-uploader'); ?></button>
    </div>

    <!-- Right section: Cropping area -->
    <div class="mbt-avatar-right">
        <div class="preview-field" id="mbt-avatar-preview"></div>
        <div style="margin-top: 10px;">
            <button type="button" class="button" id="rotateLeft" data-deg="-90">⟲ Rotate Left</button>
            <button type="button" class="button" id="rotateRight" data-deg="90">⟳ Rotate Right</button>
        </div>
    </div>
</div>
