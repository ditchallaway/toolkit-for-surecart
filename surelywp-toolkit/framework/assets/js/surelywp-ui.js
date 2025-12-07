/* globals surely_plugin_fw_ui */
"use strict";
$ = jQuery;
jQuery(document).ready(function () {

    $('.customer-role').select2();

    /* Reset modal */
    $(document).on('click', '.reset-trigger', function (event) {
        event.preventDefault();
        $('.reset-modal').addClass("show-modal");
    });
    $(document).on('click', '.close-button', function (event) {
        event.preventDefault();
        $('.reset-modal').removeClass("show-modal");
    });

    $(document).on('click', '.single-read-notification', function (event) {
        var notificationId = $(this).data('id'); // Get ID from data-id attribute

        $.ajax({
            url: ajax_obj.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'surlywp_single_read_notification',
                id: notificationId,
                nonce: ajax_obj.nonce
            },
            success: function (response) {

                if (response.success) {
                    $('.notify-menu').html(response.data.html).addClass('opened-menu');
                    $('.dropdown-menu').addClass('active');
                }
            }
        });
    });

    /* Mark as read button event */
    $(document).on('click', '.marked-read', function (event) {
        var data = { action: 'surlywp_read_notification' };

        $('#wp_ajax_loader').show();
        $(this).hide();

        $.ajax({
            url: ajax_obj.ajaxurl,
            type: 'POST',
            cache: false,
            dataType: 'json',
            data: { 'action': 'surlywp_read_notification' },
            success: function (response) {
                $('.notify-menu').html(response.data.html).addClass('opened-menu');
                $('.dropdown-menu').addClass('active');
                $('#wp_ajax_loader').hide();
                $('.unread-count').hide();
            }
        })
    });

    var GetCok = getCookie('sidebarMode');


    if (GetCok == 'close') {
        console.log(GetCok, 'GetCok');
        jQuery('.sidebar-wrap').toggleClass('close-panel');
        jQuery('.sidebar').toggleClass('close');
    }

    /* Switched addon */
    jQuery('#surelywp-addons-lists').change(function () {
        var getVal = jQuery(this).val();
        var url = location.href;
        var admin_url = url.split('page=');
        location.href = admin_url[0] + 'page=' + getVal;

    });

    jQuery('.custom-option').click(function () {
        var getVal = jQuery(this).attr('data-value');
        var url = location.href;
        var admin_url = url.split('page=');
        location.href = admin_url[0] + 'page=' + getVal;

    });

    // /* Moved admin notice to top of wrap class */
    jQuery(".notice.notice-error").insertAfter(".reset-modal");
    jQuery(".notice.notice-success").insertAfter(".reset-modal");

    /* Trigger save settings */
    jQuery('#surelywp_save').click(function () {
        jQuery('.setting-btn .surelywp-ric-settings-save').trigger('click');
    });

    $(document).on('click', '.dropdown-toggle', function (event) {
        jQuery(".dropdown-menu").toggleClass('active');
        jQuery(".notify-menu").toggleClass('opened-menu');
    });

    jQuery('.custom-select').click(function () {
        jQuery(this).toggleClass('opened');
    });

    jQuery(document).on('click', function (e) {
        if (!($(e.target).closest(".custom-select").hasClass('opened'))) {
            jQuery('.custom-select').removeClass('opened');

        }

        if (!($(e.target).closest(".notify-menu").hasClass('opened-menu'))) {
            jQuery('.notify-menu').removeClass('opened-menu');
            jQuery('.dropdown-menu').removeClass('active');

        }
    });


    /* Display loader on addon activate button click */
    $(document).on('click', '.surelywp-active', function (event) {
        $('#wp_ajax_loader').show();
    });


    if ($('.settings-error').length) {

        const url = new URL(window.location.href);

        if (url.searchParams.has('active')) {
            url.searchParams.delete('active');
            window.history.replaceState({}, document.title, url.toString());
        }

        setTimeout(() => {
            $('.settings-error').fadeOut(400, function () {
                $(this).remove();
            });
        }, 2000);
    }

    $(document).on('click', '.close-modal-button', function(event) {
		event.preventDefault();
		 $('.reset-modal').removeClass("show-modal");
	});
    
    $(document).on('click', '.licence-notice-close', function(e) {
       e.preventDefault();
       $(this).closest('.licence-notice').hide();
    });
});

function setCookie(name, value, expiry) {
    let d = new Date();
    d.setTime(d.getTime() + (expiry * 86400));
    document.cookie = name + "=" + value + ";" + "expires=" + d.toUTCString() + ";path=/";
}

function getCookie(name) {
    let cookie = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
    return cookie ? cookie[2] : null;
}