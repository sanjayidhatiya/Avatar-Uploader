jQuery(document).ready(function ($) {

    $(document).on('click', '.file-select-btn', function () {
        $('.file-field').click();
    });

    let vanilla;

    function readFile(input) {
        if (input.files?.[0]) {
            let reader = new FileReader();

            reader.onload = function (e) {
                $('.preview-field').addClass('ready');
                vanilla.croppie('bind', {
                    url: e.target.result
                }).then(function () {
                    console.log('jQuery bind complete');
                });

            }

            reader.readAsDataURL(input.files[0]);
        }
        else {
            alert("Sorry - you're browser doesn't support the FileReader API");
        }
    }

    let viewport_w = parseInt(mbt_au_frontend_object.mbt_ua_setting.file_dimension.w) || 100;
    let viewport_h = parseInt(mbt_au_frontend_object.mbt_ua_setting.file_dimension.h) || 100;

    let boundary_w = parseInt(viewport_w) + 100;
    let boundary_h = parseInt(viewport_h) + 100;

    vanilla = $('#mbt-avatar-preview').croppie({
        viewport: {
            width: viewport_w,
            height: viewport_h,
            type: mbt_au_frontend_object.mbt_ua_setting.file_style
        },
        boundary: {
            width: boundary_w,
            height: boundary_h
        },
        enableExif: true,
        showZoomer: false,
        enableOrientation: true
    });

    $("#rotateLeft").click(function () {
        vanilla.croppie('rotate', parseInt($(this).data('deg')));
    });

    $("#rotateRight").click(function () {
        vanilla.croppie('rotate', parseInt($(this).data('deg')));
    });


    $('#mbt_avatar').on('change', function () {

        $(".mbt-au-message").removeClass('success').removeClass('error').html('');
        $('.preview-field').removeClass('ready');

        let avatar_size = (this.files[0].size / 1024);
        let avatar_size_kb = (Math.round(avatar_size * 100) / 100);

        let obj = mbt_au_frontend_object.mbt_ua_setting.file_type;
        let fileExtension = Object.keys(obj).map(function (key) {
            return obj[key];
        });
        if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
            $(".mbt-au-message").addClass('error').html(mbt_au_frontend_object.mbt_ff_error_text + " : " + fileExtension.join(', '));
        } else if (mbt_au_frontend_object.mbt_ua_setting.max_size < avatar_size_kb) {
            let max_size_mb = (Math.round(mbt_au_frontend_object.mbt_ua_setting.max_size / 1024));
            if (max_size_mb < 1) {
                max_size_mb = mbt_au_frontend_object.mbt_ua_setting.max_size + ' KB';
            } else {
                max_size_mb = max_size_mb + ' MB';
            }
            $(".mbt-au-message").addClass('error').html(mbt_au_frontend_object.mbt_fs_error_text + ' ' + max_size_mb);
        } else {
            readFile(this);
        }

    });
    $('.mbt-avatar-submit').on('click', function (ev) {
        vanilla.croppie('result', {
            type: 'canvas',
            size: 'viewport'
        }).then(function (response) {
            $(".ajax-overlay").show();
            $(".ajax-overlay-spinner").show();

            $.ajax({
                url: mbt_au_frontend_object.ajaxUrl,
                type: "POST",
                dataType: 'json',
                data: {
                    action: 'mbt_avatar_save',
                    security: mbt_au_frontend_object.nonce,
                    "user_avatar": response
                },
                success: function (response) {
                    $(".ajax-overlay").hide();
                    $(".ajax-overlay-spinner").hide();

                    if (response.success) {
                        $('.mbt-au-message').addClass('success').html(response.data.message);

                        $("#mbt_au_avatar").attr('src', response.data.avatar_url);
                        $(".preview-field").removeClass('ready');
                        $('#mbt_avatar').val("");

                        $(".avatar-field").addClass('show');
                        $(".file-remove-btn").addClass('show');
                        $(".file-select-btn").text(mbt_au_frontend_object.mbt_ca_btn_text);
                    } else {
                        $('.mbt-au-message').addClass('error').html(response.data.message);
                    }
                }
            });
        });
    });

    $('.file-remove-btn').on('click', function (ev) {
        $(".sec-overlay").show();
        $(".sec-overlay-spinner").show();

        $.ajax({
            url: mbt_au_frontend_object.ajaxUrl,
            type: "POST",
            dataType: 'json',
            data: {
                action: 'mbt_avatar_remove',
                security: mbt_au_frontend_object.nonce
            },
            success: function (response) {
                $(".sec-overlay").hide();
                $(".sec-overlay-spinner").hide();

                if (response.success) {
                    $('.mbt-au-message').addClass('success').html(response.data.message);

                    $("#mbt_au_avatar").attr('src', response.data.avatar_url);
                    $(".preview-field").removeClass('ready');
                    $('#mbt_avatar').val("");

                    $(".avatar-field").removeClass('show');
                    $(".file-remove-btn").removeClass('show');
                    $(".file-select-btn").text(mbt_au_frontend_object.mbt_ua_btn_text);
                } else {
                    $('.mbt-au-message').addClass('error').html(response.data.message);
                }
            },
            error: function () {
                alert('AJAX failed');
            }
        });
    });

    $('.file-field').on('change', function () {
        $(".preview-field").addClass("ready");
    });
    
});