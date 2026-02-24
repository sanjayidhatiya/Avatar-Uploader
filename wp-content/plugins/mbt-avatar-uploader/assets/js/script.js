/* global mbt_au_frontend_object, jQuery */
/**
 * MBT Avatar Uploader – front-end script.
 * Depends on jQuery (WP bundled) and Croppie.
 */
( function ( $ ) {
    'use strict';

    $( document ).ready( function () {

        /* ------------------------------------------------------------------ */
        /* 1.  Open system file-chooser when the select button OR avatar is    */
        /*     clicked (avatar wrap delegates to the same file input).         */
        /* ------------------------------------------------------------------ */
        $( document ).on( 'click', '.file-select-btn, .mbt-au-avatar-wrap', function () {
            $( '.file-field' ).click();
        } );

        /* ------------------------------------------------------------------ */
        /* 2.  Initialise Croppie with dimensions from admin settings.         */
        /* ------------------------------------------------------------------ */
        var vanilla;

        var viewportW  = parseInt( mbt_au_frontend_object.mbt_ua_setting.file_dimension.w, 10 ) || 100;
        var viewportH  = parseInt( mbt_au_frontend_object.mbt_ua_setting.file_dimension.h, 10 ) || 100;
        var boundaryW  = viewportW + 100;
        var boundaryH  = viewportH + 100;
        var cropStyle  = mbt_au_frontend_object.mbt_ua_setting.file_style || 'circle';

        // Sanitise style value before passing to Croppie.
        if ( cropStyle !== 'circle' && cropStyle !== 'square' ) {
            cropStyle = 'circle';
        }

        vanilla = $( '#mbt-avatar-preview' ).croppie( {
            viewport: {
                width:  viewportW,
                height: viewportH,
                type:   cropStyle
            },
            boundary: {
                width:  boundaryW,
                height: boundaryH
            },
            enableExif:        true,
            showZoomer:        false,
            enableOrientation: true
        } );

        /* ------------------------------------------------------------------ */
        /* 3.  Rotation buttons.                                               */
        /* ------------------------------------------------------------------ */
        $( '#rotateLeft, #rotateRight' ).on( 'click', function () {
            vanilla.croppie( 'rotate', parseInt( $( this ).data( 'deg' ), 10 ) );
        } );

        /* ------------------------------------------------------------------ */
        /* 4.  File selected – validate type & size, then bind to Croppie.    */
        /* ------------------------------------------------------------------ */
        $( '#mbt_avatar' ).on( 'change', function () {
            $( '.mbt-au-message' ).removeClass( 'success error' ).html( '' );
            $( '.preview-field' ).removeClass( 'ready' );

            if ( ! this.files || ! this.files[ 0 ] ) {
                return;
            }

            var file         = this.files[ 0 ];
            var avatarSizeKB = file.size / 1024;
            var ext          = file.name.split( '.' ).pop().toLowerCase();

            // Build allowed extension list from settings object.
            var obj           = mbt_au_frontend_object.mbt_ua_setting.file_type || {};
            var allowedExts   = Object.keys( obj ).map( function ( key ) { return obj[ key ]; } );

            if ( allowedExts.indexOf( ext ) === -1 ) {
                $( '.mbt-au-message' )
                    .addClass( 'error' )
                    .text( mbt_au_frontend_object.mbt_ff_error_text + ': ' + allowedExts.join( ', ' ) );
                $( this ).val( '' );
                return;
            }

            var maxKB = parseInt( mbt_au_frontend_object.mbt_ua_setting.max_size, 10 ) || 1024;
            if ( avatarSizeKB > maxKB ) {
                var label = maxKB >= 1024
                    ? ( Math.round( maxKB / 1024 ) + ' MB' )
                    : ( maxKB + ' KB' );
                $( '.mbt-au-message' )
                    .addClass( 'error' )
                    .text( mbt_au_frontend_object.mbt_fs_error_text + ' ' + label );
                $( this ).val( '' );
                return;
            }

            // Read & bind to Croppie.
            if ( window.FileReader ) {
                var reader = new FileReader();
                reader.onload = function ( e ) {
                    $( '.preview-field' ).addClass( 'ready' );
                    vanilla.croppie( 'bind', { url: e.target.result } );
                };
                reader.readAsDataURL( file );
            } else {
                $( '.mbt-au-message' )
                    .addClass( 'error' )
                    .text( 'Your browser does not support the FileReader API.' );
            }
        } );

        /* ------------------------------------------------------------------ */
        /* 5.  Upload button – crop and send to server.                        */
        /* ------------------------------------------------------------------ */
        $( '.mbt-avatar-submit' ).on( 'click', function () {
            vanilla.croppie( 'result', {
                type: 'canvas',
                size: 'viewport'
            } ).then( function ( dataUrl ) {
                $( '.ajax-overlay, .ajax-overlay-spinner' ).show();

                $.ajax( {
                    url:      mbt_au_frontend_object.ajaxUrl,
                    type:     'POST',
                    dataType: 'json',
                    data:     {
                        action:      'mbt_avatar_save',
                        nonce:       mbt_au_frontend_object.nonce,
                        user_avatar: dataUrl
                    },
                    success: function ( response ) {
                        $( '.ajax-overlay, .ajax-overlay-spinner' ).hide();

                        if ( response.success ) {
                            $( '.mbt-au-message' ).addClass( 'success' ).text( response.data.message );
                            $( '#mbt_au_avatar' ).attr( 'src', response.data.avatar_url );
                            $( '.preview-field' ).removeClass( 'ready' );
                            $( '#mbt_avatar' ).val( '' );
                            $( '.avatar-field' ).addClass( 'show' );
                            $( '.file-remove-btn' ).addClass( 'show' );
                            $( '.file-select-btn .mbt-au-btn-text' ).text( mbt_au_frontend_object.mbt_ca_btn_text );
                        } else {
                            $( '.mbt-au-message' ).addClass( 'error' ).text( response.data.message );
                        }
                    },
                    error: function () {
                        $( '.ajax-overlay, .ajax-overlay-spinner' ).hide();
                        $( '.mbt-au-message' ).addClass( 'error' ).text( 'Request failed. Please try again.' );
                    }
                } );
            } );
        } );

        /* ------------------------------------------------------------------ */
        /* 6.  Remove button – delete avatar from server.                      */
        /* ------------------------------------------------------------------ */
        $( '.file-remove-btn' ).on( 'click', function () {
            $( '.sec-overlay, .sec-overlay-spinner' ).show();

            $.ajax( {
                url:      mbt_au_frontend_object.ajaxUrl,
                type:     'POST',
                dataType: 'json',
                data:     {
                    action: 'mbt_avatar_remove',
                    nonce:  mbt_au_frontend_object.nonce
                },
                success: function ( response ) {
                    $( '.sec-overlay, .sec-overlay-spinner' ).hide();

                    if ( response.success ) {
                        $( '.mbt-au-message' ).addClass( 'success' ).text( response.data.message );
                        $( '#mbt_au_avatar' ).attr( 'src', response.data.avatar_url );
                        $( '.preview-field' ).removeClass( 'ready' );
                        $( '#mbt_avatar' ).val( '' );
                        $( '.avatar-field' ).removeClass( 'show' );
                        $( '.file-remove-btn' ).removeClass( 'show' );
                        $( '.file-select-btn .mbt-au-btn-text' ).text( mbt_au_frontend_object.mbt_ua_btn_text );
                    } else {
                        $( '.mbt-au-message' ).addClass( 'error' ).text( response.data.message );
                    }
                },
                error: function () {
                    $( '.sec-overlay, .sec-overlay-spinner' ).hide();
                    $( '.mbt-au-message' ).addClass( 'error' ).text( 'Request failed. Please try again.' );
                }
            } );
        } );

    } ); // end ready
}( jQuery ) );