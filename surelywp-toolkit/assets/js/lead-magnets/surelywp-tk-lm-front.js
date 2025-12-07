"use strict";

jQuery(document).ready(function ($) {


    if ($('.surelywp-lead-magnet-button').length || $('#surelywp-tk-lm-form').length) {

        // If consent checkbox set show and user is login.
        if (tk_lm_front_ajax_object.is_user_login && tk_lm_front_ajax_object.consent_checkbox) {
            $('.surelywp-lead-magnet-button').before(tk_lm_front_ajax_object.consent_checkbox);
        }

        // Ajax Call on optin Button Click
        $(document).on("click", ".surelywp-lead-magnet-button", function (e) {

            if ($(this).hasClass('disable')) {
                return;
            }

            e.preventDefault();

            $(".lm-alert-msg").remove();

            // Get User is Login or not.
            var is_user_login = tk_lm_front_ajax_object.is_user_login;
            var is_customer_give_consent = false;

            if (is_user_login && $('.optin_form_consent_checkbox').length) {

                var consent_checkbox_element = $('.optin_form_consent_checkbox');
                is_customer_give_consent = consent_checkbox_element.prop('checked') ? true : false;
                if (consent_checkbox_element.prop('required') && !is_customer_give_consent) {

                    // Checkbox is required but not checked.
                    display_message(
                        ".surelywp-lead-magnet-button",
                        wp.i18n.__("Please check the consent checkbox.", "surelywp-toolkit"),
                        "danger",
                        3600000
                    );
                    return;
                }
            }

            // Get the class attribute
            var classAttr = $(this).attr('class');

            // Use a regular expression to find the product ID
            var match = classAttr.match(/lm-product-id-([a-z0-9\-]+)/);

            var product_id;

            if (match) {

                product_id = match[1];

            } else { // For Surecart Product Page.

                if ($('#sc-store-data').length) { // For Surecart older version.

                    var scriptData = $('#sc-store-data').html();
                    var jsonData = JSON.parse(scriptData);
                    if (jsonData.product) {
                        product_id = Object.keys(jsonData.product)[0];
                    }

                } else if ($('.wp-block-surecart-product-page').length) { // For Surecart 3.

                    var form = $('.wp-block-surecart-product-page');
                    // Get the 'data-wp-context' attribute
                    var context = form.attr('data-wp-context');

                    // Parse the JSON string
                    var contextData = JSON.parse(context);

                    // Extract the product ID
                    product_id = contextData.product.id;
                }
            }

            // If Product id not found then return.
            if (!product_id) {
                display_message(
                    ".surelywp-lead-magnet-button",
                    wp.i18n.__("Product ID not found.", "surelywp-toolkit"),
                    "danger",
                    3600000
                );
                return;
            }

            if (is_user_login) {

                var is_surecart_button = ('surlywp-lm-optin-btn' === $(this).find('sc-button').attr('id')) ? true : false;
                var btn_a_tag = $(this).find('.wp-element-button');
                var btn_text = btn_a_tag.html();

                if (is_surecart_button) {
                    $('#surlywp-lm-optin-btn').attr("loading", "true");
                    $('#surlywp-lm-optin-btn').attr("disabled", "true");
                } else {
                    $('.surelywp-lead-magnet-button').addClass('disable');
                    btn_a_tag.html('<span style="visibility: hidden;">' + btn_text + '</span><sc-spinner class="lm-btn-loader"></sc-spinner>');
                }

                // Remove Alert Message
                if ($(".lm-alert-msg").length > 0) {
                    $(".lm-alert-msg").remove();
                }
                $.ajax({
                    url: tk_lm_front_ajax_object.ajax_url,
                    dataType: "json",
                    type: "POST",
                    data: {
                        action: "surelywp_tk_lm_login_user_handler",
                        product_id: product_id,
                        is_customer_give_consent: is_customer_give_consent,
                        nonce: tk_lm_front_ajax_object.nonce,
                    },
                    success: function (res) {

                        if (is_surecart_button) {
                            $("#surlywp-lm-optin-btn").attr("loading", "false");
                            $("#surlywp-lm-optin-btn").removeAttr("disabled");
                        } else {
                            $('.surelywp-lead-magnet-button').removeClass('disable');
                            btn_a_tag.html(btn_text);
                        }

                        if (res.status == true) {

                            // Close modal
                            $(".surelywp-lm-optin-form-modal").remove();

                            if (!res.already_created) {
                                display_message(
                                    ".surelywp-lead-magnet-button",
                                    wp.i18n.__("Order Created successfully.", "surelywp-toolkit"),
                                    "success",
                                    3600000
                                );
                            }

                            // Specify the URL you want to redirect to
                            var newURL = res.dashboard_url.toString();

                            // Use window.location to redirect to the new URL
                            window.location.href = newURL;

                        } else if (res.error !== '') {
                            display_message(
                                ".surelywp-lead-magnet-button",
                                res.error,
                                "danger",
                                3600000
                            );
                        } else {
                            display_message(
                                ".surelywp-lead-magnet-button",
                                wp.i18n.__("Something went wrong", "surelywp-toolkit"),
                                "danger",
                                3600000
                            );
                        }
                    },
                }).catch((error) => {
                    console.error("Error:", error);
                });
            } else {
                $("body").append(
                    '<div class="lm-loader-wrap"><span class="lm-loader"></span></div>'
                );
                $.ajax({
                    url: tk_lm_front_ajax_object.ajax_url,
                    type: "POST",
                    dataType: "json",
                    data: {
                        action: "surelywp_tk_lm_modal_render",
                        product_id: product_id,
                        nonce: tk_lm_front_ajax_object.nonce,
                    },
                    success: function (res) {

                        $(".lm-loader-wrap").remove();

                        if (res.status) {

                            var body = $("body");
                            body.append(res.form_html);

                        } else if (res.error) {

                            display_message(
                                ".surelywp-lead-magnet-button",
                                res.error,
                                "danger",
                                3600000
                            );
                        }
                    },
                }).catch((error) => {
                    console.error("Error:", error);
                });
            }
        });

        // Ajax call on optin Form submit.
        $(document).on("scFormSubmit", '#surelywp-tk-lm-form', function (event) {

            event.preventDefault();
            $("#optin-submit-btn").attr("loading", "true");
            $("#optin-submit-btn").attr("disabled", "true");
            // Remove Alert Message
            if ($(".lm-alert-msg").length > 0) {
                $(".lm-alert-msg").remove();
            }
            var nonce = $("#optin_form_submit_nonce").val();
            var product_id = $("#surelywp-lm-product-id").val();
            var jsonData = event.target.getFormJson();
            var form_data = [];

            jsonData
                .then((result) => {
                    form_data = result;
                    $.ajax({
                        url: tk_lm_front_ajax_object.ajax_url,
                        type: "POST",
                        dataType: "json",
                        data: {
                            action: "surelywp_tk_lm_optin_form_submit_callback",
                            form_data: form_data,
                            nonce: nonce,
                            product_id: product_id,
                        },
                        success: function (res) {

                            $("#optin-submit-btn").attr("loading", "false");
                            $("#optin-submit-btn").removeAttr("disabled");

                            var display_message_after = '';
                            if ($('.surelywp-lead-magnet-button').length) {
                                display_message_after = '.surelywp-lead-magnet-button';
                            } else {
                                display_message_after = '.surelywp-subscription-form';
                            }

                            if (res.email_status == true) {

                                // Close modal
                                $(".surelywp-lm-optin-form-modal").remove();

                                display_message(
                                    display_message_after,
                                    res.email_verification_message,
                                    "success",
                                    null
                                );

                                // Remove the inline form.
                                $('.surelywp-subscription-form').remove();

                            } else if (res.status == true) {

                                // Close modal
                                $(".surelywp-lm-optin-form-modal").remove();

                                if (!res.already_created) {
                                    display_message(
                                        display_message_after,
                                        wp.i18n.__("Order Created successfully.", "surelywp-toolkit"),
                                        "success",
                                        20000
                                    );

                                    // Remove the inline form.
                                    $('.surelywp-subscription-form').remove();

                                }
                                // Specify the URL you want to redirect to
                                var newURL = res.dashboard_url.toString();

                                // Use window.location to redirect to the new URL
                                window.location.href = newURL;
                            } else if (res.error != "") {
                                display_message(".optin-submit-btn", res.error, "danger", null);
                            } else {
                                display_message(
                                    ".optin-submit-btn",
                                    wp.i18n.__("Something went wrong", "surelywp-toolkit"),
                                    "danger",
                                    50000
                                );
                            }
                        },
                    });
                })
                .catch((error) => {
                    console.error("Error:", error);
                });
        });

        // close Email Optin Form modal
        $(document).on("click", "#email-optin-form-modal-close", function () {
            $(".surelywp-lm-optin-form-modal").remove();
        });

        // Display message on product page
        function display_message(location, message, type, duration) {
            $(location).after(
                "<sc-alert class='lm-alert-msg surelywp-lm-error' open type='" +
                type +
                "'>" +
                message +
                "</sc-alert>"
            );

            if (duration) {
                setTimeout(function () {
                    $(".lm-alert-msg").remove();
                }, duration);
            }
        }
    }
});
