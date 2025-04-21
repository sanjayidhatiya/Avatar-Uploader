<div class="mbt-au-field-container">
    <?php $user_ID = get_current_user_id(); ?>
    <?php if($user_ID > 0) { ?>
        <?php
            $attach_id = get_user_meta( $user_ID, 'mbt_user_avatar_attachment_id', true);
            $af_class           = $avatar_url = "";
            $avatar_btn_text    = esc_html__( 'Upload Avatar', 'mbt-avatar-uploader' );
            if($attach_id>0) {
                $af_class   = "show";
                $avatar_url = wp_get_attachment_url( $attach_id );
                if ( empty( $avatar_url ) ) {
                    $avatar_url = MBT_AU_PLUGIN_URL . '/assets/images/mbt-default-user-avatar.png';
                }
                $avatar_btn_text    = esc_html__( 'Change Avatar', 'mbt-avatar-uploader' );
            }
        ?>
        <div class="mbt-au-field avatar-field-sec">
            
            <img class="sec-overlay-spinner" alt="" src="<?php echo MBT_AU_PLUGIN_URL; ?>/assets/images/spinner.gif">
            <div class="sec-overlay"></div>

            <div class="mbt-au-field avatar-field <?php echo $af_class; ?>">
                <img id="mbt_au_avatar" alt="" src="<?php echo $avatar_url; ?>">
            </div>
            <div class="mbt-au-field">
                <input class="file-field" type="file" id="mbt_avatar" />
                <button class="btn file-select-btn"><?php echo $avatar_btn_text; ?></button>
                <button class="btn file-remove-btn <?php echo $af_class; ?>"><?php esc_html_e( 'Remove', 'mbt-avatar-uploader' ); ?></button>
            </div>

        </div>

            
        <div class="mbt-au-field preview-field">
            
            <img class="ajax-overlay-spinner" alt="" src="<?php echo MBT_AU_PLUGIN_URL; ?>/assets/images/spinner.gif">
            <div class="ajax-overlay"></div>

            <div class="mbt-avatar-preview-wrap">
                <div id="mbt-avatar-preview">
                    
                </div>
            </div>
            
            <button class="btn" id="rotateLeft" data-deg="-90">
                <?php esc_html_e( 'Rotate Left', 'mbt-avatar-uploader' ); ?>
            </button>
            <button class="btn" id="rotateRight" data-deg="90">
                <?php esc_html_e( 'Rotate Right', 'mbt-avatar-uploader' ); ?>
            </button>
            <input class="btn mbt-avatar-submit" type="submit" name="submit" value="<?php esc_html_e( 'Upload', 'mbt-avatar-uploader' ); ?>">
        </div>
        <div class="mbt-au-message"></div>
    <?php } else { ?>
        <p><?php esc_html_e( 'You must log in first', 'mbt-avatar-uploader' ); ?></p>
    <?php } ?>
</div>
