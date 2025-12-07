"use strict";
jQuery(document).ready(function ($) {

    // Add and remove "surelywp-surecart-triggers" class on fluent crm triggers choices.
    $(document).on('click', '.fc_trigger_left_col .el-menu-item', function (e) {
        if ($(this).find('span:contains("SureCart")').length > 0) {
            $('.el-dialog__body').addClass('surelywp-surecart-triggers');
        } else {
            $('.el-dialog__body').removeClass('surelywp-surecart-triggers');
        }
    });


    // For Surecart order view Page
    if ($('.surecart_page_sc-orders .css-1ssgtuh').length && tk_fc_backend_ajax_object.profile_btn_on_orders) {


        var ordersInterval = setInterval(function () {

            var customer_email = $('.css-hwa08m div[slot="description"]').html();

            if (customer_email) {

                display_fc_profile_view_btn(customer_email, 'sc_orders');
                clearInterval(ordersInterval);
            }
        }, 1000);
    };

    // For Surecart Customer view Page
    if ($('.surecart_page_sc-customers .css-1ssgtuh').length && tk_fc_backend_ajax_object.profile_btn_on_customers) {


        var customerInterval = setInterval(function () {

            var customer_email = $('.css-12icawk .css-kbi48l').html();

            if (customer_email) {

                display_fc_profile_view_btn(customer_email, 'sc_customers');
                clearInterval(customerInterval);
            }
        }, 1000);
    };

    // For Surecart Subscription view Page
    if ($('.surecart_page_sc-subscriptions .css-1ssgtuh').length && tk_fc_backend_ajax_object.profile_btn_on_subscriptions) {


        var subscriptionsInterval = setInterval(function () {

            var customer_email = $('.css-bqoia2 h1').html();

            if (customer_email) {

                display_fc_profile_view_btn(customer_email, 'sc_subscriptions');
                clearInterval(subscriptionsInterval);
            }
        }, 1000);
    };

    // Get the Fluent Crm profile view button and display it.
    function display_fc_profile_view_btn(customer_email, page) {

        if (customer_email) {

            $.ajax({
                url: tk_fc_backend_ajax_object.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    'action': 'surelywp_tk_fc_get_profile_btn',
                    'customer_email': customer_email,

                },
                success: function (response) {
                    if (response.status) {

                        if ('sc_customers' == page) { // On customer
                            $('.css-2jjstt').append(response.fc_profile_btn);
                        } else if ('sc_subscriptions' == page) { // On subscription
                            $('.css-wzxb7d > div:first-child').find('.components-card__footer').append(response.fc_profile_btn);
                        } else if ('sc_orders' == page) { // On order
                            $('.css-wzxb7d > div:first-child').find('.css-hwa08m').append(response.fc_profile_btn);
                        }
                    }
                }
            });
        }
    }
});