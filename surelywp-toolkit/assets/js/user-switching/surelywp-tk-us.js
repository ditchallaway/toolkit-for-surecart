"use strict";

jQuery(document).ready(function ($) {

    function add_subsctiption_order_us_link(customer_id_column, target_column) {


        document.querySelectorAll(customer_id_column + ' tr').forEach((row) => {

            const customerDiv = row.querySelector('.surelywp-order-customer-id');
            const targetColumn = row.querySelector(target_column);

            if (customerDiv && targetColumn) {

                const customerID = customerDiv.getAttribute('data-customer-id');

                // Create the span and link elements
                const span = document.createElement('span');
                span.className = 'view';
                span.innerHTML = ' | ';

                const switchLink = document.createElement('a');

                switchLink.href = window.location.href + '&customerID=' + customerID;
                switchLink.textContent = tk_us_ajax_object.Linktext;

                span.appendChild(switchLink);
                targetColumn.appendChild(span);
            }
        });

    }

    if ($('#customer_id').length && $('.surecart_page_sc-orders')) {

        $('.surecart_page_sc-orders .column-customer_id').hide();

        add_subsctiption_order_us_link('.surecart_page_sc-orders', '.column-order');
    }


    if ($('#customer_id').length && $('.surecart_page_sc-subscriptions')) {

        $('.surecart_page_sc-subscriptions .column-customer_id').hide();
        add_subsctiption_order_us_link('.surecart_page_sc-subscriptions', '.column-customer .row-actions');
    }


    // hide/show settings.
    $(document).on('change', '#surelywp-tk-us-settings-status', function (e) {
        $('#surelywp-tk-allowed-user-roles').toggle();
        $('#surelywp-tk-us-user-roles').select2();
        $('#surelywp-tk-us-customers-page-settings').toggle();
        $('#surelywp-tk-us-wordpress-page-settings').toggle();
        $('#surelywp-tk-us-orders-page-settings').toggle();
        $('#surelywp-tk-us-subscriptions-page-settings').toggle();
        $('#surelywp-tk-us-service-page-settings').toggle();
        $('#surelywp-tk-us-support-tickets-page-settings').toggle();
        $('#surelywp-tk-us-inquiries-page-settings').toggle();
    });

    var user_role = tk_us_ajax_object.current_user_role;
    var role_enable = tk_us_ajax_object.role_enable;

    if (role_enable) {

        // Add User switching button on services view.
        if (tk_us_ajax_object.current_hook === 'surecart_page_sc-services') {
            var customer_id = $('#surelywp-sv-customer-id').val();
            if (customer_id && $('.surelywp-sv-serview-view.admin').length) {
                let btn_html = '<sc-menu-item id="surelywp-tk-us-switch-btn" href="' + window.location + '&customerID=' + customer_id + '" class="hydrated">' + tk_us_ajax_object.Linktext + '</sc-menu-item>';
                $('#service-action-btn').next('sc-menu').append(btn_html);
            }
        }

        // Add User switching button on support ticket view.
        if (tk_us_ajax_object.current_hook === 'surecart_page_sc-support') {
            var customer_id = $('#surelywp-sp-customer-id').val();
            if (customer_id && $('.surelywp-sp-support-view.admin').length) {
                let btn_html = '<sc-menu-item id="surelywp-tk-us-switch-btn" href="' + window.location + '&customerID=' + customer_id + '" class="hydrated">' + tk_us_ajax_object.Linktext + '</sc-menu-item>';
                $('#support-action-btn').next('sc-menu').append(btn_html);
            }
        }

        // Add User switching button on inquiry view.
        if (tk_us_ajax_object.current_hook === 'surecart_page_sc-inquiries') {
            var customer_id = $('#inquiry-customer').data('customer-id');
            if (customer_id && $('.surelywp-cm-inquiry-view.admin').length) {
                let btn_html = '<sc-menu-item id="surelywp-tk-us-switch-btn" href="' + window.location + '&customerID=' + customer_id + '" class="hydrated">' + tk_us_ajax_object.Linktext + '</sc-menu-item>';
                if ($('#inquiry-action-btn').length) {
                    $('#inquiry-action-btn').next('sc-menu').append(btn_html);
                } else {
                    var action_btn = '<sc-dropdown position="bottom-right" placement="bottom-start" close-on-select="" style="--panel-width: 14em;" class="hydrated"><sc-button id="inquiry-action-btn" type="primary" slot="trigger" caret="" loading="false" size="medium" class="hydrated">Actions</sc-button><sc-menu class="hydrated">' + btn_html + '</sc-menu></sc-dropdown>';
                    $('.css-t9ogfm').append(action_btn);
                }
            }
        }

        if (tk_us_ajax_object.current_hook == 'surecart_page_sc-customers') {

            $('.surecart_page_sc-customers .row-title').each(function (index, el) {

                let url = $(this).attr('href');
                var qs = url.substring(url.indexOf('?') + 1).split('&');
                for (var i = 0, result = {}; i < qs.length; i++) {
                    qs[i] = qs[i].split('=');
                    result[qs[i][0]] = decodeURIComponent(qs[i][1]);
                }
                let customer_id = result.id;
                if (customer_id) {
                    let view_elem = '<span class="view"> | <a href="' + window.location + '&customerID=' + customer_id + '">' + tk_us_ajax_object.Linktext + '</span>';
                    $(this).next('.row-actions').find('.edit').after(view_elem);
                }
            });

            /* Add button in customer detail page */
            var page_url = window.location.href;
            var customer_id = get_param(page_url, 'id');
            if (customer_id) {
                var customerInterval = setInterval(function () {
                    jQuery('.css-2jjstt').append('<div class="switch-btn" style="margin-top:10px"><sc-button outline type="primary" size="small" href="' + window.location + '&customerID=' + customer_id + '">' + tk_us_ajax_object.Linktext + '</sc-button></div>');
                    if ($('.switch-btn').length > 0) {
                        clearInterval(customerInterval);
                    }
                }, 1000);
            }
        }

        // subscription details page.
        if (tk_us_ajax_object.current_hook == 'surecart_page_sc-subscriptions') {

            var customerInterval = setInterval(function () {

                var button = jQuery('.css-wzxb7d > div:first-child').find('.components-card__footer sc-button').attr('href');

                var param_attr = get_param(button, 'id');

                if (param_attr) {
                    jQuery('.css-wzxb7d > div:first-child').find('.components-card__footer').append('<div><sc-button outline type="primary" size="small" class="switch-btn" href="' + window.location + '&customerID=' + param_attr + '">' + tk_us_ajax_object.Linktext + '</sc-button></div>');
                    if ($('.switch-btn').length > 0) {
                        clearInterval(customerInterval);
                    }
                }

            }, 1000);
        } else if (tk_us_ajax_object.current_hook == 'surecart_page_sc-orders') { //Add button in Order detail.


            var customerInterval = setInterval(function () {

                var customer_url = jQuery('.css-wzxb7d > div:first-child').find('sc-line-item a').attr('href');

                var param_attr = get_param(customer_url, 'id');

                if (param_attr) {
                    jQuery('.css-wzxb7d > div:first-child').find('.css-hwa08m').append('<div><sc-button outline type="primary" size="small" class="switch-btn" href="' + window.location + '&customerID=' + param_attr + '">' + tk_us_ajax_object.Linktext + '</sc-button></div>');
                    if ($('.switch-btn').length > 0) {
                        clearInterval(customerInterval);
                    }
                }

            }, 1000);
        }
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