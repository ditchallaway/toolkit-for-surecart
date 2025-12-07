"use strict";

jQuery(document).ready(function ($) {

    $(document).on('scChange', '.sc-show-archived', function (e) {
        if (e.target.checked) {
            surelywp_tk_misc_set_downloads_images_preview();
        }
    });

    $(document).on('click', '.css-2okr1n', function (e) {
        setTimeout(() => {
            surelywp_tk_misc_set_downloads_images_preview();
        }, 1000);
    });

    if ($('.surecart_page_sc-products').length) {
        
        setTimeout(() => {
            surelywp_tk_misc_set_downloads_images_preview();
        }, 1000);
    }

    function surelywp_tk_misc_set_downloads_images_preview() {

        const product_id = get_param(window.location.href, 'id');

        if (!product_id) return;

        $.ajax({
            url: tk_misc_backend_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'surelywp_tk_misc_get_product_downloads',
                product_id: product_id,
                view_type: 'admin',
                nonce: tk_misc_backend_ajax_object.misc_ajax_nonce,
            },
            dataType: 'json',
            success: function (response) {
                if (!response.status) return;

                const imageItems = response.downloads
                    .filter(download => download?.media?.content_type?.startsWith('image/'))
                    .map(download => ({
                        id: download.media.id,
                        filename: download.media.filename
                    }));

                imageItems.forEach(item => {
                    const rows = document.querySelectorAll('.css-djnk3q');
                    rows.forEach(row => {
                        const nameDiv = row.parentElement?.querySelector('.css-1uabedp');

                        if (
                            nameDiv &&
                            nameDiv.textContent.trim() === item.filename &&
                            !row.querySelector('img')
                        ) {

                            row.innerHTML = `<sc-skeleton style="width: 100%; height: 100%; --border-radius: 0;"></sc-skeleton>`;

                            $.ajax({
                                url: tk_misc_backend_ajax_object.ajax_url,
                                type: 'POST',
                                data: {
                                    action: 'surelywp_tk_misc_get_download_media_url',
                                    media_id: item.id,
                                    view_type: 'admin',
                                    nonce: tk_misc_backend_ajax_object.misc_ajax_nonce,
                                },
                                dataType: 'json',
                                success: function (media) {
                                    const url = media?.url;
                                    if (!url) return;

                                    const rows = document.querySelectorAll('.css-djnk3q');
                                    rows.forEach(innerRow => {
                                        const innerNameDiv = innerRow.parentElement?.querySelector('.css-1uabedp');
                                        if (
                                            innerNameDiv &&
                                            innerNameDiv.textContent.trim() === item.filename
                                        ) {
                                            const img = document.createElement('img');
                                            img.src = url;
                                            img.alt = item.filename;
                                            img.style.maxWidth = '100%';
                                            img.style.height = 'auto';
                                            innerRow.innerHTML = '';
                                            innerRow.appendChild(img);
                                        }
                                    });
                                },
                                error: function (xhr, status, error) {
                                    console.error(`Error loading media for ID ${item.id}:`, error);
                                }
                            });
                        }
                    });
                });
            },
            error: function (xhr, status, error) {
                console.error('Error fetching downloads:', error);
            }
        });
    }

    $(document).on('click', '#sc-checkout-sync-btn', function (e) {

        $('.msg-success').remove();
        var is_running = $(this).hasClass('sysc-loading');
        if (is_running) {
            return;
        }

        $(this).addClass('sysc-loading');

        $.ajax({
            url: tk_misc_backend_ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                'action': 'surelywp_tk_misc_manual_checkout_sync',
                'nonce': tk_misc_backend_ajax_object.misc_ajax_nonce,
            },
            success: function (res) {
                $('#sc-checkout-sync-btn').removeClass('sysc-loading');
                if (res.status) {
                    $('#sc-checkout-sync-btn').after('<p class="msg-success">' + res.message + '</p>');
                }
            }
        });
    });

    if ($('.surecart_page_sc-orders .css-htf0c5').length && tk_misc_backend_ajax_object.order_id && tk_misc_backend_ajax_object.enable_retry_btn) {

        var order_status_get_interval = setInterval(function () {
            var order_status = jQuery('.css-htf0c5').eq(0).find('sc-order-status-badge').attr('status');
            if (order_status) {
                if ('payment_failed' === order_status) {

                    var order_id = tk_misc_backend_ajax_object.order_id;

                    $.ajax({
                        url: tk_misc_backend_ajax_object.ajax_url,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'action': 'surelywp_tk_misc_get_payment_retry_button_tag',
                            'nonce': tk_misc_backend_ajax_object.misc_ajax_nonce,
                            'order_id': order_id,

                        },
                        success: function (response) {
                            if (response.status) {
                                $('.css-t9ogfm sc-dropdown sc-menu').append(response.retry_button);
                                $('body').append(response.dialog_popup);
                            }
                        }
                    });

                }
                clearInterval(order_status_get_interval);
            }
        }, 1000);
    }

    $(document).on('click', '#surelywp-retry-payment-menu', function (e) {
        $('#surelywp-retry-payment-confirm-popup').attr('open', true);
    });

    $(document).on('click', '#surelywp-retry-payment-cancel', function (e) {
        $('#surelywp-retry-payment-confirm-popup').attr('open', false);
    });

    $(document).on('click', '#surelywp-retry-payment-btn', function (e) {
        var period_id = $(this).data('period-id');
        $(this).attr('loading', true);
        $.ajax({
            url: tk_misc_backend_ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                'action': 'surelywp_tk_misc_retry_sub_payment',
                'nonce': tk_misc_backend_ajax_object.misc_ajax_nonce,
                'period_id': period_id,

            },
            success: function (response) {
                if (response) {
                    $('body').append('<div class="components-snackbar-list css-uksis0" tabindex="-1" data-testid="snackbar-list"><div style="height: auto; opacity: 1;"><div class="components-snackbar-list__notice-container"><div class="components-snackbar" tabindex="0" role="button" aria-label="Dismiss this notice" data-testid="snackbar"><div class="components-snackbar__content">' + response.message + '</div></div></div></div></div>');
                    $('#surelywp-retry-payment-confirm-popup').attr('open', false);
                    setTimeout(
                        function () {
                            window.location.reload();
                        },
                        1000
                    )
                }
            }
        });
    });

    setTimeout(() => {
        if ($('.surecart_page_sc-orders .css-htf0c5').length && tk_misc_backend_ajax_object.enable_recovered_badge) {
            var customerInterval = setInterval(function () {

                var customer_url = $('.css-wzxb7d > div:first-child').find('sc-line-item a').attr('href');
                var customer_id = get_param(customer_url, 'id');

                if (customer_id) {

                    var currentUrl = window.location.href; // Get the current URL
                    var urlParams = new URLSearchParams(window.location.search);
                    var order_id = urlParams.get('id'); // Extract 'id' parameter

                    if (order_id) {
                        $.ajax({
                            url: tk_misc_backend_ajax_object.ajax_url,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                'action': 'surelywp_tk_misc_add_recovered_tag',
                                'customer_id': customer_id,
                                'order_id': order_id,

                            },
                            success: function (response) {
                                if (response.status) {

                                    $('.css-dainmi .css-htf0c5').append(response.tag);
                                }
                            }
                        });
                    }
                    clearInterval(customerInterval);
                }
            }, 1000);
        };
    }, 1500);

    // Customer Dashboard Menu Text
    $(document).on('change', '#surelywp-tk-misc-enable-rename-name', function (e) {
        $('#customer-dashboard-nav-menu-title').toggle();
    });

    // Login Redirection
    $(document).on('change', '#surelywp-tk-misc-enable-login-redirection', function (e) {
        $('#surelywp-tk-misc-login-redirect-url').toggle();
    });

    // Role Based Redirecton.
    $(document).on('change', '#surelywp-tk-misc-enable-role-based-login-redirection', function (e) {
        $('#role-based-redirection-option').toggle();
        $('#surelywp-tk-misc-login-redirection-user-roles').select2();
    });


    // Login Redirection user role selection add url setting
    $(document).on('select2:select', '#surelywp-tk-misc-login-redirection-user-roles', function (e) {

        var role_value = e.params.data.id; // Get the selected value.
        var role_label = e.params.data.text; // Get the selected text.

        var clonedSetting = $('#surelywp-tk-misc-login-redirect-url').clone();
        clonedSetting.removeClass('hidden');

        // Update ID
        clonedSetting.attr('id', role_value + '-login-redirection');

        // Update label and description
        clonedSetting.find('.input-label').text(role_label + ' Login Redirection URL');
        clonedSetting.find('.input-label-desc').text('Enter the URL where users with the ' + role_label + ' user role should be redirected when logging into the SureCart Customer Dashboard.');

        // Update input field
        var inputField = clonedSetting.find('input');

        inputField.attr('id', '');
        inputField.attr('name', 'surelywp_tk_misc_settings_options[redirection_urls][' + role_value + '][login_redirect_url]');
        inputField.val(''); // Clear value for new setting

        // Append the cloned setting after the original
        $('.role-based-redirection-option').append(clonedSetting);
    });

    // Remove the user tole url setting.
    $(document).on('select2:unselect', '#surelywp-tk-misc-login-redirection-user-roles', function (e) {
        var role_value = e.params.data.id;
        $('#' + role_value + '-login-redirection').remove();
    });

    // Logout Redirection
    $(document).on('change', '#surelywp-tk-misc-enable-logout-redirection', function (e) {
        $('#surelywp-tk-misc-logout-redirect-url').toggle();
    });

    // Back Home Redirection
    $(document).on('change', '#surelywp-tk-misc-enable-bh-redirection', function (e) {
        $('#surelywp-tk-misc-bh-redirect-url').toggle();
    });

    if ($('.surelywp-price-desc-display-type').length) {
        document.querySelector('.surelywp-price-desc-display-type').choices = [
            { label: 'Display Only Selected Price Description', value: 'display_selected' },
            { label: 'Display All Descriptions', value: 'display_all' }
        ];
    }

    if ($('#surelywp_tk_misc_price_desc').length) {
        var price_desc_metabox = $('#surelywp_tk_misc_price_desc');
        let interval = setInterval(function () {
            if ($('.css-1pdnxkc').length && !$('.css-1pdnxkc').next().is(price_desc_metabox)) {
                $('.css-1pdnxkc').after(price_desc_metabox);
                price_desc_metabox.removeClass('hidden');
                clearInterval(interval); // Stop the interval after adding.
            }
        }, 500);
    }

    // Add External/Affiliate Products block on surecart product page.
    if ($('#surelywp_tk_misc_external_product').length) {

        var external_product_block = $('#surelywp_tk_misc_external_product');
        external_product_block.removeClass('hidden');
        external_product_block.remove();
        $('.css-wzxb7d > div').eq(0).after(external_product_block);
    }

    if ($('#surelywp_tk_misc_price_desc').length || $('#surelywp_tk_misc_external_product').length) {

        $(document).on("scFormSubmit", '.sc-model-form', function (e) {

            var formData = new FormData();
            var post_id = $('.surelywp-tk-product-post-id').val();
            formData.append('post_id', post_id);

            // External product options.
            var is_external_product_link_open_new_tab = $('#surelywp_tk-external-product-open-new-tab').prop('checked') ? 1 : 0;
            var external_product_btn_url = $('#surelywp-tk-external-product-btn-url').val();
            var external_product_btn_text = $('#surelywp-tk-external-product-btn-text').val();

            formData.append('is_external_product_link_open_new_tab', is_external_product_link_open_new_tab);
            formData.append('external_product_btn_url', external_product_btn_url);
            formData.append('external_product_btn_text', external_product_btn_text);


            // Price Descriptions Options.
            var misc_price_desc_display_type = $('#surelywp-price-desc-display-type').val();
            formData.append('_surelywp_tk_misc_price_desc_display_type', misc_price_desc_display_type);
            $('.surelyp-tk-misc-price-desc-input').each(function () {
                var name = $(this).attr('name');
                var value = $(this).val();
                formData.append(name, value);
            });

            formData.append('action', 'surelywp_tk_misc_update_product_meta');
            formData.append('nonce', tk_misc_backend_ajax_object.misc_ajax_nonce);

            console.log(formData);
            $.ajax({
                url: tk_misc_backend_ajax_object.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
            });
        });
    }

    function get_param(url, param) {
        if (url) {
            var qs = url.substring(url.indexOf('?') + 1).split('&');
            for (var i = 0, result = {}; i < qs.length; i++) {
                qs[i] = qs[i].split('=');
                if (qs[i][0] === param) {
                    return decodeURIComponent(qs[i][1]);
                }
            }
        }
        return false;
    }

});