<?php
/**
 * MBT Avatar Uploader – upload/crop form template.
 * Included via the [MBT_Avatar_Field] shortcode only.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$mbt_au_user_id = (int) get_current_user_id();
?>
<div class="mbt-au-field-container">
<?php if ( $mbt_au_user_id > 0 ) :

    $mbt_au_attach_id  = (int) get_user_meta( $mbt_au_user_id, 'mbt_user_avatar_attachment_id', true );
    $mbt_au_af_class   = '';
    $mbt_au_avatar_url = '';
    $mbt_au_btn_text   = esc_html__( 'Upload Avatar', 'mbt-avatar-uploader' );

    if ( $mbt_au_attach_id > 0 ) {
        $mbt_au_tmp_url = wp_get_attachment_url( $mbt_au_attach_id );
        if ( ! empty( $mbt_au_tmp_url ) ) {
            $mbt_au_avatar_url = $mbt_au_tmp_url;
        } else {
            delete_user_meta( $mbt_au_user_id, 'mbt_user_avatar_attachment_id' );
            $mbt_au_attach_id = 0;
        }
    }

    if ( empty( $mbt_au_avatar_url ) ) {
        $mbt_au_avatar_url = MBT_AU_PLUGIN_URL . '/assets/images/mbt-default-user-avatar.png';
    }

    if ( $mbt_au_attach_id > 0 ) {
        $mbt_au_af_class = 'show';
        $mbt_au_btn_text = esc_html__( 'Change Avatar', 'mbt-avatar-uploader' );
    }

    $mbt_au_user_info    = get_userdata( $mbt_au_user_id );
    $mbt_au_display_name = $mbt_au_user_info ? esc_html( $mbt_au_user_info->display_name ) : '';
    $mbt_au_email        = $mbt_au_user_info ? esc_html( $mbt_au_user_info->user_email ) : '';
    $mbt_au_alt          = $mbt_au_user_info
        ? esc_attr( sprintf( /* translators: %s: display name */ __( '%s avatar', 'mbt-avatar-uploader' ), $mbt_au_user_info->display_name ) )
        : esc_attr__( 'Your avatar', 'mbt-avatar-uploader' );
    ?>

    <!-- ===== Main card ===== -->
    <div class="mbt-au-card">

        <!-- Card header gradient -->
        <div class="mbt-au-card-header">
            <h3><?php esc_html_e( 'Profile Photo', 'mbt-avatar-uploader' ); ?></h3>
        </div>

        <!-- Avatar section (straddles header/body) -->
        <div class="mbt-au-field avatar-field-sec">
            <div class="sec-overlay"></div>
            <div class="mbt-au-spinner sec-overlay-spinner"></div>

            <div class="mbt-au-avatar-ring">
                <div class="mbt-au-avatar-wrap">
                    <img id="mbt_au_avatar"
                         src="<?php echo esc_url( $mbt_au_avatar_url ); ?>"
                         alt="<?php echo $mbt_au_alt; ?>">
                    <div class="mbt-au-avatar-edit-hint" aria-hidden="true">
                        <!-- Pencil icon -->
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm17.71-10.21a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                        </svg>
                    </div>
                    <!-- Hidden legacy div kept for JS selector compat -->
                    <div class="mbt-au-field avatar-field <?php echo esc_attr( $mbt_au_af_class ); ?>"></div>
                </div>
            </div>

            <!-- Card body -->
            <div class="mbt-au-card-body">
                <?php if ( $mbt_au_display_name ) : ?>
                    <p class="mbt-au-user-label"><?php echo $mbt_au_display_name; ?></p>
                    <p class="mbt-au-user-sublabel"><?php echo $mbt_au_email; ?></p>
                <?php endif; ?>

                <!-- Action buttons -->
                <div class="mbt-au-field">
                    <input class="file-field" type="file" id="mbt_avatar" accept=".jpg,.jpeg,.png" />

                    <div class="mbt-au-actions">
                        <button type="button" class="btn file-select-btn">
                            <!-- Upload/Camera icon -->
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 15.2A3.2 3.2 0 1 0 12 8.8a3.2 3.2 0 0 0 0 6.4zm0-8.4a5.2 5.2 0 1 1 0 10.4A5.2 5.2 0 0 1 12 6.8zM9 2L7.17 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3.17L15 2H9z"/>
                            </svg>
                            <span class="mbt-au-btn-text"><?php echo esc_html( $mbt_au_btn_text ); ?></span>
                        </button>

                        <button type="button" class="btn file-remove-btn <?php echo esc_attr( $mbt_au_af_class ); ?>">
                            <!-- Trash icon -->
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                            <span class="mbt-au-btn-text"><?php esc_html_e( 'Remove', 'mbt-avatar-uploader' ); ?></span>
                        </button>
                    </div>
                </div>

                <!-- Status message -->
                <div class="mbt-au-message" role="alert" aria-live="polite"></div>
            </div>
        </div><!-- /.avatar-field-sec -->

    </div><!-- /.mbt-au-card -->

    <!-- ===== Crop panel (shown after file is selected) ===== -->
    <div class="mbt-au-field preview-field">
        <div class="ajax-overlay"></div>
        <div class="mbt-au-spinner ajax-overlay-spinner"></div>

        <div class="mbt-au-crop-card">
            <p class="mbt-au-crop-card-title"><?php esc_html_e( 'Crop &amp; Adjust', 'mbt-avatar-uploader' ); ?></p>

            <div class="mbt-avatar-preview-wrap">
                <div id="mbt-avatar-preview"></div>
            </div>

            <p class="mbt-au-crop-hint"><?php esc_html_e( 'Drag to reposition · Scroll to zoom', 'mbt-avatar-uploader' ); ?></p>

            <div class="mbt-au-crop-actions">
                <button type="button" class="btn btn-ghost" id="rotateLeft" data-deg="-90">
                    <!-- Rotate-left icon -->
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7.11 8.53L5.7 7.11C4.8 8.27 4.24 9.61 4.07 11h2.02c.14-.87.49-1.72 1.02-2.47zM6.09 13H4.07c.17 1.39.72 2.73 1.62 3.89l1.41-1.42A5.98 5.98 0 0 1 6.09 13zm1.01 5.32c1.16.9 2.51 1.44 3.9 1.61V17.9a5.95 5.95 0 0 1-2.46-1.03L7.1 18.32zM13 4.07V1L8.45 5.55 13 10V6.09c2.84.48 5 2.94 5 5.91s-2.16 5.43-5 5.91v2.02c3.95-.49 7-3.85 7-7.93 0-4.08-3.05-7.44-7-7.93z"/></svg>
                    <span class="mbt-au-btn-text"><?php esc_html_e( 'Left', 'mbt-avatar-uploader' ); ?></span>
                </button>

                <button type="button" class="btn mbt-avatar-submit">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19.35 10.04A7.49 7.49 0 0 0 12 4C9.11 4 6.6 5.64 5.35 8.04A5.994 5.994 0 0 0 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
                    </svg>
                    <?php esc_html_e( 'Save Avatar', 'mbt-avatar-uploader' ); ?>
                </button>

                <button type="button" class="btn btn-ghost" id="rotateRight" data-deg="90">
                    <!-- Rotate-right icon -->
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M15.55 5.55L11 1v3.07C7.06 4.56 4 7.92 4 12c0 4.08 3.05 7.44 7 7.93v-2.02a5.99 5.99 0 0 1-5-5.91c0-2.97 2.16-5.43 5-5.91V10l4.55-4.45zM19.93 11a7.492 7.492 0 0 0-1.62-3.89l-1.42 1.42c.54.75.88 1.6 1.02 2.47h2.02zm-3.02 7.32l-1.41-1.41A5.98 5.98 0 0 1 13 17.9v2.03c1.39-.17 2.74-.71 3.91-1.61zm1.39-4.32h2.02c-.17-1.39-.72-2.73-1.62-3.89l-1.41 1.41c.54.76.88 1.61 1.01 2.48z"/></svg>
                    <span class="mbt-au-btn-text"><?php esc_html_e( 'Right', 'mbt-avatar-uploader' ); ?></span>
                </button>
            </div>
        </div><!-- /.mbt-au-crop-card -->
    </div><!-- /.preview-field -->

<?php else : ?>
    <div class="mbt-au-card">
        <div class="mbt-au-card-header"><h3><?php esc_html_e( 'Profile Photo', 'mbt-avatar-uploader' ); ?></h3></div>
        <div class="mbt-au-card-body">
            <p style="color:#8b90a7;font-size:14px;margin:20px 0;">
                <?php esc_html_e( 'You must be logged in to upload an avatar.', 'mbt-avatar-uploader' ); ?>
            </p>
        </div>
    </div>
<?php endif; ?>
</div>